#!/bin/bash

SCRIPT=$(readlink -f $0)
SCRIPTPATH=`dirname $SCRIPT`

#
# Read SQL Root Psw and new Cacti Root Psw
#
read -e -p "Enter MYSQL root psw:" mysql_root_psw
read -e -p "Enter MYSQL cacti psw:" mysql_cacti_psw


#
# - YUM - 
# NOTE: should already be installed or marked as dependencie in ProvMon RPM package
#
yum -y install cacti


#
# SQL Preparation
#
/usr/bin/mysqladmin -u root --password=$mysql_root_psw create cacti
echo "GRANT ALL ON cacti.* TO 'cactiuser'@'localhost' IDENTIFIED BY '$mysql_cacti_psw';" | mysql -u root --password=$mysql_root_psw
/usr/bin/mysql cacti -u cactiuser --password=$mysql_cacti_psw < $SCRIPTPATH"/cacti/cacti.sql" 


#
# Cacti Specific Settings
# Note: leave the file comments inside for better understanding
#
echo "
<?php
/* make sure these values refect your actual database/host/user/password */
\$database_type = \"mysql\";
\$database_default = \"cacti\";
\$database_hostname = \"localhost\";
\$database_username = \"cactiuser\";
\$database_password = \"$mysql_cacti_psw\";
\$database_port = \"3306\";
\$database_ssl = false;

/*
   Edit this to point to the default URL of your Cacti install
   ex: if your cacti install as at http://serverip/cacti/ this
   would be set to /cacti/
*/
//$url_path = "/cacti/";

/* Default session name - Session name must contain alpha characters */
//$cacti_session_name = "Cacti";
?>" > /usr/share/cacti/include/config.php 


#
# Change Rights for Cacti Access
#
chown -R cacti:cacti /usr/share/cacti/rra /usr/share/cacti/log


#
# Enable Cacti Cron
#
echo "*/5 * * * * cacti /usr/bin/php /usr/share/cacti/poller.php > /dev/null 2>&1" > /etc/cron.d/cacti


#
# Copy Plugins
#
cp cacti/ss_docsis_stats.php /usr/share/cacti/scripts/
cp cacti/cisco_cmts.xml /usr/share/cacti/resource/snmp_queries/ 
cp cacti/ss_docsis_cmts_cm_count.php /usr/share/cacti/scripts/


#
# Enable HTTP Server config
#
sed -c -i "s/\tRequire host localhost/\tRequire all granted/" /etc/httpd/conf.d/cacti.conf


#
# add php default timezone setting
# Note: This is required, otherwise cacti/rrdtool will not draw anything
#
FILE=/etc/php.ini
LINE='date.timezone="Europe/Berlin"'
grep -q "$LINE" "$FILE" || echo "$LINE" >> "$FILE"


#
# Restart HTTP Server
#
systemctl restart httpd

echo "Please Visit: https://your-host/cacti";
echo "Default User/Psw: admin/admin";
