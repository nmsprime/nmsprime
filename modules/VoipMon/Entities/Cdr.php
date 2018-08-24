<?php

namespace Modules\VoipMon\Entities;

class Cdr extends \BaseModel
{
    // SQL connection
    // Default config of the voipmonitor daemon is to create its own database, use it instead of database defined in .env
    protected $connection = 'mysql-voipmonitor';

    // The associated SQL table for this Model
    public $table = 'cdr';

    // Name of View
    public static function view_headline()
    {
        return 'VoipMonitor Call Data Records';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-phone"></i>';
    }

    public static function view_no_entries()
    {
        return 'No CDRs found: 1. Is VoipMonitor running? 2. Does remote VoipMonitor have access to local DB? 3. Is MySQL port open to remote VoipMonitor?';
    }

    // There are no validation rules
    public static function rules($id = null)
    {
        return [];
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
                'index_header' => [$this->table.'.calldate', $this->table.'.caller', $this->table.'.called', $this->table.'.mos_min_mult10'],
                'header' =>  'Caller: '.$this->caller.' (Start: '.$this->calldate.')',
                'bsclass' => $bsclass,
                'edit' => ['mos_min_mult10' => 'mos_min_normalized'],
                'order_by' => ['0' => 'desc'], ];
    }

    public function get_bsclass()
    {
        if ($this->mos_min_mult10 > 40) {
            $bsclass = 'success';
        } elseif ($this->mos_min_mult10 > 30) {
            $bsclass = 'info';
        } elseif ($this->mos_min_mult10 > 20) {
            $bsclass = 'warning';
        } else {
            $bsclass = 'danger';
        }

        return $bsclass;
    }

    public function mos_min_normalized()
    {
        return $this->mos_min_mult10 / 10;
    }

    /**
     * All Relations
     *
     * link with phonenumbers
     */
    public function phonenumber()
    {
        return $this->belongsTo('Modules\ProvVoip\Entities\Phonenumber', 'phonenumber_id');
    }

    // Belongs to a phonenumber
    public function view_belongs_to()
    {
        return $this->phonenumber;
    }
}
