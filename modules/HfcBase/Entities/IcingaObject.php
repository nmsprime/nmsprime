<?php

namespace Modules\HfcBase\Entities;

class IcingaObject extends \BaseModel
{
    // SQL connection
    protected $connection = 'mysql-icinga2';
    // The associated SQL table for this Model
    public $table = 'icinga_objects';

    public static function db_exists()
    {
        try {
            $ret = \Schema::connection('mysql-icinga2')->hasTable('icinga_objects');
        } catch (\PDOException $e) {
            return false;
        }

        return $ret;
    }

    public function icingahoststatus()
    {
        return $this->hasOne(IcingaHostStatus::class, 'host_object_id', 'object_id');
    }
}
