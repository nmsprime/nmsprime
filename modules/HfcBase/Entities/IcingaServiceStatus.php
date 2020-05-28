<?php

namespace Modules\HfcBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\HfcBase\Contracts\ImpairedContract;

class IcingaServiceStatus extends Model implements ImpairedContract
{
    // SQL connection
    protected $connection = 'mysql-icinga2';

    protected $primaryKey = 'servicestatus_id';

    // The associated SQL table for this Model
    public $table = 'icinga_servicestatus';

    public $additionalData;
    public $affectedModems;

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            $model->additionalData = $model->deserializePerfdata($model->perfdata ?? '');
        });
    }

    public function icingaObject()
    {
        return $this->belongsTo(IcingaObject::class, 'service_object_id', 'object_id')
            ->where('is_active', '=', '1')
            ->where('name2', '<>', 'ping4')
            ->orderByRaw("name2 like 'clusters%' desc");
    }

    public function scopeForTroubleDashboard($query)
    {
        return $query->orderBy('last_hard_state', 'desc')
            ->orderBy('last_time_ok', 'desc')
            ->with(['icingaObject.netelement'])
            ->whereHas('icingaObject');
    }

    public function getNetelementAttribute()
    {
        return $this->icingaObject->netelement;
    }

    public function hasAdditionalData()
    {
        return count($this->additionalData);
    }

    public function toIcingaWeb()
    {
        return 'https://'.\Request::server('HTTP_HOST').'/icingaweb2/monitoring/service/show?host='.
            $this->icingaObject->name1.'&service='.$this->icingaObject->name2;
    }

    public function toControlling()
    {
        return false;
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

    public function toTicket()
    {
        $state = preg_replace('/[<>]/m', '', $this->output);

        return route('Ticket.create', [
            'name' => "Service {$this->icingaObject->name2}: ",
            'description' => "{$state}\nSince {$this->last_hard_state_change}",
        ]);
    }

    public function toSubTicket($netelements, $perf)
    {
        if (! $perf['id']) {
            return route('Ticket.create', [
                'name' => "{$perf['text']}",
                'description' => "{$perf['text']}\nSince {$this->last_hard_state_change}",
            ]);
        }

        return route('Ticket.create', [
            'name' => "Netelement {$netelements[$perf['id']]->name}: ",
            'description' => "{$perf['text']}\nSince {$this->last_hard_state_change}",
        ]);
    }

    public function affectedModemsCount($netelements)
    {
        if (\Str::contains($this->icingaObject->name2, 'cluster')) {
            return $this->affectedModems = $this->additionalData->map(function ($element) use ($netelements) {
                $element['id'] = $netelements[$element['id']]->modems_count;

                return $element;
            })->sum('id');
        }

        return $this->affectedModems = optional($this->netelement)->modems_count;
    }

    /**
     * Return formatted impaired performance data for a given perfdata string
     *
     * @author Ole Ernst, Christian Schramm
     * @param string $perf
     * @return Illuminate\Support\Collection
     */
    private function deserializePerfdata(string $perf): \Illuminate\Support\Collection
    {
        $ret = [];
        preg_match_all("/('.+?'|[^ ]+)=([^ ]+)/", $perf, $matches, PREG_SET_ORDER);

        foreach ($matches as $idx => $match) {
            $data = explode(';', rtrim($match[2], ';'));

            $value = $data[0];
            $unifiedValue = intval(preg_replace('/[^0-9.]/', '', $value)); // remove unit of measurement, such as percent
            $warningThreshhold = $data[1] ?? null;
            $criticalThreshhold = $data[2] ?? null;

            if (is_numeric($unifiedValue) && (substr($value, -1) == '%' || (isset($data[3]) && isset($data[4])))) { // we are dealing with percentages
                $min = $data[3] ?? 0;
                $max = $data[4] ?? 100;
                $percentage = ($max - $min) ? (($unifiedValue - $min) / ($max - $min) * 100) : null;
                $percentageText = sprintf(' (%.1f%%)', $percentage);
            }

            if (is_numeric($unifiedValue) && $warningThreshhold && $criticalThreshhold) { // set the html color
                $htmlClass = $this->getPerfDataHtmlClass($unifiedValue, $warningThreshhold, $criticalThreshhold);

                if ($htmlClass === 'success') { // don't show non-impaired perf data
                    unset($ret[$idx]);
                    continue;
                }
            }

            $id = explode('_', substr($match[1], 1))[0];
            $text = is_numeric($id) ? "'".explode('_', substr($match[1], 1))[1] : $match[1];
            if (! is_numeric($id)) {
                $id = null;
            }

            $ret[$idx]['id'] = $id;
            $ret[$idx]['val'] = $value;
            $ret[$idx]['text'] = $text.($percentageText ?? null);
            $ret[$idx]['cls'] = $htmlClass ?? null;
            $ret[$idx]['per'] = $percentage ?? null;
        }

        return collect($ret);
    }

    /**
     * Return performance data colour class according to given limits
     *
     * @author Ole Ernst, Christian Schramm
     * @param int $value
     * @param int $warningThreshhold
     * @param int $criticalThreshhold
     * @return string
     */
    private function getPerfDataHtmlClass(int $value, int $warningThreshhold, int $criticalThreshhold): string
    {
        if ($criticalThreshhold > $warningThreshhold) { // i.e. for upstream power
            [$value, $warningThreshhold,$criticalThreshhold] =
                negate($value, $warningThreshhold, $criticalThreshhold);
        }

        if ($value > $warningThreshhold) {
            return 'success';
        }

        if ($value > $criticalThreshhold) {
            return 'warning';
        }

        return 'danger';
    }
}
