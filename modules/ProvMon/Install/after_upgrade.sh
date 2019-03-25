# import/update all cacti host templates
cd /usr/share/cacti/cli
for file in /var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_*.xml;
  do su -s /bin/bash -c "php import_template.php --filename=$file" apache
done

# link git files to the correct location, this way they are automatically updated
ln -srf /var/www/nmsprime/modules/ProvMon/Console/cacti/ss_docsis.php /usr/share/cacti/scripts/ss_docsis.php
ln -srf /var/www/nmsprime/modules/ProvMon/Console/cacti/cisco_cmts.xml /usr/share/cacti/resource/snmp_queries/cisco_cmts.xml

# add our css rules to cacti, if they haven't been added yet (see after_install.sh as well)
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
