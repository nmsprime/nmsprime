#!/bin/bash
source scl_source enable rh-php71
dir='/run/nmsprime/cacti'

rm -rf "$dir"/*
cd "$dir"

read -r -a auths <<< $(php -r 'require_once "/etc/cacti/db.php"; echo "$database_default\n$database_password\n$database_username\n";' | xargs)
modempoller-nmsprime -d "${auths[0]}" -p "${auths[1]}" -u "${auths[2]}"
fft-nmsprime

su -s /bin/bash -c '/usr/bin/php /usr/share/cacti/poller.php' apache

auth=$(grep '^DB_DATABASE\|^DB_USERNAME\|^DB_PASSWORD' /etc/nmsprime/env/global.env | sort | cut -d'=' -f2 | xargs)
read -r -a auths <<< "$auth"
cat <(echo "START TRANSACTION;UPDATE modem JOIN configfile ON modem.configfile_id = configfile.id SET us_pwr = 0, us_snr = 0, ds_pwr = 0, ds_snr = 0, tdr = NULL, fft_max = NULL WHERE configfile.device = 'cm' AND modem.deleted_at IS NULL AND configfile.deleted_at IS NULL;") update.sql <(echo 'COMMIT;') | mysql -u "${auths[2]}" --password="${auths[1]}" "${auths[0]}"
echo '\Modules\Dashboard\Http\Controllers\DashboardController::save_modem_statistics();' | php /var/www/nmsprime/artisan tinker
