<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class IcingaStateHistory extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql-icinga2';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'icinga_statehistory';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'statehistory_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'state_time'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('stateType', function (Builder $builder) {
            $builder->where('state_type', 1);
        });
    }
}
