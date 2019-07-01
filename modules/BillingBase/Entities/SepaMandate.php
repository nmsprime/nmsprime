<?php

namespace Modules\BillingBase\Entities;

use Digitick\Sepa\PaymentInformation;
use Modules\ProvBase\Entities\Contract;

class SepaMandate extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'sepamandate';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'reference' 	=> 'required',
            'iban'          => 'required|iban',
            'bic' 			=> 'bic|regex:/^[A-Z]{6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3}){0,3}$/',			// see SepaMandateController@prep_rules, Sparkasse/S-Firm regex from error message of sepa-xml upload
            'signature_date' => 'date|required',
            'valid_from' 	=> 'date|required',
            'valid_to'		=> 'dateornull',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'SEPA Mandate';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-handshake-o"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();
        $valid_to = $this->valid_to ? ' - '.$this->valid_to : '';

        return ['table' => $this->table,
                'index_header' => [$this->table.'.holder', $this->table.'.valid_from', $this->table.'.valid_to', $this->table.'.reference'],
                'bsclass' => $bsclass,
                'order_by' => ['0' => 'asc'],
                'header' =>  "$this->reference - $this->iban - $this->valid_from $valid_to",
            ];
    }

    /**
     * Get bootstrap class for colorization of model in GUI
     *
     * @return string
     */
    public function get_bsclass()
    {
        // Dont check when table header of index page is assembled
        if (! $this->id) {
            return '';
        }

        if ($this->check_validity('now')) {
            return 'success';
        }

        if ($this->get_start_time() > strtotime(date('Y-m-d'))) {
            return 'info';
        }

        return 'active';
    }

    public function view_belongs_to()
    {
        return $this->contract;
    }

    /**
     * Relationships:
     */
    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
    }

    public function costcenter()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\CostCenter');
    }

    /*
     * Init Observers
     */
    // public static function boot()
    // {
    // 	SepaMandate::observe(new SepaMandateObserver);
    // 	parent::boot();
    // }

    /*
     * Other Functions
     */

    /**
     * Update SEPA-Mandate status during SettlementRun (SettlementRunCommand) if it changes
     */
    public function update_status()
    {
        $end = $this->get_end_time();
        $ends = $end && ($end < strtotime('first day of next month'));

        $changed = false;

        if ($this->state == PaymentInformation::S_FIRST) {
            $this->state = $ends ? PaymentInformation::S_ONEOFF : PaymentInformation::S_RECURRING;
            $changed = true;
        } elseif ($ends) {
            $this->state = PaymentInformation::S_FINAL;
            $changed = true;
        }

        if ($changed) {
            $this->save();
        }
    }

    /**
     * Returns start time of item - Note: valid_from field has higher priority than created_at
     *
     * @return int 		time in seconds after 1970
     */
    public function get_start_time()
    {
        $date = $this->valid_from && $this->valid_from != '0000-00-00' ? $this->valid_from : $this->created_at->toDateString();

        return strtotime($date);
    }

    /**
     * Returns start time of item - Note: valid_from field has higher priority than created_at
     *
     * @return int 		time in seconds after 1970
     */
    public function get_end_time()
    {
        return $this->valid_to && $this->valid_to != '0000-00-00' ? strtotime($this->valid_to) : null;
    }
}

/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class SepaMandateObserver
{
    public function creating($mandate)
    {
    }

    public function updating($mandate)
    {
    }
}
