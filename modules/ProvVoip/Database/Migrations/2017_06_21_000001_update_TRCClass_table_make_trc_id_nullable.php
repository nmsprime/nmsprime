<?php


/**
 * Updater to add col for technical method of purchase tariffs
 *
 * @author Patrick Reichel
 */
class UpdateTRCClassTableMakeTrcIdNullable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'trcclass';

    /**
     * Run the migrations.
     *
     * @author Patrick Reichel
     *
     * @return void
     */
    public function up()
    {

        // make trc_id nullable ⇒ this will be used for unset TRCClass
        DB::statement('ALTER TABLE `'.$this->tablename.'` MODIFY `trc_id` INTEGER UNSIGNED UNIQUE NULL;');

        // insert value for not set TRC class (this e.g. will be used in autogenerated phonenumbermanagements
        DB::update('INSERT INTO `'.$this->tablename."` (trc_id, trc_short, trc_description) VALUES(NULL, 'n/a', 'unknown or not set');");
    }

    /**
     * Reverse the migrations.
     *
     * @author Patrick Reichel
     *
     * @return void
     */
    public function down()
    {

        // remove the null entry
        DB::statement('DELETE FROM `'.$this->tablename.'` WHERE `trc_id` IS NULL');

        // make trc_id not nullable
        DB::statement('ALTER TABLE '.$this->tablename.' MODIFY `trc_id` INTEGER UNSIGNED UNIQUE NOT NULL;');
    }
}
