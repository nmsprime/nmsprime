# source environment variables to use php 7.1
source scl_source enable rh-php71

# variables
env="/etc/nmsprime/env/provmon.env"
mysql_cacti_psw=$(pwgen 12 1) # SQL password for user nmsprime_cacti
admin_psw='admin'

# set cacti psw in env file
sed -i "s/^CACTI_DB_PASSWORD=$/CACTI_DB_PASSWORD=$mysql_cacti_psw/" "$env"

# avoid excessive DB access, seen in all 0.8.8 versions of cacti
rpm -qi cacti | grep Version | grep -q '0.8.8' && sed -i 's/usleep(500)/sleep(1)/' /usr/share/cacti/poller.php

# fix bug in 1.0.4
cd /var/lib/cacti
md5sum cli/add_graphs.php | grep -q '1416f1ddae7fb14a4acc64008c146524' && wget -qO- https://github.com/Cacti/cacti/commit/2609d5892cb9b8d284fe090538f023664c06c24c.patch | head -n -13 | patch -p1

# create DB accessed by cactiuser
mysql -u root -e "CREATE DATABASE cacti CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';"
mysql -u root -e "GRANT ALL ON cacti.* TO 'cactiuser'@'localhost' IDENTIFIED BY '$mysql_cacti_psw';";

# allow cacti to access time_zone_name table
mysql -u root -e "GRANT SELECT ON mysql.time_zone_name TO 'cactiuser'@'localhost';";

# set psw in cacti db config file
sed -i "s/^\$database_password =.*/\$database_password = '$mysql_cacti_psw';/" /etc/cacti/db.php

# populate default DB
# NOTE: for some unknown reasons, doing this in one line, like "mysql ... < ", does not work. maybe due two special char * in file link
cacti_file=`ls /usr/share/doc/cacti-*/cacti.sql`
mysql -u root cacti < "$cacti_file"

# allow guest user to access graphs without login (also invalidate its password, by setting an imposible bcrypt hash)
# send SNMP queries concurrenly to modems (depending on no of cpus)
mysql cacti -u cactiuser --password="$mysql_cacti_psw" << EOF
REPLACE INTO settings VALUES ('guest_user','guest'),('concurrent_processes','$(nproc)');
UPDATE user_auth SET password='$(php -r "echo password_hash('$admin_psw', PASSWORD_DEFAULT);")', must_change_password='' WHERE username='admin';
UPDATE user_auth SET password='invalidated', must_change_password='', enabled='on' WHERE username='guest';
EOF

# link ss_docsis.php in git to the correct location, this way its automatically updated
ln -srf /var/www/nmsprime/modules/ProvMon/Console/cacti/ss_docsis.php /usr/share/cacti/scripts/ss_docsis.php
ln -srf /var/www/nmsprime/modules/ProvMon/Console/cacti/cisco_cmts.xml /usr/share/cacti/resource/snmp_queries/cisco_cmts.xml

sed -i 's/^#//' /etc/cron.d/cacti
sed -i 's/Require host localhost$/Require all granted\n\t\tDirectoryIndex index.php/' /etc/httpd/conf.d/cacti.conf
systemctl reload httpd.service

# add tree categories, to group devices of same type, import cablemodem template from git
cd /usr/share/cacti/cli
su -s /bin/bash -c "php add_tree.php --type=tree --name='Cablemodem' --sort-method=natural" apache
su -s /bin/bash -c "php add_tree.php --type=tree --name='CMTS' --sort-method=natural" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_cablemodem.xml" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_casa_cmts.xml" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_cisco_cmts.xml" apache

/opt/rh/rh-php71/root/usr/bin/php /var/www/nmsprime/artisan view:clear

# we call ProvMonController from cacti and thus need to be able to write to the following folder
chmod o+w /var/www/nmsprime/storage/framework/views

# create graphs for all existing modems
/opt/rh/rh-php71/root/usr/bin/php /var/www/nmsprime/artisan nms:cacti

# make .env files readable for apache
# TODO/REVIEW: shouln't this be in module_after_install.sh to be executed for every module?
chgrp -R apache /etc/nmsprime/env
chmod -R o-rwx /etc/nmsprime/env
chmod -R g-w /etc/nmsprime/env

# add our css rules to cacti, if they haven't been added yet (see after_upgrade.sh as well)
file='/usr/share/cacti/include/themes/modern/main.css'
if [[ -e "$file" && -z $(grep -o nmsprime "$file") ]]; then
cat << EOF >> "$file"

/* nmsprime */

html {
	overflow: unset !important;
	overflow-x:hidden !important;
	overflow-y: visible !important;
	height: auto !important;
}

body:not(.loginBody) {
	overflow: unset !important;
	overlow-y: visible !important;
}

table {
	margin: 0 !important;
}

#cactiContent, #navigation_right {
	height: auto !important;
}
EOF
fi
