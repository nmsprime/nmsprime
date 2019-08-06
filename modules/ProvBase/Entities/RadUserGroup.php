<?php

namespace Modules\ProvBase\Entities;

class RadUserGroup extends \BaseModel
{
    // SQL connection
    protected $connection = 'mysql-radius';
    // The associated SQL table for this Model
    public $table = 'radusergroup';

    public $timestamps = false;
    protected $forceDeleting = true;

    protected $primaryKey = 'username';

    // freeradius-mysql does not use softdeletes
    public static function bootSoftDeletes()
    {
    }
}
