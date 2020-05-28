<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\HfcReq\Entities\NetElement;

class IcingaObject extends Model
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

    public function hoststatus()
    {
        return $this->hasOne(IcingaHostStatus::class, 'host_object_id', 'object_id');
    }

    public function netelement()
    {
        return $this->belongsTo(NetElement::class, 'name1', 'id_name')
            ->where('id', '>', '2')
            ->where('netelementtype_id', '>', '2')
            ->without('netelementtype')
            ->withActiveModems();
    }

    public function servicestatus()
    {
        return $this->hasOne(IcingaServiceStatus::class, 'service_object_id', 'object_id')
            ->orderBy('last_hard_state', 'desc')
            ->orderBy('last_time_ok', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', '=', '1');
    }

    public function scopeWithHostStatus($query)
    {
        return $query->active()
            ->where('objecttype_id', '1')
            ->with(['netelement', 'hoststatus']);
    }

    public function scopeWithServices($query)
    {
        return $query->active()
            ->with(['servicestatus', 'netelement'])
            ->where('name2', '<>', 'ping4')
            ->whereHas('servicestatus')
            ->orderByRaw("name2 like 'clusters%' desc");
    }
}
