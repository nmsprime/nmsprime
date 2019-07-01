<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;

class EnviaContract extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'enviacontract';

    // do not auto delete anything related to envia (can e.g. be contracts and modems)
    protected $delete_children = false;

    protected $fillable = [];

    // Name of View
    public static function view_headline()
    {
        return 'envia TEL contract';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-handshake-o"></i>';
    }

    // link title in index view
    public function view_index_label()
    {
        $envia_contract_reference = $this->get_envia_contract_reference();
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
                'index_header' => [$this->table.'.envia_contract_reference', $this->table.'.state', $this->table.'.start_date', $this->table.'.end_date', 'contract.number', 'modem.id'],
                'eager_loading' => ['contract', 'modem'],
                'bsclass' => $bsclass,
                'edit' => ['envia_contract_reference' => 'get_envia_contract_reference', 'state' => 'get_state', 'start_date' => 'get_start_date', 'end_date' => 'get_end_date', 'contract.number' => 'get_contract_nr', 'modem.id' => 'get_modem_id'],
                'header' => $envia_contract_reference,
                'raw_columns' => ['contract.number', 'modem.id'],
        ];
    }

    public function get_bsclass()
    {
        $state = is_null($this->state) ? '–' : $this->state;

        if (in_array($state, ['Aktiv'])) {
            $bsclass = 'success';
        } elseif (in_array($state, ['Gekündigt'])) {
            $bsclass = 'danger';
        } elseif (in_array($state, ['In Realisierung'])) {
            $bsclass = 'warning';
        } else {
            $bsclass = 'info';
        }

        return $bsclass;
    }

    public function get_state()
    {
        $state = is_null($this->state) ? '–' : $this->state;

        return $state;
    }

    public function get_start_date()
    {
        $start_date = is_null($this->start_date) ? '–' : $this->start_date;

        return $start_date;
    }

    public function get_end_date()
    {
        $end_date = is_null($this->end_date) ? '–' : $this->end_date;

        return $end_date;
    }

    public function get_envia_contract_reference()
    {
        $envia_contract_reference = is_null($this->envia_contract_reference) ? '–' : $this->envia_contract_reference;

        return $envia_contract_reference;
    }

    public function get_contract_nr()
    {
        if (! $this->contract_id) {
            $contract_nr = '–';
        } else {
            $contract = Contract::withTrashed()->where('id', $this->contract_id)->first();
            if (! is_null($contract->deleted_at)) {
                $contract_nr = '<s>'.$contract->number.'</s>';
            } else {
                $contract_nr = '<a href="'.\URL::route('Contract.edit', [$this->contract_id]).'" target="_blank">'.$contract->number.'</a>';
            }
        }

        return $contract_nr;
    }

    public function get_modem_id()
    {
        if (! $this->modem_id) {
            $modem_id = '–';
        } else {
            $modem = Modem::withTrashed()->where('id', $this->modem_id)->first();
            if (! is_null($modem->deleted_at)) {
                $modem_id = '<s>'.$this->modem_id.'</s>';
            } else {
                $modem_id = '<a href="'.\URL::route('Modem.edit', [$this->modem_id]).'" target="_blank">'.$this->modem_id.'</a>';
            }
        }

        return $modem_id;
    }

    /* // View Relation. */
    /* public function view_has_many() */
    /* { */
    /* 	$ret = array(); */
    /* 	$ret['Edit']['Contract'] = $this->contract; */
    /* 	/1* $ret['Edit']['Modem'] = $this->modem; *1/ */

    /* 	return $ret; */
    /* } */

    // the relations

    /**
     * Link to contract
     */
    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
    }

    /**
     * Link to modem
     */
    public function modem()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Modem', 'modem_id');
    }

    /**
     * Link to enviaorders
     */
    public function enviaorders()
    {
        return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrder', 'enviacontract_id');
    }

    /**
     * Link to phonenumbermanagements
     */
    public function phonenumbermanagements()
    {
        return $this->hasMany('Modules\ProvVoip\Entities\PhonenumberManagement', 'enviacontract_id');
    }

    /**
     * Link to phonenumbers
     */
    public function phonenumbers()
    {
        $phonenumbers = [];
        foreach ($this->phonenumbermanagements as $mgmt) {
            array_push($phonenumbers, $mgmt->phonenumber);
        }

        return collect($phonenumbers);
        /* return $this->hasManyThrough('Modules\ProvVoip\Entities\Phonenumber', 'Modules\ProvVoip\Entities\PhonenumberManagement', 'enviacontract_id'); */
        /* return $this->hasManyThrough('Modules\ProvVoip\Entities\Phonenumber', 'Modules\ProvVoip\Entities\PhonenumberManagement'); */
    }

    /**
     * Gets all phonenumbers with:
     *		- existing phoneunmbermanagement
     *		- activation date less or equal than today
     *		- deactivation date null or bigger than today
     *
     * @author Patrick Reichel
     */
    public function phonenumbers_active_through_phonenumbermanagent()
    {
        $phonenumbers = $this->phonenumbers();

        $isodate = substr(date('c'), 0, 10);

        $ret = [];
        foreach ($phonenumbers as $phonenumber) {
            $mgmt = $phonenumber->phonenumbermanagement;

            // activation date not set
            if (is_null($mgmt->activation_date)) {
                continue;
            }

            // not yet activated
            if ($mgmt->activation_date > $isodate) {
                continue;
            }

            // deactivation date set and today or in the past
            if (
                (! is_null($mgmt->deactivation_date))
                &&
                ($mgmt->deactivation_date <= $isodate)
            ) {
                continue;
            }

            // number seems to be active
            array_push($ret, $phonenumber);
        }

        return $ret;
    }

    /**
     * Build an array containing all relations of this contract for edit view
     *
     * @author Patrick Reichel
     */
    public function get_relation_information()
    {
        $relations = [];
        $relations['head'] = '';
        $relations['hints'] = [];
        $relations['links'] = [];

        if ($this->contract_id) {
            $contract = Contract::withTrashed()->find($this->contract_id);
            $relations['hints']['Contract'] = ProvVoipEnviaHelpers::get_user_action_information_contract($contract);
        }

        if ($this->modem_id) {
            $modem = Modem::withTrashed()->find($this->modem_id);
            $relations['hints']['Modem'] = ProvVoipEnviaHelpers::get_user_action_information_modem($modem);
        }

        $mgmts = $this->phonenumbermanagements;
        if ($mgmts) {
            $phonenumbers = [];
            foreach ($mgmts as $mgmt) {
                array_push($phonenumbers, $mgmt->phonenumber);
            }
            $this->phonenumbers = collect($phonenumbers);
            $relations['hints']['Phonenumbers'] = ProvVoipEnviaHelpers::get_user_action_information_phonenumbers($this, $this->phonenumbers);
        }

        if ($this->enviaorders) {
            $relations['hints']['envia TEL orders'] = ProvVoipEnviaHelpers::get_user_action_information_enviaorders($this->enviaorders->sortBy('orderdate'));
        }

        return $relations;
    }

    /**
     * We do not delete envia TEL contracts directly (e.g. on deleting a phonenumber).
     * This is later done using a cronjob that deletes all orphaned contracts.
     *
     * This method will return true so that related models can be deleted.
     *
     * @author Patrick Reichel
     */
    public function delete()
    {

        // check from where the deletion request has been triggered and set the correct var to show information
        $prev = explode('?', \URL::previous())[0];
        $prev = \Str::lower($prev);

        $msg = 'Deletion of envia TEL contracts will be done via cron job';
        if (\Str::endsWith($prev, 'edit')) {
            \Session::push('tmp_info_above_relations', $msg);
        } else {
            \Session::push('tmp_info_above_index_list', $msg);
        }

        return true;
    }
}
