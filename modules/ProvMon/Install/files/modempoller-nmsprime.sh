#!/bin/bash
dir='/run/nmsprime/cacti'

rm -rf "$dir"/*
cd "$dir"

read -r -a auths <<< $(php -r 'require_once "/etc/cacti/db.php"; echo "$database_default\n$database_password\n$database_username\n";' | xargs)
modempoller-nmsprime -d "${auths[0]}" -p "${auths[1]}" -u "${auths[2]}"
su -s /bin/bash -c '/usr/bin/php /usr/share/cacti/poller.php' apache
