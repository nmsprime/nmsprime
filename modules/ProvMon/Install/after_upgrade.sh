cd /usr/share/cacti/cli

for file in /var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_*.xml;
  do su -s /bin/bash -c "php import_template.php --filename=$file" apache
done
