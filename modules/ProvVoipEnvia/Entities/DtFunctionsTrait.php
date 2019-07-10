<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;

/**
 * Helper to hold functionality used for DataTables related stuff
 * used by multiple models.
 *
 * @author Patrick Reichel
 */
trait DtFunctionsTrait
{
    /**
     * Get query data used for contract filterColumn
     *
     * @author Patrick Reichel
     */
    public function get_contract_filtercolumn_query()
    {
        return ['query' => "(contract_id in (SELECT id from contract WHERE ((CONCAT(company,' ',lastname,', ',firstname) like ?))))", 'eagers' => ['contract']];
    }

    /**
     * Get query data used for modem filterColumn
     *
     * @author Patrick Reichel
     */
    public function get_modem_filtercolumn_query()
    {
        return ['query' => "(modem_id in (SELECT id from modem WHERE ((CONCAT(name,id,' ',street,' ',house_number,' ',city,'/',district) like ?))))", 'eagers' => ['modem']];
    }

    /**
     * Get data to be displayed in DataTable contract field
     *
     * @author Patrick Reichel
     */
    public function get_contract_data()
    {
        if (! $this->contract_id) {
            $contract_nr = '–';
        } else {
            $contract = Contract::withTrashed()->where('id', $this->contract_id)->first();
            $content = $contract->company ?: $contract->lastname.', '.$contract->firstname;
            if (! is_null($contract->deleted_at)) {
                $contract_nr = '<s>'.$content.'</s>';
            } else {
                $contract_nr = '<a href="'.\URL::route('Contract.edit', [$this->contract_id]).'" target="_blank">'.$content.'</a>';
            }
        }

        return $contract_nr;
    }

    /**
     * Get data to be displayed in DataTable contract field
     *
     * @author Patrick Reichel
     */
    public function get_modem_data()
    {
        if (! $this->modem_id) {
            $modem_id = '–';
        } else {
            $modem = Modem::withTrashed()->where('id', $this->modem_id)->first();
            $content = $modem->name ?: $modem->id;
            $content .= $modem->street ? '<br>'.$modem->street.' '.$modem->house_number : '';
            if ($modem->city) {
                $content .= '<br>'.$modem->city;
                $content .= $modem->district ? '/'.$modem->district : '';
            }
            if (! is_null($modem->deleted_at)) {
                $modem_id = '<s>'.$content.'</s>';
            } else {
                $modem_id = '<a href="'.\URL::route('Modem.edit', [$this->modem_id]).'" target="_blank">'.$content.'</a>';
            }
        }

        return $modem_id;
    }
}
