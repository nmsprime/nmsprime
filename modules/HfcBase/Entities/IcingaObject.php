<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\HfcReq\Entities\NetElement;
use Illuminate\Database\Eloquent\Builder;

class IcingaObject extends Model
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
    public $table = 'icinga_objects';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'object_id';

    /**
     * The "booted" method of the model. Query only active Objects.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', '1');
        });
    }

    /**
     * Relation to IcingaHost.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hoststatus()
    {
        return $this->hasOne(IcingaHostStatus::class, 'host_object_id', 'object_id');
    }

    /**
     * Relation to IcingaService.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function servicestatus()
    {
        return $this->hasOne(IcingaServiceStatus::class, 'service_object_id', 'object_id')
            ->orderBy('last_hard_state', 'desc')
            ->orderBy('last_time_ok', 'desc');
    }

    /**
     * Relation to Netelement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function netelement()
    {
        return $this->belongsTo(NetElement::class, 'name1', 'id_name')
            ->where('id', '>', '2')
            ->where('netelementtype_id', '>', '2')
            ->without('netelementtype')
            ->withActiveModems();
    }

    /**
     * Scope to get the related Hoststatus.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithHostStatus($query)
    {
        return $query->where('objecttype_id', '1')
            ->with(['netelement', 'hoststatus']);
    }

    /**
     * Scope to get the related Service.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithServices($query)
    {
        return $query->with(['servicestatus', 'netelement'])
            ->where('name2', '<>', 'ping4')
            ->whereHas('servicestatus')
            ->orderByRaw("name2 like 'clusters%' desc");
    }

    /**
     * Check for Icinga Database.
     *
     * @return bool
     */
    public static function db_exists()
    {
        try {
            return \Schema::connection('mysql-icinga2')->hasTable('icinga_objects');
        } catch (\PDOException $e) {
            return false;
        }

        return false;
    }
}
