<?php

namespace Modules\HfcSnmp\Entities;

class SnmpValue extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'snmpvalue';

    // Disable Observer by Default as this would leed to a mass number of log entries - NOTE: Observer is enabled in SnmpController for manual SnmpSets
    public $observer_enabled = false;

	// Add your validation rules here
    public static function rules($id = null)
    {
        return array(
        );
    }

    // Name of View
    public static function view_headline()
    {
        return 'Temporary Testing SNMP Values';
    }

    // View Icon
	public static function view_icon()
	{
	  return '<i class="fa fa-th-list"></i>';
	}

    // AJAX Index list function
    // generates datatable content and classes for model
    // TODO: device or DeviceType? and SNMP mibfile? implementation
	public function view_index_label()
	{
        //copy functionality
        $device = '';
        if ($this->device)
            $device = $this->device->name;

        $snmpmib = '';
        if ($this->snmpmib)
            $snmpmib = $this->snmpmib->field;

		return ['table' => $this->table,
				'index_header' => [$this->table.'.oid_index', $this->table.'.value'],
				'header' =>  $this->id.': '.$device.' - '.$snmpmib.' - '.$this->oid_index,
				'order_by' => ['0' => 'asc']];
	}

    /**
     * Relations
     */
    public function netelement()
    {
        return $this->belongsTo('Modules\HfcReq\Entities\NetElement');
    }

    public function oid()
    {
        return $this->belongsTo('Modules\HfcSnmp\Entities\OID');
    }

}
