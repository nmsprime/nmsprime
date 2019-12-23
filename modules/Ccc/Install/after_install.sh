dir='/var/www/nmsprime'
env='/etc/nmsprime/env'
source "$env/root.env"
ccc_pw=$(pwgen 12 1) # SQL password for user nmsprime_ccc

mysql -u "$ROOT_DB_USERNAME" --password="$ROOT_DB_PASSWORD" << EOF
CREATE DATABASE nmsprime_ccc CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
GRANT ALL ON nmsprime_ccc.* TO 'nmsprime_ccc'@'localhost' IDENTIFIED BY '$ccc_pw';
EOF

sed -i "s/^CCC_DB_PASSWORD=$/CCC_DB_PASSWORD=$ccc_pw/" "$env/ccc.env"

# firewalld - enable admin interface
firewall-cmd --add-service=https --zone=public --permanent
firewall-cmd --reload

# reload apache config
systemctl reload httpd

# create directories
mkdir -p "$dir/storage/app/config/ccc/template"
chown -R apache "$dir/storage/"
