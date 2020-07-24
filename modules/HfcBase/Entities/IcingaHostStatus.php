<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\HfcBase\Contracts\ImpairedContract;

class IcingaHostStatus extends Model implements ImpairedContract
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
    public $table = 'icinga_hoststatus';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'hoststatus_id';

    /**
     * Contains more detailed for Subservices, deserialized from perfdata field.
     *
     * @var \Illuminate\Support\Collection
     */
    public $additionalData = [];

    /**
     * The amount of modems affected by this Service.
     *
     * @var int
     */
    public $affectedModems;

    /**
     * The "booting" method of the model. For every retrieved Hosts, interpret
     * the state and set it to Critical, when it is not OK.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            if ($model->last_hard_state != 0) {
                $model->last_hard_state = 2;
            }
        });
    }

    /**
     * Relation to IcingaObject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function icingaObject()
    {
        return $this->belongsTo(IcingaObject::class, 'host_object_id', 'object_id')
            ->where('is_active', '=', '1')
            ->where('objecttype_id', '1');
    }

    /**
     * Scope to get all necessary informations for the trouble Dashboard.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTroubleDashboard($query)
    {
        return $query->with(['icingaObject.netelement'])->whereHas('icingaObject');
    }

    /**
     * Laravel magic method to quickly access the netelement.
     *
     * @return void
     */
    public function getNetelementAttribute()
    {
        return $this->icingaObject->netelement;
    }

    /**
     * Link for this Host in IcingaWeb2
     *
     * @return string
     */
    public function toIcingaWeb()
    {
        if (! $this->netelement) {
            return 'https://'.request()->server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$this->icingaObject->name1;
        }

        return 'https://'.request()->server('HTTP_HOST').'/icingaweb2/monitoring/host/show?host='.$this->netelement->id.'_'.$this->netelement->name;
    }

    /**
     * Link to Controlling page in NMS Prime, if this Host is registered as a
     * NetElement in NMS Prime.
     *
     * @return string|void
     */
    public function toControlling()
    {
        if (! $this->netelement) {
            return;
        }

        return route('NetElement.controlling_edit', [
            'id' => $this->netelement->id,
            'parameter' => 0,
            'index' => 0,
        ]);
    }

    /**
     * Link to Topo overview. Depending of the information available the
     * netelement or all netelements are displayed.
     *
     * @return string
     */
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

    /**
     * Link to Ticket creation form already prefilled.
     *
     * @return string
     */
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

    /**
     * Tries to get the amount of affected modems of the related NetElement.
     *
     * @param \Illuminate\Database\Eloquent\Collection $netelements
     * @return int
     */
    public function affectedModemsCount($netelements)
    {
        if ($this->netelement) {
            return $this->affectedModems = $netelements[$this->netelement->id] ?? 0;
        }

        return 0;
    }

    /**
     * This method is here to fulfil the contract. Currently Hosts don't hace
     * any performance data property.
     *
     * @return bool
     */
    public function hasAdditionalData()
    {
        return false;
    }
}
