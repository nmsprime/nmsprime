#!/bin/bash
# This script may be executed after updating via git (i.e. git pull) to show the
# difference between the currently used configuration files and the possibly
# updated ones in git. Based on the output, the currently used and updated ones
# should be merged (like *.rpmnew files). Permission mismatch warnings are
# directed to stderr. Thus, redirect stdout to a file to get only the
# differences between the files:

# ./get_config_diff.sh > config.diff

ignore=(
	'/etc/dhcp-nmsprime/blocked.conf'
	'/etc/dhcp-nmsprime/cmts_gws.conf'
	'/etc/dhcp-nmsprime/endpoints-host.conf'
	'/etc/dhcp-nmsprime/modems-clients-public.conf'
	'/etc/dhcp-nmsprime/modems-host.conf'
	'/etc/dhcp-nmsprime/mta.conf'
	'/etc/kea/gateways6.conf'
	'/etc/kea/hosts6.conf'
	'/var/log/nmsprime/tftpd-cm.log'
	'/var/named/dynamic/in-addr.arpa.zone'
	'/var/named/dynamic/nmsprime.test.zone'
)

for file in $(find /var/www/nmsprime -maxdepth 4 -path "*/Install/config.cfg"); do
	while read -r line; do
		f_from="$(dirname $file)/files/$(echo "$line" | cut -d'=' -f1 | xargs)"
		f_to=$(echo "$line" | cut -d'=' -f2 | xargs)

		if [ ! -f "$f_to" ]
		then
			f_to='/dev/null'
		else
			if [ $(stat -c "%a" "$f_from") -ne $(stat -c "%a" "$f_to") ]; then
				echo "permission mismatch: $f_from $f_to" >&2
			fi
		fi

		diff -u "$f_to" "$f_from"
	done < <(awk '/\[files\]/{flag=1;next}/\[/{flag=0}flag' "$file" | grep '=' | sed -f <(printf '\|%s|d\n' "${ignore[@]}"))
done
