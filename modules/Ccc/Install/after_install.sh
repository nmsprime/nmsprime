env="/etc/nmsprime/env/ccc.env"
ccc_pw=$(pwgen 12 1) # SQL password for user nmsprime_ccc

mysql -u root -e "CREATE DATABASE nmsprime_ccc;"
mysql -u root -e "GRANT ALL ON nmsprime_ccc.* TO 'nmsprime_ccc'@'localhost' IDENTIFIED BY '$ccc_pw'";
sed -i "s/^CCC_DB_PASSWORD=$/CCC_DB_PASSWORD=$ccc_pw/" "$env"

# firewalld - enable admin interface
firewall-cmd --add-service=https --zone=public --permanent
firewall-cmd --reload

# reload apache config
systemctl reload httpd

# make .env files readable for apache
chgrp -R apache /etc/nmsprime/env
chmod -R o-rwx /etc/nmsprime/env
chmod -R g-w /etc/nmsprime/env
