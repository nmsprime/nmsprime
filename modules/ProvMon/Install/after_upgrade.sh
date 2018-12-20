# import/update all cacti host templates
cd /usr/share/cacti/cli
for file in /var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_*.xml;
  do su -s /bin/bash -c "php import_template.php --filename=$file" apache
done

# add our css rules to cacti, if they haven't been added yet (see after_install.sh as well)
file='/usr/share/cacti/include/themes/modern/main.css'
if [[ -e "$file" && -z $(grep -o nmsprime "$file") ]]; then
cat << EOF >> "$file"

/* nmsprime */

html {
	overflow-x: auto;
}

table {
	margin: 0 !important;
}

.cactiGraphContentAreaPreview {
	overflow-y: unset !important;
	overflow-x: unset !important;
}
EOF
fi
