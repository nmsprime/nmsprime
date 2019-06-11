<?php

class InstallPhpConfigProvmon extends BaseMigration
{
    protected $tablename = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file = '/etc/httpd/conf.d/cacti.conf';
        $str = file_get_contents($file);
        $str = preg_replace('/Require all granted$/m', "Require all granted\n\t\tDirectoryIndex index.php", $str);
        file_put_contents($file, $str);
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
