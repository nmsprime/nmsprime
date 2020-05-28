<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\HfcBase\Contracts\ImpairedContract;

class IcingaHostStatus extends Model implements ImpairedContract
{
    // SQL connection
    protected $connection = 'mysql-icinga2';

    protected $primaryKey = 'servicestatus_id';
    // The associated SQL table for this Model
    public $table = 'icinga_hoststatus';

    public $additionalData = [];
    public $affectedModems;

    public function icingaObject()
    {
        return $this->belongsTo(IcingaObject::class, 'host_object_id', 'object_id')
            ->where('is_active', '=', '1')
            ->where('objecttype_id', '1');
    }

    public function scopeForTroubleDashboard($query)
    {
        return $query->with(['icingaObject.netelement'])->whereHas('icingaObject');
    }

    public function status()
    {
        return $this->output;
    }

    public function getNetelementAttribute()
    {
        return $this->icingaObject->netelement;
    }

    public function toIcingaWeb()
    {
        if (! $this->netelement) {
            return 'https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$this->icingaObject->name1;
        }

        return 'https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$this->netelement->id.'_'.$this->netelement->name;
    }

    public function toControlling()
    {
        if (! $this->netelement->id) {
            return;
        }

        return route('NetElement.controlling_edit', [
            'id' => $this->netelement->id,
            'parameter' => 0,
            'index' => 0,
        ]);
    }

    public function toTicket()
    {
        if (! $this->netelement) {
            return route('Ticket.create', [
                'name' => e($this->icingaObject->name1),
                'description' => '',
            ]);
        }

        return route('Ticket.create', [
            'name' => e($this->netelement->name),
            'description' => '',
        ]);
    }

    public function toMap()
    {
        if ($this->netelement) {
            return route('TreeTopo.show', ['field' => 'id', 'search' => $this->netelement->id]);
        }

        if (is_numeric($id = explode('_', $this->icingaObject->name1)[0])) {
            return route('TreeTopo.show', ['field' => 'id', 'search' => $id]);
        }

        return route('TreeTopo.show', ['field' => 'id', 'search' => 2]);
    }

    public function affectedModemsCount($netelements)
    {
        return $this->affectedModems = optional($this->netelement)->modems_count;
    }

    public function hasAdditionalData()
    {
        return false;
    }
}
