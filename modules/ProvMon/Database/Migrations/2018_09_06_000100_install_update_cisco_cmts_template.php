<?php

class InstallUpdateCiscoCmtsTemplate extends BaseMigration
{
    protected $tablename = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        system('su -s /bin/bash -c "php /usr/share/cacti/cli/import_template.php --filename=/var/www/nmsprime/modules/ProvMon/Console/cacti/cacti_host_template_cisco_cmts.xml" apache');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
