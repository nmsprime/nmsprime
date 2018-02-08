#!/bin/bash

# NOTE: this will be automatically performed durring RPM or aritsan install installation
#       so this script is deprecated / duplicated

# ask user for various credentials
read -e -p "Enter MYSQL root psw:" mysql_root_psw
read -e -p "Enter MYSQL cactiuser (will be automatically created) psw:" mysql_cacti_psw
read -e -p "Enter Cacti web admin psw:" admin_psw

# install cacti
yum -y install cacti
# avoid excessive DB access, seen in all 0.8.8 versions of cacti
rpm -qi cacti | grep Version | grep -q '0.8.8' && sed -i 's/usleep(500)/sleep(1)/' /usr/share/cacti/poller.php
# fix bug in 1.0.4
cd /var/lib/cacti
md5sum cli/add_graphs.php | grep -q '1416f1ddae7fb14a4acc64008c146524' && wget -qO- https://github.com/Cacti/cacti/commit/2609d5892cb9b8d284fe090538f023664c06c24c.patch | head -n -13 | patch -p1

# create DB accessed by cactiuser
mysqladmin -u root --password="$mysql_root_psw" create cacti
echo "GRANT ALL ON cacti.* TO 'cactiuser'@'localhost' IDENTIFIED BY '$mysql_cacti_psw';" | mysql -u root --password="$mysql_root_psw"
# populate timezone info
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root --password="$mysql_root_psw" mysql
echo "GRANT SELECT ON mysql.time_zone_name TO 'cactiuser'@'localhost';" | mysql -u root --password="$mysql_root_psw"
# populate default DB
sed -i "s/database_password =.*/database_password = \"$mysql_cacti_psw\";/" /etc/cacti/db.php
mysql cacti -u cactiuser --password="$mysql_cacti_psw" < /usr/share/doc/cacti-*/cacti.sql
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

echo "#*/5 * * * * cacti /usr/bin/php /usr/share/cacti/poller.php > /dev/null 2>&1" > /etc/cron.d/cacti
sed -i 's/Require host localhost$/Require all granted/' /etc/httpd/conf.d/cacti.conf
systemctl reload httpd.service

# add tree categories, to group devices of same type, import cablemodem template from git
cd /usr/share/cacti/cli
su -s /bin/bash -c "php add_tree.php --type=tree --name='Cablemodem' --sort-method=natural" apache
su -s /bin/bash -c "php add_tree.php --type=tree --name='CMTS' --sort-method=natural" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_cablemodem.xml" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_casa_cmts.xml" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_cisco_cmts.xml" apache

# add cacti to group apache, so it is able to read the .env file
gpasswd -a cacti apache

php /var/www/nmsprime/artisan view:clear
# we call ProvMonController from cacti and thus need to be able to write to the following folder
chmod o+w /var/www/nmsprime/storage/framework/views

# create graphs for all existing modems
php /var/www/nmsprime/artisan nms:cacti

echo "Please visit https://localhost/cacti to finish the installation, rerun php artisan nms:cacti and uncomment /etc/cron.d/cacti"
