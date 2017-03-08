#!/bin/bash

# ask user for various credentials
read -e -p "Enter MYSQL root psw:" mysql_root_psw
read -e -p "Enter MYSQL cactiuser (will be automatically created) psw:" mysql_cacti_psw
read -e -p "Enter Cacti web admin psw:" admin_psw

# install cacti
yum -y install cacti
# avoid excessive DB access, seen in all 0.8.8 versions of cacti
rpm -qi cacti | grep Version | grep -q '0.8.8' && sed -i 's/usleep(500)/sleep(1)/' /usr/share/cacti/poller.php
# create DB accessed by cactiuser
mysqladmin -u root --password="$mysql_root_psw" create cacti
echo "GRANT ALL ON cacti.* TO 'cactiuser'@'localhost' IDENTIFIED BY '$mysql_cacti_psw';" | mysql -u root --password="$mysql_root_psw"
sed -i "s/database_password =.*/database_password = \"$mysql_cacti_psw\";/" /etc/cacti/db.php
# populate default DB
mysql cacti -u cactiuser --password="$mysql_cacti_psw" < /usr/share/doc/cacti-*/cacti.sql
# allow guest user to access graphs without login (also invalidate its password, by setting an imposible md5 hash)
# send SNMP queries concurrenly to modems (depending on no of cpus)
mysql cacti -u cactiuser --password="$mysql_cacti_psw" << EOF
REPLACE INTO settings VALUES ('guest_user','guest'),('concurrent_processes','$(nproc)');
UPDATE user_auth SET password='$(echo -n "$admin_psw" | md5sum | cut -d' ' -f1)', must_change_password='' WHERE username='admin';
UPDATE user_auth SET password='invalidated', must_change_password='', enabled='on' WHERE username='guest';
EOF

# set correct ownership, link ss_docsis.php in git to the correct location, this way its automatically updated
chown -R cacti:cacti /var/lib/cacti/rra /var/log/cacti
ln -srf /var/www/lara/modules/ProvMon/Console/cacti/ss_docsis.php /usr/share/cacti/scripts/ss_docsis.php
ln -srf /var/www/lara/modules/ProvMon/Console/cacti/cisco_cmts.xml /usr/share/cacti/resource/snmp_queries/cisco_cmts.xml
echo "*/5 * * * * cacti /usr/bin/php /usr/share/cacti/poller.php > /dev/null 2>&1" > /etc/cron.d/cacti
sed -i 's/Require host localhost$/Require all granted/' /etc/httpd/conf.d/cacti.conf
systemctl reload httpd.service

# add tree categories, to group devices of same type, import cablemodem template from git
cd /usr/share/cacti/cli
su -s /bin/bash -c "php add_tree.php --type=tree --name='Cablemodem' --sort-method=natural" apache
su -s /bin/bash -c "php add_tree.php --type=tree --name='CMTS' --sort-method=natural" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/lara/modules/ProvMon/Console/cacti/cacti_host_template_cablemodem.xml --with-user-rras=1:2:3:4" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/lara/modules/ProvMon/Console/cacti/cacti_host_template_casa_cmts.xml --with-user-rras=1:2:3:4" apache
su -s /bin/bash -c "php import_template.php --filename=/var/www/lara/modules/ProvMon/Console/cacti/cacti_host_template_cisco_cmts.xml --with-user-rras=1:2:3:4" apache

# replicate logging into the webGUI via the commandline to set the default values
# this is a bit ugly, but there is no other way to fully automate the installation
cd ../install
php << "CODE" > /dev/null
<?php
$_REQUEST["step"] = 2;
include_once('index.php');
while (list($name, $array) = each($input)) $_POST[$name] = $array["default"];
file_put_contents('/tmp/settings.txt', serialize($_POST));
?>
CODE
php << "CODE" > /dev/null
<?php
$_POST = unserialize(file_get_contents('/tmp/settings.txt'));
$_REQUEST["step"] = 3;
include_once('index.php');
?>
CODE
rm /tmp/settings.txt

php /var/www/lara/artisan view:clear
# we call ProvMonController from cacti and thus need to be able to write to the following folder
chmod o+w /var/www/lara/storage/framework/views

# create graphs for all existing modems
php /var/www/lara/artisan nms:cacti
