<?php

namespace Modules\HfcBase\Entities;

class IcingaHostStatus extends \BaseModel
{
    // SQL connection
    protected $connection = 'mysql-icinga2';

    // The associated SQL table for this Model
    public $table = 'icinga_hoststatus';
}
