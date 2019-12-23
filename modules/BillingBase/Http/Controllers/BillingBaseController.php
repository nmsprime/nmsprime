<?php

namespace Modules\BillingBase\Http\Controllers;

use View;
use Schema;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\Dashboard\Entities\BillingAnalysis;

class BillingBaseController extends \BaseController
{
    public $name = 'BillingBase';

    public function index()
    {
        $title = 'Billing Dashboard';
        $income_data = BillingAnalysis::getIncomeData();
        $contracts_data = BillingAnalysis::getContractData();

        return View::make('billingbase::index', $this->compact_prep_view(compact('title', 'income_data', 'contracts_data')));
    }

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
            ['form_type' => 'select', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date', 'value' => $days, 'help' => trans('billingbase::help.BillingBase.rcd')],
            ['form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference', 'help' => trans('billingbase::help.BillingBase.MandateRef').$cols, 'options' => ['placeholder' => \App\Http\Controllers\BaseViewController::translate_label('e.g.: String - {number}')]],
            ['form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types', 'help' => trans('billingbase::help.BillingBase.SplitSEPA'), 'space' => 1],

            ['form_type' => 'text', 'name' => 'cdr_offset', 'description' => trans('messages.cdr_offset'), 'help' => trans('billingbase::help.BillingBase.cdr_offset')],
            ['form_type' => 'text', 'name' => 'cdr_retention_period', 'description' => 'CDR retention period', 'help' => trans('billingbase::help.BillingBase.cdr_retention')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_default', 'description' => trans('messages.voip_extracharge_default'), 'help' => trans('billingbase::help.BillingBase.extra_charge')],
            ['form_type' => 'text', 'name' => 'voip_extracharge_mobile_national', 'description' => trans('messages.voip_extracharge_mobile_national'), 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'fluid_valid_dates', 'description' => 'Uncertain start/end dates for tariffs', 'help' => trans('billingbase::help.BillingBase.fluid_dates')],
            ['form_type' => 'checkbox', 'name' => 'termination_fix', 'description' => 'Item Termination only end of month', 'help' => trans('billingbase::help.BillingBase.ItemTermination')],
            ['form_type' => 'checkbox', 'name' => 'show_ags', 'description' => trans('messages.show_ags'), 'help' => trans('billingbase::help.BillingBase.showAGs')],
            ['form_type' => 'checkbox', 'name' => 'adapt_item_start', 'description' => trans('billingbase::view.adaptItemStart'), 'help' => trans('billingbase::help.BillingBase.adaptItemStart')],
        ];
    }
}
