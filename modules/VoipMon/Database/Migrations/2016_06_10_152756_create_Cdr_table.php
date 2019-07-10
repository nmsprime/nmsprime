<?php

class CreateCdrTable extends BaseMigration
{
    // Name of the table
    protected $tablename = 'cdr';

    /**
     * Run the migrations.
     *
     * @author Ole Ernst
     *
     * @return void
     */
    public function up()
    {
        $dump = base_path('modules/VoipMon/Console/voipmonitor/voipmonitor.cdr-16.0.2.sql');
        // we always import the dump, so we don't need to rely on a local voipmonitor
        \DB::unprepared(file_get_contents($dump));

        // start local voipmonitor, it will update the imported schema if necessary
        $this->_voipmonitor_cmd_local('start');

        if (! $this->_voipmonitor_exists()) {
            echo "You may want to grant access to DB voipmonitor to a remote user, so that the remote voipmonitor can fill the local DB.\n";
        }

        return parent::up();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // stop local voipmonitor, as we are about to drop its database
        $this->_voipmonitor_cmd_local('stop');

        \DB::statement('DROP DATABASE IF EXISTS voipmonitor');
    }

    /**
     * Check if voipmonitor application is installed on the system
     *
     * @author Ole Ernst
     *
     * @return true if voipmonitor application is installed on the system, False else
     */
    protected function _voipmonitor_exists()
    {
        system('which voipmonitor > /dev/null 2>&1', $ret);

        if ($ret) {
            echo "voipmonitor currently not installed locally\n";
        }

        return ! $ret;
    }

    protected function _voipmonitor_cmd_local($cmd)
    {
        // don't do anything, if voipmonitor is not installed
        if (! $this->_voipmonitor_exists()) {
            return;
        }

        system("systemctl $cmd voipmonitor.service");
    }
}
