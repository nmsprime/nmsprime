<?php

class InstallSetCharsetCacti extends BaseMigration
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
        system("echo 'ALTER DATABASE $database_default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;' | mysql -u $database_username -p$database_password");
        system('systemctl restart rh-php71-php-fpm');
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
