cd /var/www/lara

php artisan module:publish HfcBase

chown -R apache /var/www/lara/public/modules/hfcbase/kml
chown -R apache /var/www/lara/public/modules/hfcbase/erd