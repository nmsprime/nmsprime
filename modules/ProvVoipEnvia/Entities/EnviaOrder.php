<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;

// Model not found? execute composer dump-autoload in nmsprime root dir
class EnviaOrder extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'enviaorder';

    // do not auto delete anything related to envia orders (can e.g. be contracts, modems or phonenumbers)
    // deletion of orders (and related order documents is later done in separate cron job) – so we don't have to care here
    protected $delete_children = false;

    // collect all order related informations ⇒ later we can use subarrays of this array to get needed informations
    // mark missing data with value null
    protected static $meta = [

        // TODO: Process the list with all possible ordertypes
        'orders' => [
            [
                'ordertype' => 'Neuschaltung envia TEL voip reselling',
                'ordertype_id' => 1,
                'method' => 'contract/create',
                'phonenumber_related' => false,
            ],
            [	// I don't know why – but envia TEL has (at least) two IDs for this ordertype – maybe for creates with and without attached phonenumbers?
                'ordertype' => 'Neuschaltung envia TEL voip reselling',
                'ordertype_id' => 2,
                'method' => 'contract/create',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Sprachtarif wird geändert',
                'ordertype_id' => 12,
                'method' => 'contract/change_tariff',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Neuschaltung einer Rufnummer',
                'ordertype_id' => 19,
                'method' => 'voip_account/create',
                'phonenumber_related' => true,
            ],
            [
                'ordertype' => 'Stornierung eines Auftrags',
                'ordertype_id' => 20,
                'method' => 'order/cancel',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Änderung der Rufnummernkonfiguration',
                'ordertype_id' => 21,
                'method' => 'voip_account/update',
                'phonenumber_related' => true,
            ],
            [
                'ordertype' => 'Änderung des Einkaufstarifs',
                'ordertype_id' => 22,
                'method' => 'contract/change_variation',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Kündigung einer Rufnummer',
                'ordertype_id' => 23,
                'method' => 'voip_account/terminate',
                'phonenumber_related' => true,
            ],
            [
                'ordertype' => 'Änderung der Kundendaten',
                'ordertype_id' => 27,
                'method' => 'customer/update',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Umzug',
                'ordertype_id' => 70,
                'method' => 'contract/relocate',
                'phonenumber_related' => false,
            ],
            [
                'ordertype' => 'Kündigung envia TEL voip reselling',	// TODO: Add correct string given by envia TEL
                'ordertype_id' => null,
                'method' => 'contract/terminate',
                'phonenumber_related' => false,
            ],
            /* array( */
            /* 	'ordertype' => '', */
            /* 	'ordertype_id' => , */
            /* 	'method' => '', */
            /* 	'phonenumber_related' => , */
            /* ), */
        ],
        'states' => [
            [
                'orderstatus_id' => 1000,
                'orderstatus' => 'in Bearbeitung',
                'view_class' => 'info',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1001,
                'orderstatus' => 'erfolgreich verarbeitet',
                'view_class' => 'success',
                'state_type' => 'success',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1002,
                'orderstatus' => 'keine Teilnehmeranschlussleitung möglich / Auftrag nicht realisierbar',
                'view_class' => 'danger',
                'state_type' => 'failed',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1003,
                'orderstatus' => 'Teilnehmeranschlussleitung bestätigt/Warte auf Anschaltung',
                'view_class' => 'info',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1009,
                'orderstatus' => 'Warte auf Portierungserklärung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1010,
                'orderstatus' => 'Terminverschiebung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1012,
                'orderstatus' => 'Dokument fehlerhaft oder nicht lesbar',
                'view_class' => 'danger',
                'state_type' => 'pending',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1013,
                'orderstatus' => 'Warte auf Portierungsbestätigung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1014,
                'orderstatus' => 'Fehlgeschlagen, Details siehe Bemerkung',
                'view_class' => 'danger',
                'state_type' => 'failed',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1015,
                'orderstatus' => 'Schaltung bestätigt zum Zieltermin',
                'view_class' => 'success',
                'state_type' => 'success',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1016,
                'orderstatus' => 'Warte auf Bearbeitung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1017,
                'orderstatus' => 'Stornierung bestätigt',
                'view_class' => 'success',
                'state_type' => 'success',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1018,
                'orderstatus' => 'Stornierung nicht möglich',
                'view_class' => 'danger',
                'state_type' => 'failed',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1019,
                'orderstatus' => 'Warte auf Zieltermin',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1020,
                'orderstatus' => 'In Kündigung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1028,
                'orderstatus' => 'Produkt am neuen Standort nicht verfügbar',
                'view_class' => 'warning',
                'state_type' => 'failed',
                'final' => true,
            ],
            [
                'orderstatus_id' => 1030,
                'orderstatus' => 'Warte auf Rückmeldung Lieferant',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1036,
                'orderstatus' => 'Eskalationsstufe 1 - Warte auf Portierungsbestätigung',
                'view_class' => 'danger',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1037,
                'orderstatus' => 'Eskalationsstufe 2 - Warte auf Portierungsbestätigung',
                'view_class' => 'danger',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1038,
                'orderstatus' => 'Portierungsablehnung, siehe Bemerkung',
                'view_class' => 'danger',
                'state_type' => 'failed',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1039,
                'orderstatus' => 'Warte auf Zieltermin kleiner gleich 180 Kalendertage',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1040,
                'orderstatus' => 'Warte auf KVz Fertigstellung',
                'view_class' => 'warning',
                'state_type' => 'pending',
                'final' => false,
            ],
            [
                'orderstatus_id' => 1041,
                'orderstatus' => 'Negativmeldung Telekom erhalten',
                'view_class' => 'danger',
                'state_type' => 'pending',
                'final' => false,
            ],
        ],

    ];

    /**
     * Constructor
     *
     * @author Patrick Reichel
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        // preserve currently set show filter for later use in datatable calls
        $this->store_index_show_filter_in_session();
    }

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // Prevent users from creating orders (table enviaorder is only changable through envia TEL API!)
            // TODO: later remove delete button
            'orderid' => 'required|integer|min:1',
            'related_order_id' => 'exists:enviaorder,id,deleted_at,NULL',
        ];
    }

    // Don't forget to fill this array
    protected $fillable = [
        'orderid',
        'method',
        'ordertype_id',
        'ordertype',
        'orderstatus_id',
        'orderstatus',
        'orderdate',
        'ordercomment',
        'related_order_id',
        'customerreference',
        'contractreference',
        'contract_id',
        'modem_id',
        'enviacontract_id',
    ];

    /**
     * Get the order subarray from meta
     *
     * @author Patrick Reichel
     *
     * @return array containing metadata for all order types:
     *			ordertype (string),
     *			ordertype_id (int),
     *			method (str)
     *			phonenumber_related (bool)
     */
    public static function get_orders_metadata()
    {
        return self::$meta['orders'];
    }

    /**
     * Get the stats subarray from meta
     *
     * @author Patrick Reichel
     *
     * @return array containing metadata for all order states:
     *			orderstatus_id (int),
     *			orderstatus (str),
     *			view_class (str),
     *			state_type (str),
     *			final (bool),
     */
    public static function get_states_metadata()
    {
        return self::$meta['states'];
    }

    /**
     * Checks if an orderstatus is final
     *
     * @author Patrick Reichel
     *
     * @param $order to check
     *
     * @return true if orderstatus is final (will not change anymore), else false
     */
    public static function orderstate_is_final($order)
    {
        $finals = [];
        foreach (self::$meta['states'] as $state_meta) {
            $final = $state_meta['final'];
            $type = $state_meta['orderstatus'];
            $id = $state_meta['orderstatus_id'];

            if ($final) {
                if (! is_null($type)) {
                    array_push($finals, $type);
                }
                if (! is_null($id)) {
                    array_push($finals, $id);
                }
            }
        }

        $final_state = (
            in_array($order->orderstatus_id, $finals)
            ||
            in_array($order->orderstatus, $finals)
        );

        return $final_state;
    }

    /**
     * Checks if a given ordertype is phonenmumber related
     *
     * @param $order order to check
     *
     * @author Patrick Reichel
     */
    public static function ordertype_is_phonenumber_related($order)
    {
        $relates = [];
        foreach (self::$meta['orders'] as $order_meta) {
            $related = $order_meta['phonenumber_related'];
            $type = $order_meta['ordertype'];
            $id = $order_meta['ordertype_id'];

            if ($related) {
                if (! is_null($type)) {
                    array_push($relates, $type);
                }
                if (! is_null($id)) {
                    array_push($relates, $id);
                }
            }
        }

        $related_state = (
            in_array($order->ordertype, $relates)
            ||
            in_array($order->ordertype_id, $relates)
            ||
            in_array($order->method, $relates)
        );

        return $related_state;
    }

    /**
     * Checks if an order is related to a given method.
     * Call this in your specialized methods
     *
     * @author Patrick Reichel
     */
    protected static function _order_mapped_to_method($order, $method)
    {
        $matches = [];
        foreach (self::$meta['orders'] as $order_meta) {
            $cur_method = $order_meta['method'];
            if ($cur_method == $method) {
                $cur_type = $order_meta['ordertype'];
                $cur_id = $order_meta['ordertype_id'];

                array_push($matches, $cur_method);
                if (! is_null($cur_type)) {
                    array_push($matches, $cur_type);
                }
                if (! is_null($cur_id)) {
                    array_push($matches, $cur_id);
                }
            }
        }

        $mapped_to_method = (
            in_array($order->ordertype, $matches)
            ||
            in_array($order->ordertype_id, $matches)
            ||
            in_array($order->method, $matches)
        );

        return $mapped_to_method;
    }

    /**
     * Checks if an order matches a given state_type.
     * Use this in your concrete checks
     *
     * @author Patrick Reichel
     */
    protected static function _order_mapped_to_state_type($order, $state_type)
    {
        $matches = [];
        foreach (self::$meta['states'] as $state_meta) {
            $cur_state_type = $state_meta['state_type'];
            if ($cur_state_type == $state_type) {
                $cur_state = $state_meta['orderstatus'];
                $cur_id = $state_meta['orderstatus_id'];
                if (! is_null($cur_state)) {
                    array_push($matches, $cur_state);
                }
                if (! is_null($cur_id)) {
                    array_push($matches, $cur_id);
                }
            }
        }

        $mapped_to_state_type = (
            in_array($order->orderstatus, $matches)
            ||
            in_array($order->orderstatus_id, $matches)
        );

        return $mapped_to_state_type;
    }

    /**
     * Check if order has successfully been cancelled
     * We assume this if the orderstate indicates it and the ordertype is not order/cancel.
     *
     * TODO: check what happens if we try to cancel an order that cancelled another order…
     *
     * @author Patrick Reichel
     */
    public static function order_successfully_cancelled($order)
    {
        if (
            (($order->orderstatus_id == 1017) || ($order->orderstatus == 'Stornierung bestätigt'))
            &&
            ($order->ordertype != 'Stornierung eines Auftrags')
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if order is successfully processed.
     *
     * @author Patrick Reichel
     */
    public static function order_successful($order)
    {
        return self::_order_mapped_to_state_type($order, 'success');
    }

    /**
     * Check if order is pending.
     *
     * @author Patrick Reichel
     */
    public static function order_pending($order)
    {
        return self::_order_mapped_to_state_type($order, 'pending');
    }

    /**
     * Check if order has failed
     *
     * @author Patrick Reichel
     */
    public static function order_failed($order)
    {
        return self::_order_mapped_to_state_type($order, 'failed');
    }

    /**
     * Checks if order is related to creation of a phonenumber in every case
     *
     * @author Patrick Reichel
     */
    public static function order_creates_voip_account($order)
    {
        return self::_order_mapped_to_method($order, 'voip_account/create');
    }

    /**
     * Checks if order can be used to create a phonenumber (but not in each case does)
     *
     * @author Patrick Reichel
     */
    public static function order_possibly_creates_voip_account($order)
    {
        return self::_order_mapped_to_method($order, 'contract/create');
    }

    /**
     * Checks if order is related to termination of a phonenumber in every case
     *
     * @author Patrick Reichel
     */
    public static function order_terminates_voip_account($order)
    {
        return self::_order_mapped_to_method($order, 'voip_account/terminate');
    }

    /**
     * Checks if order is related to termination of a phonenumber
     *
     * @author Patrick Reichel
     */
    public static function order_possibly_terminates_voip_account($order)
    {
        return self::_order_mapped_to_method($order, 'contract/terminate');
    }

    /**
     * Checks if order cancels another order
     *
     * @author Patrick Reichel
     */
    public static function order_cancels_other_order($order)
    {
        return self::_order_mapped_to_method($order, 'order/cancel');
    }

    // Name of View
    public static function view_headline()
    {
        return 'envia TEL orders';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-shopping-cart"></i>';
    }

    public function view_index_label()
    {
        // combine all possible orderstatus IDs with GUI colors
        $colors = [];
        foreach (self::$meta['states'] as $state) {
            $colors[$state['orderstatus_id']] = $state['view_class'];
        }

        if (! boolval($this->orderstatus_id)) {
            $bsclass = 'info';
        } else {
            $bsclass = $colors[$this->orderstatus_id];
        }

        // get the current show filter
        // as we called */EnviaOrder/datatables we have to get this information from session
        $show_filter = \Session::get('enviaorder_show_filter', 'all');
        if ($show_filter == 'all') {
            $where_clauses = [];
        } else {
            $where_clauses = [self::get_user_interaction_needing_enviaorder_where_clause()];
        }

        return ['table' => $this->table,
                'index_header' => [$this->table.'.ordertype', $this->table.'.orderstatus', 'escalation_level', 'contract.number', 'modem.id', 'phonenumber.number', 'enviacontract.envia_contract_reference',  $this->table.'.created_at', $this->table.'.updated_at', $this->table.'.orderdate', 'enviaorder_current'],
                'bsclass' => $bsclass,
                'disable_sortsearch' => ['phonenumber.number'],
                'eager_loading' => ['modem', 'contract', 'enviacontract', 'phonenumbers'],
                'edit' => ['ordertype' => 'get_ordertype', 'orderstatus'  => 'get_orderstatus', 'modem.id' => 'get_modem_id', 'contract.number' => 'get_contract_nr', 'enviacontract.envia_contract_reference' => 'get_enviacontract_ref', 'enviaorder_current' => 'get_user_interaction_necessary', 'phonenumber.number' => 'get_phonenumbers', 'escalation_level' => 'get_escalation_level'],
                'header' => $this->orderid.' – '.$this->ordertype.': '.$this->orderstatus,
                'where_clauses' => $where_clauses,
                'raw_columns' => ['contract.number', 'modem.id', 'phonenumber.number', 'enviacontract.envia_contract_reference', 'enviaorder_current'],
        ];
    }

    public function get_escalation_level()
    {
        // combine all possible orderstatus IDs with GUI colors
        $colors = [];
        foreach (self::$meta['states'] as $state) {
            $colors[$state['orderstatus_id']] = $state['view_class'];
        }

        if (! boolval($this->orderstatus_id)) {
            $bsclass = 'info';
        } else {
            $bsclass = $colors[$this->orderstatus_id];
        }
        // this is used to group the orders by their escalation levels (so later on we can sort them by these levels)
        $escalations = [
            'success' => 0,
            'info' => 1,
            'warning' => 2,
            'danger' => 3,
        ];

        $escalation_level = $escalations[$bsclass].' – '.$bsclass;

        return $escalation_level;
    }

    public function get_ordertype()
    {
        $ordertype = $this->ordertype;
        $ordertype = str_replace('Rufnummernkonfiguration', 'Rufnummern&shy;konfiguration', $ordertype);

        return $ordertype;
    }

    public function get_orderstatus()
    {
        $orderstatus = $this->orderstatus;
        $orderstatus = str_replace('Portierungserklärung', 'Portierungs&shy;erklärung', $orderstatus);

        return $orderstatus;
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

    public function get_enviacontract_ref()
    {
        if ($this->enviacontract_id) {
            $enviacontract = EnviaContract::withTrashed()->where('id', $this->enviacontract_id)->first();
            $reference = ! is_null($enviacontract->envia_contract_reference) ? $enviacontract->envia_contract_reference : 'ID: '.$this->enviacontract_id;
            if (! is_null($enviacontract->deleted_at)) {
                $enviacontract_nr = '<s>'.$reference.'</s>';
            } else {
                $enviacontract_nr = '<a href="'.\URL::route('EnviaContract.edit', [$this->enviacontract_id]).'" target="_blank">'.$reference.'</a>';
            }
        } else {
            $enviacontract_nr = '–';
        }

        return $enviacontract_nr;
    }

    public function get_user_interaction_necessary()
    {
        if (! $this->user_interaction_necessary()) {
            $current = '–';
        } else {
            $current = '<b>'.\App\Http\Controllers\BaseViewController::translate_label('Yes').'!!</b><br><a href="'.\URL::route('EnviaOrder.marksolved', ['EnviaOrder' => $this->id]).'" target="_self">'.\App\Http\Controllers\BaseViewController::translate_label('Mark solved').'</a>';
        }

        return $current;
    }

    public function get_phonenumbers()
    {
        // show all order related phonenumbers
        $phonenumber_nrs = [];
        $space_before_numbers = str_repeat('&nbsp', 3);
        foreach ($this->phonenumbers as $phonenumber) {
            $phonenumbermanagement = $phonenumber->phonenumbermanagement;

            $prefix_number = $phonenumber->prefix_number;
            // collect the prefix numbers (there should be only one per order – but who knows…)
            if (! array_key_exists($prefix_number, $phonenumber_nrs)) {
                $phonenumber_nrs[$prefix_number] = [];
            }

            $phonenumber_nr = $phonenumber->number;
            if (! is_null($phonenumbermanagement)) {
                $phonenumbermanagement_id = $phonenumber->phonenumbermanagement->id;
                $phonenumber_nr = $space_before_numbers.'<a href="'.\URL::route('PhonenumberManagement.edit', [$phonenumbermanagement_id]).'" target="_blank">'.$phonenumber_nr.'</a>';
            } else {
                $phonenumber_nr = $space_before_numbers.'<a href="'.\URL::route('Phonenumber.edit', [$phonenumber->id]).'" target="_blank">'.$phonenumber_nr.'</a>';
            }
            array_push($phonenumber_nrs[$prefix_number], $phonenumber_nr);
        }
        if (! boolval($phonenumber_nrs)) {
            $phonenumber_nrs = '–';
        } else {
            $tmp_nrs = [];
            foreach ($phonenumber_nrs as $prefix_number => $numbers) {
                $tmp = $prefix_number.'/<br>';
                $tmp .= implode('<br>', $numbers);
                array_push($tmp_nrs, $tmp);
            }
            $phonenumber_nrs = implode('<br><br>', $tmp_nrs);
        }

        return $phonenumber_nrs;
    }

    /**
     * Get the filter to use for index view (used to show only user interaction needing orders).
     *
     * To make the filter information available in datatables (called by EnviaOrder/datatables without our custom GET param)
     * we use the session.
     *
     * @author Patrick Reichel
     */
    public function store_index_show_filter_in_session()
    {

        // first: check context
        // if called by datatables: do nothing
        if (\Str::contains(\URL::current(), '/EnviaOrder/datatables')) {
            return;
        }

        // array containing all implemented filters
        // later used as whitelist for given show_filter param
        $available_filters = [
            'all', // default and fallback: show all orders
            'action_needed', // show only orders needing user interaction
        ];

        // check if we have to filter the list of orders
        $filter = \Input::get('show_filter', 'all');
        if (! in_array($filter, $available_filters)) {
            $filter = 'all';
        }

        \Session::put('enviaorder_show_filter', $filter);
    }

    // belongs to phonenumber or modem or contract - see BaseModel for explanation
    public function view_belongs_to()
    {
        if (! $this->phonenumbers->isEmpty()) {
            $ret = [];
            foreach ($this->phonenumbers as $phonenumber) {
                if (is_null($phonenumber->phonenumbermanagement)) {
                    array_push($ret, $phonenumber);
                } else {
                    array_push($ret, $phonenumber->phonenumbermanagement);
                }
            }

            return collect($ret);
        } elseif (boolval($this->modem_id)) {
            return $this->modem;
        } else {
            return $this->contract;
        }
    }

    // returns all objects that are related to an envia TEL Order
    public function view_has_many()
    {
        if (\Module::collections()->has('ProvVoipEnvia')) {
            $ret['envia TEL']['EnviaOrderDocument']['class'] = 'EnviaOrderDocument';
            $ret['envia TEL']['EnviaOrderDocument']['relation'] = $this->enviaorderdocument;
            $ret['envia TEL']['EnviaOrderDocument']['method'] = 'show';
            $ret['envia TEL']['EnviaOrderDocument']['options']['hide_delete_button'] = '1';
        } else {
            $ret = [];
        }

        return $ret;
    }

    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract');
    }

    public function modem()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Modem');
    }

    public function phonenumbers()
    {
        return $this->belongsToMany('Modules\ProvVoip\Entities\Phonenumber', 'enviaorder_phonenumber', 'enviaorder_id', 'phonenumber_id')->withTimestamps();
    }

    public function enviaorderdocument()
    {
        return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrderDocument', 'enviaorder_id')->orderBy('created_at');
    }

    public function enviacontract()
    {
        return $this->belongsTo('Modules\ProvVoipEnvia\Entities\EnviaContract', 'enviacontract_id');
    }

    /**
     * Get informations about necessary/possible user interaction.
     * In this first step we only provide a link to mark this open order as (manually) solved.
     * Later on we can provide hints what to do or even solve automagically
     *
     * @author Patrick Reichel
     */
    public function get_user_action_information()
    {
        $user_actions = [];
        $user_actions['head'] = '';
        $user_actions['hints'] = [];
        $user_actions['links'] = [];

        $contract = null;
        $items = null;
        $modem = null;
        $phonenumbers = [];

        if ($this->contract_id) {
            $contract = Contract::withTrashed()->find($this->contract_id);
            $user_actions['hints']['Contract (= envia TEL  Customer)'] = ProvVoipEnviaHelpers::get_user_action_information_contract($contract);
            $items = $contract->items;
        }

        if ($items) {
            $user_actions['hints']['Items (Internet and VoIP only)'] = ProvVoipEnviaHelpers::get_user_action_information_items($items);
        }

        if ($this->modem_id) {
            $modem = Modem::withTrashed()->find($this->modem_id);
            $user_actions['hints']['Modem (can hold multiple envia TEL contracts)'] = ProvVoipEnviaHelpers::get_user_action_information_modem($modem);
        }

        if ($modem) {
            $mtas = $modem->mtas;
            if ($mtas) {
                foreach ($mtas as $mta) {
                    foreach ($mta->phonenumbers as $phonenumber) {
                        if ($phonenumber) {
                            array_push($phonenumbers, $phonenumber);
                        }
                    }
                }
            }
        }

        if ($phonenumbers) {
            $user_actions['hints']['Phonenumbers (= envia TEL VoipAccounts)'] = ProvVoipEnviaHelpers::get_user_action_information_phonenumbers($this, $phonenumbers);
        }

        // show headline and link to solve if order has changed since the last user interaction
        if ($this->user_interaction_necessary()) {

            // show that user interaction is necessary
            $user_actions['head'] = '<h5 class="text-danger">envia TEL Order has been updated</h5>';
            $user_actions['head'] .= 'Please check if user interaction is necessary.<br><br>';

            // finally add link to mark open order as solved
            $user_actions['links']['Mark as solved'] = \URL::route('EnviaOrder.marksolved', ['EnviaOrder' => $this->id]);
        }

        $enviacontract = $this->enviacontract;
        if ($enviacontract) {
            $user_actions['hints']['envia TEL contract'] = ProvVoipEnviaHelpers::get_user_action_information_enviacontract($enviacontract);
        }

        return $user_actions;
    }

    /**
     * Creates the mailto: links (ready-to-use) for generation of email templates.
     *
     * @author Patrick Reichel
     */
    public function get_mailto_links()
    {
        $mailto_links = [];

        $user = \Auth::user();

        $line = str_repeat('-', 64);

        // prepare the mailto link
        $to = 'Realisierung-Standard@enviatel.de';

        // create the subject
        $subject = 'Frage zu Order '.$this->orderid;
        if ($this->ordertype) {
            $subject .= ' ('.$this->ordertype.')';
        }

        // create the mail body
        $body = "Sehr geehrte Damen und Herren,\n\n";
        $body .= "zu folgender Order habe ich eine Frage:\n\n";
        $body .= 'Order ID: '.$this->orderid."\n";
        if ($this->customerreference) {
            $body .= 'Customer reference: '.$this->customerreference."\n";
        }
        if ($this->contractreference) {
            $body .= 'Contract reference: '.$this->contractreference."\n";
        }
        if ($this->ordertype) {
            $body .= 'Order type: '.$this->ordertype."\n";
        }
        $body .= "\n\n$line\n\n\n\n$line\n\n\nMit freundlichen Grüßen\n\n";

        // add the current logged in user's name (after the greeting)
        if ($user->first_name) {
            $body .= $user->first_name;
        }
        if ($user->last_name) {
            if ($user->first_name) {
                $body .= ' ';
            }
            $body .= $user->last_name."\n";
        }

        // add to array; later on there can be multiple links to different mail addresses
        $mailto_links[$to] = 'mailto:'.$to.'?Subject='.rawurlencode($subject).'&amp;Body='.rawurlencode($body);

        return $mailto_links;
    }

    /**
     * @author Patrick Reichel
     */
    public static function get_user_interaction_needing_enviaorder_where_clause()
    {
        $where_clause = '(last_user_interaction IS NULL OR last_user_interaction < updated_at) AND ((orderstatus_id != 1000) OR ((orderstatus_id IS NULL) AND (orderstatus NOT LIKE "in Bearbeitung")))';

        return $where_clause;
    }

    /**
     * For use in layout view: get number of user interaction needing orders
     *
     * @author Patrick Reichel
     */
    public static function get_user_interaction_needing_enviaorder_count()
    {
        $count = self::whereRaw('(last_user_interaction IS NULL OR last_user_interaction < updated_at) AND ((orderstatus_id != 1000) OR ((orderstatus_id IS NULL) AND (orderstatus NOT LIKE "in Bearbeitung")) AND (orderstatus NOT LIKE "initializing"))')->count();

        return $count;
    }

    /**
     * Checks if user interaction is necessary.
     *
     * @author Patrick Reichel
     *
     * @todo add more rules for ordertype-orderstatus combinations that don't need user interaction
     */
    public function user_interaction_necessary()
    {

        // first: if last user interaction was after last status update – nothing to do
        if ($this->last_user_interaction > $this->updated_at) {
            return false;
        }

        // if current state is “in Bearbeitung” then we have to do nothing
        // next action is to perform by envia TEL
        if ($this->orderstatus == 'in Bearbeitung' || $this->orderstatus_id == 1000) {
            return false;
        }

        // currently created orders also don't need interaction
        if ($this->orderstatus == 'initializing') {
            return false;
        }

        // default (be pessimistic): we have to perform action…
        return true;
    }

    /**
     * Marks an open order as “solved“.
     * That means: update the last_user_interaction to now without touching updated_at
     *
     * @author Patrick Reichel
     */
    public function mark_as_solved()
    {

        // temporary disable refreshing of timestamps (= don't touch updated_at)
        $this->timestamps = false;

        // set last user interaction date to now
        $this->last_user_interaction = \Carbon\Carbon::now()->toDateTimeString();
        $this->save();
    }

    /**
     * We do not delete envia TEL orders directly (e.g. on deleting a phonenumber).
     * This is later done using a cronjob that deletes all orphaned orders.
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

        $msg = 'Deletion of envia TEL orders will be done via cron job';
        if (\Str::endsWith($prev, 'edit')) {
            \Session::push('tmp_info_above_relations', $msg);
        } else {
            \Session::push('tmp_info_above_index_list', $msg);
        }

        return true;
    }
}
