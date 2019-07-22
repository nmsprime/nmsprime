<?php

class InstallUseModempollerNmsprime extends BaseMigration
{
    protected $tablename = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        require_once '/etc/cacti/db.php';
        system("echo \"REPLACE INTO settings VALUES ('enable_snmp_agent',''); UPDATE poller SET processes = 1 WHERE name = 'Main Poller';\" | mysql -u $database_username -p$database_password $database_default");
        system("sed -i 's/^[^#]/#/' /etc/cron.d/cacti");
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
