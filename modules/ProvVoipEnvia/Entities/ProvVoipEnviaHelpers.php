<?php

namespace Modules\ProvVoipEnvia\Entities;

class ProvVoipEnviaHelpers
{
    /**
     * Build the table HTML for given data.
     *
     * @param $data array containing the rows of the table (first is used as header)
     *					each row has to be given as an array holding the cols of this row
     *
     * @return raw HTML string for direct use
     *
     * @author Patrick Reichel
     */
    protected static function _get_user_action_table($data)
    {
        $replace_func = function ($data) {
            $placeholders = [
                'placeholder_yes' => '<span class="text-success centerblock">&#10004;</span>',
                'placeholder_no' => '<span class="text-danger centerblock">&#10008;</span>',
                'placeholder_unset' => '–',
            ];
            foreach ($placeholders as $placeholder => $replacement) {
                $data = str_replace($placeholder, $replacement, $data);
            }

            return $data;
        };

        $td_style = 'padding-left: 5px; padding-right: 5px; vertical-align: top;';
        $th_style = $td_style.' padding-bottom: 4px; padding-top: 4px;';

        $ret = '';

        // the tables head
        $ret = '<table class="table-hover">';
        $ret .= '<thead><tr>';
        foreach (array_shift($data) as $col) {
            $ret .= '<th style="'.$th_style.'">'.$col.'</th>';
        }
        $ret .= '</tr></thead>';

        $ret .= '<tbody>';

        // the tables body (row by row)
        foreach ($data as $row) {
            $ret .= '<tr>';
            foreach ($row as $col) {
                $ret .= '<td style="'.$td_style.'">';
                $ret .= $replace_func($col);
                $ret .= '</td>';
            }
            $ret .= '</tr>';
        }

        $ret .= '</tbody>';

        $ret .= '</table>';

        return $ret;
    }

    /**
     * Create table containing information about the contract
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_contract($contract)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaOrder.number'),
            trans('provvoipenvia::view.enviaOrder.address'),
            trans('provvoipenvia::view.enviaOrder.contractStart'),
            trans('provvoipenvia::view.enviaOrder.contractEnd'),
            trans('provvoipenvia::view.enviaOrder.hasInternet'),
            trans('provvoipenvia::view.enviaOrder.hasTelephony'),
        ];
        array_push($data, $head);

        $row = [];

        if (is_null($contract->deleted_at)) {
            array_push($row, '<a href="'.\URL::route('Contract.edit', ['Contract' => $contract->id]).'">'.$contract->number.'</a>');
        } else {
            array_push($row, "<s>$contract->number</s>");
        }

        $tmp_address = '';
        $tmp_address .= (boolval($contract->company) ? $contract->company.'<br>' : '');
        $tmp_address .= (boolval($contract->firstname) ? $contract->firstname.' ' : '');
        $tmp_address .= (boolval($contract->lastname) ? $contract->lastname : '');
        $tmp_address .= ((boolval($contract->firstname) || boolval($contract->lastname)) ? '<br>' : '');
        $tmp_address .= (boolval($contract->district) ? $contract->district.'<br>' : '');
        $tmp_address .= $contract->street.(boolval($contract->house_number) ? '&nbsp;'.$contract->house_number : '').'<br>';
        $tmp_address .= $contract->city;
        array_push($row, $tmp_address);

        array_push($row, boolval($contract->contract_start) ? $contract->contract_start : 'placeholder_unset');
        array_push($row, boolval($contract->contract_end) ? $contract->contract_end : 'placeholder_unset');
        array_push($row, ($contract->internet_access > 0 ? 'placeholder_yes' : 'placeholder_no'));
        array_push($row, ($contract->has_telephony > 0 ? 'placeholder_yes' : 'placeholder_no'));

        array_push($data, $row);

        $ret = static::_get_user_action_table($data);

        return $ret;
    }

    /**
     * Create table containing information about related items
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_items($items)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaOrder.product'),
            trans('provvoipenvia::view.enviaOrder.type'),
            trans('provvoipenvia::view.enviaOrder.validFrom'),
            trans('provvoipenvia::view.enviaOrder.fix'),
            trans('provvoipenvia::view.enviaOrder.validTo'),
            trans('provvoipenvia::view.enviaOrder.fix'),
        ];
        array_push($data, $head);

        foreach ($items as $item) {
            if (! in_array(\Str::lower($item->product->type), ['internet', 'voip'])) {
                continue;
            }

            $row = [];

            array_push($row, '<a href="'.\URL::route('Item.edit', ['Item' => $item->id]).'">'.$item->product->name.'</a>');
            array_push($row, $item->product->type);
            array_push($row, (boolval($item->valid_from) ? $item->valid_from : 'placeholder_unset'));
            if ($item->valid_from_fixed > 0) {
                array_push($row, 'placeholder_yes');
            } elseif ($item->valid_from) {
                array_push($row, 'placeholder_no');
            } else {
                array_push($row, '');
            }
            array_push($row, (boolval($item->valid_to) ? $item->valid_to : 'placeholder_unset'));
            if ($item->valid_to_fixed > 0) {
                array_push($row, 'placeholder_yes');
            } elseif ($item->valid_to) {
                array_push($row, 'placeholder_no');
            } else {
                array_push($row, '');
            }

            array_push($data, $row);
        }

        $ret = static::_get_user_action_table($data);

        return $ret;
    }

    /**
     * Create table containing information about the modem
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_modem($modem)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaOrder.macAddress'),
            trans('provvoipenvia::view.enviaOrder.hostname'),
            trans('provvoipenvia::view.enviaOrder.installationAddress'),
            trans('provvoipenvia::view.enviaOrder.configfile'),
            trans('provvoipenvia::view.enviaOrder.qos'),
            trans('provvoipenvia::view.enviaOrder.hasInternet'),
        ];
        array_push($data, $head);

        $row = [];

        if (is_null($modem->deleted_at)) {
            array_push($row, '<a href="'.\URL::route('Modem.edit', ['Modem' => $modem->id]).'">'.$modem->mac.'</a>');
        } else {
            array_push($row, "<s>$modem->mac</s>");
        }
        array_push($row, $modem->hostname);

        $tmp_address = '';
        $tmp_address .= (boolval($modem->company) ? $modem->company.'<br>' : '');
        $tmp_address .= (boolval($modem->firstname) ? $modem->firstname.' ' : '');
        $tmp_address .= (boolval($modem->lastname) ? $modem->lastname : '');
        $tmp_address .= ((boolval($modem->firstname) || boolval($modem->lastname)) ? '<br>' : '');
        $tmp_address .= (boolval($modem->district) ? $modem->district.'<br>' : '');
        $tmp_address .= $modem->street.(boolval($modem->house_number) ? '&nbsp;'.$modem->house_number : '').'<br>';
        $tmp_address .= $modem->city;
        array_push($row, $tmp_address);

        if ($modem->configfile) {
            array_push($row, $modem->configfile->name);
        } else {
            array_push($row, '–');
        }

        if ($modem->qos) {
            array_push($row, $modem->qos->name);
        } else {
            array_push($row, '–');
        }
        array_push($row, ($modem->internet_access > 0 ? 'placeholder_yes' : 'placeholder_no'));

        array_push($data, $row);

        $ret = static::_get_user_action_table($data);

        return $ret;
    }

    /**
     * Create table containing information about related phonenumbers
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_phonenumbers($model, $phonenumbers)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaOrder.phonenumber'),
            trans('provvoipenvia::view.enviaOrder.activationDate'),
            trans('provvoipenvia::view.enviaOrder.activationDateEnvia'),
            trans('provvoipenvia::view.enviaOrder.deactivationDate'),
            trans('provvoipenvia::view.enviaOrder.deactivationDateEnvia'),
            trans('provvoipenvia::view.enviaOrder.active'),
        ];
        array_push($data, $head);

        $closely_related = [];
        $distantly_related = [];

        // helper to wrap weak related informations
        $wrap = function ($content, $direct_related) {
            if (! $direct_related) {
                $content = "<i>$content</i>";
            }

            return $content;
        };

        foreach ($phonenumbers as $phonenumber) {
            $direct_related = $model->phonenumbers->contains($phonenumber) ?: false;

            $row = [];
            $phonenumbermanagement = $phonenumber->phonenumbermanagement;

            if (! is_null($phonenumbermanagement)) {
                $tmp = '<a href="'.\URL::route('PhonenumberManagement.edit', ['phonenumbermanagement' => $phonenumbermanagement->id]).'">'.$phonenumber->prefix_number.'/'.$phonenumber->number.'</a>';
            } else {
                $tmp = '<a href="'.\URL::route('Phonenumber.edit', ['phonenumber' => $phonenumber->id]).'">'.$phonenumber->prefix_number.'/'.$phonenumber->number.'</a>';
            }

            array_push($row, $wrap($tmp, $direct_related));

            if (! is_null($phonenumbermanagement)) {
                array_push($row, $wrap((boolval($phonenumbermanagement->activation_date) ? $phonenumbermanagement->activation_date : 'placeholder_unset'), $direct_related));
                array_push($row, $wrap((boolval($phonenumbermanagement->external_activation_date) ? $phonenumbermanagement->external_activation_date : 'placeholder_unset'), $direct_related));
                array_push($row, $wrap((boolval($phonenumbermanagement->deactivation_date) ? $phonenumbermanagement->deactivation_date : 'placeholder_unset'), $direct_related));
                array_push($row, $wrap((boolval($phonenumbermanagement->external_deactivation_date) ? $phonenumbermanagement->external_deactivation_date : 'placeholder_unset'), $direct_related));
            } else {
                array_push($row, $wrap('mgmt n/a', $direct_related));
                array_push($row, $wrap('mgmt n/a', $direct_related));
                array_push($row, $wrap('mgmt n/a', $direct_related));
                array_push($row, $wrap('mgmt n/a', $direct_related));
            }

            array_push($row, $wrap(($phonenumber->active > 0 ? 'placeholder_yes' : 'placeholder_no'), $direct_related));

            if ($direct_related) {
                array_push($closely_related, $row);
            } else {
                array_push($distantly_related, $row);
            }
        }

        $relation_placeholder = [];

        // create the placeholder if there are closely and distantly related phonenumbers
        if ($closely_related && $distantly_related) {
            // for every col in last row: add a col to our placeholder
            $placeholder_row = [];
            foreach ($row as $_) {
                /* array_push($placeholder_row, "<div style='font-size: 8px;'>&nbsp;</div>"); */
                array_push($placeholder_row, "<hr style='margin: 4px 0'>");
            }
            array_push($relation_placeholder, $placeholder_row);
        }

        $data = array_merge($data, $closely_related, $relation_placeholder, $distantly_related);

        $ret = static::_get_user_action_table($data);

        return $ret;
    }

    /**
     * Create table containing information about related enviacontract
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_enviacontract($enviacontract)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaOrder.enviaTelContractId'),
            trans('provvoipenvia::view.enviaOrder.state'),
            trans('provvoipenvia::view.enviaOrder.contractStart'),
            trans('provvoipenvia::view.enviaOrder.contractEnd'),
        ];
        array_push($data, $head);

        $row = [
            '<a href="'.\URL::route('EnviaContract.edit', ['EnviaContract' => $enviacontract->id]).'">'.$enviacontract->envia_contract_reference.'</a>',
            $enviacontract->state ? $enviacontract->state : '–',
            $enviacontract->start_date ? $enviacontract->start_date : '–',
            $enviacontract->end_date ? $enviacontract->end_date : '–',
        ];
        array_push($data, $row);

        $ret = static::_get_user_action_table($data);

        return $ret;
    }

    /**
     * Create table containing information about related envia orders
     *
     * @author Patrick Reichel
     */
    public static function get_user_action_information_enviaorders($enviaorders)
    {
        $data = [];

        $head = [
            trans('provvoipenvia::view.enviaContract.orderId'),
            trans('provvoipenvia::view.enviaContract.ordertype'),
            trans('provvoipenvia::view.enviaContract.orderdate'),
            trans('provvoipenvia::view.enviaContract.orderstatus'),
            trans('provvoipenvia::view.enviaContract.method'),
        ];
        array_push($data, $head);

        foreach ($enviaorders as $enviaorder) {
            $row = [
                '<a href="'.\URL::route('EnviaOrder.edit', ['EnviaOrder' => $enviaorder->id]).'">'.$enviaorder->orderid.'</a>',
                $enviaorder->ordertype ? $enviaorder->ordertype : '–',
                $enviaorder->orderdate ? $enviaorder->orderdate : '–',
                $enviaorder->orderstatus ? $enviaorder->orderstatus : 'n/a',
                $enviaorder->method ? $enviaorder->method : '–',
            ];
            array_push($data, $row);
        }

        $ret = static::_get_user_action_table($data);

        return $ret;
    }
}
