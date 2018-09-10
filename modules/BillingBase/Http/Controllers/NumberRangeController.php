<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\NumberRange;

class NumberRangeController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'start', 'description' => 'Start'],
            ['form_type' => 'text', 'name' => 'end', 'description' => 'End'],
            ['form_type' => 'text', 'name' => 'prefix', 'description' => 'Prefix'],
            ['form_type' => 'text', 'name' => 'suffix', 'description' => 'Suffix'],
            ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'CostCenter', 'value' => $model->html_list(CostCenter::all(), 'name')],

            // type is hidden – ATM we use only contract – left the old line here for later use
            /* ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => NumberRange::get_types()], */
            ['form_type' => 'text', 'name' => 'type', 'description' => 'Type', 'value' => 'contract', 'hidden' => 1],
        ];
    }

    public function prepare_input($data)
    {
        $data['prefix'] = trim($data['prefix']);
        $data['suffix'] = trim($data['suffix']);

        return parent::prepare_input($data);
    }

    public function prepare_rules($rules, $data)
    {
        $rules['end'] .= $data['start'] ? '|min:'.$data['start'] : '';

        return parent::prepare_rules($rules, $data);
    }
}
