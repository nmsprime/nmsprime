<?php

namespace Modules\ProvBase\Entities;

class RadGroupReply extends \BaseModel
{
    // SQL connection
    protected $connection = 'mysql-radius';
    // The associated SQL table for this Model
    public $table = 'radgroupreply';

    public $timestamps = false;
    protected $forceDeleting = true;

    // https://wiki.mikrotik.com/wiki/Manual:RADIUS_Client
    // https://help.ubnt.com/hc/en-us/articles/204977464-EdgeRouter-PPPoE-Server-Rate-Limiting-Using-WISPr-RADIUS-Attributes
    public static $radiusAttributes = [
        'ds_rate_max_help' => [
            'WISPr-Bandwidth-Max-Down',
            'Ascend-Xmit-Rate',
        ],
        'us_rate_max_help' => [
            'WISPr-Bandwidth-Max-up',
            'Ascend-Data-Rate',
        ],
    ];

    // freeradius-mysql does not use softdeletes
    public static function bootSoftDeletes()
    {
    }
}
