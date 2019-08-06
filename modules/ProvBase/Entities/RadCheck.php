<?php

namespace Modules\ProvBase\Entities;

class RadCheck extends \BaseModel
{
    // SQL connection
    protected $connection = 'mysql-radius';
    // The associated SQL table for this Model
    public $table = 'radcheck';

    public $timestamps = false;
    protected $forceDeleting = true;

    // freeradius-mysql does not use softdeletes
    public static function bootSoftDeletes()
    {
    }
}
