<?php

namespace Modules\BillingBase\Http\Controllers;

use Schema;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;

class BillingBaseController extends \BaseController
{
    public $name = 'BillingBase';

    public function view_form_fields($model = null)
    {
        $languages = BaseViewController::generateLanguageArray(BillingBase::getPossibleEnumValues('userlang'));

        $days[0] = null;
        for ($i = 1; $i < 29; $i++) {
            $days[$i] = $i;
        }

        // build data for mandate reference help string
        $contract = new Contract;
        $mandate = new SepaMandate;
        $cols1 = Schema::getColumnListing($contract->getTable());
        $cols2 = Schema::getColumnListing($mandate->getTable());
        $cols = array_merge($cols1, $cols2);

        foreach ($cols as $key => $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                unset($cols[$key]);
            }
        }

        $cols = implode(', ', $cols);

        return [
            ['form_type' => 'select', 'name' => 'userlang', 'description' => 'Language for settlement run', 'value' => $languages],
            ['form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')],
            ['form_type' => 'text', 'name' => 'tax', 'description' => 'Tax in %'],
            ['form_type' => 'select', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date', 'value' => $days],
            ['form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference', 'help' => trans('helper.BillingBase_MandateRef').$cols, 'options' => ['placeholder' => \App\Http\Controllers\BaseViewController::translate_label('e.g.: String - {number}')]],
            ['form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types', 'help' => trans('helper.BillingBase_SplitSEPA'), 'space' => 1],

            ['form_type' => 'text', 'name' => 'cdr_offset', 'description' => trans('messages.cdr_offset'), 'help' => trans('helper.BillingBase_cdr_offset')],
            ['form_type' => 'text', 'name' => 'cdr_retention_period', 'description' => 'CDR retention period', 'help' => trans('helper.BillingBase_cdr_retention')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_default', 'description' => trans('messages.voip_extracharge_default'), 'help' => trans('helper.BillingBase_extra_charge')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_mobile_national', 'description' => trans('messages.voip_extracharge_mobile_national'), 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'fluid_valid_dates', 'description' => 'Uncertain start/end dates for tariffs', 'help' => trans('helper.BillingBase_fluid_dates')],
            ['form_type' => 'checkbox', 'name' => 'termination_fix', 'description' => 'Item Termination only end of month', 'help' => trans('helper.BillingBase_ItemTermination')],
            ['form_type' => 'checkbox', 'name' => 'show_ags', 'description' => trans('messages.show_ags'), 'help' => trans('helper.BillingBase_showAGs')],
        ];
    }
}
