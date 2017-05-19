<?php namespace Modules\Billingbase\Http\Controllers;

use Schema;
use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\ProvBase\Entities\Contract;

class BillingBaseController extends \BaseController {

	public $name = 'BillingBase';


	public function view_form_fields($model = null)
	{
		$days[0] = null;
		for ($i=1; $i < 29; $i++)
			$days[$i] = $i;

		// build data for mandate reference help string
		$contract = new Contract;
		$mandate  = new SepaMandate;
		$cols1 = Schema::getColumnListing($contract->getTable());
		$cols2 = Schema::getColumnListing($mandate->getTable());
		$cols = array_merge($cols1, $cols2);

		foreach($cols as $key => $col)
		{
			if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']))
				unset ($cols[$key]);
		}

		$cols = implode (', ', $cols);

		return [
			array('form_type' => 'select', 'name' => 'userlang', 'description' => 'Language for Accounting command', 'value' => BillingBase::getPossibleEnumValues('userlang')),
			array('form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')),
			array('form_type' => 'text', 'name' => 'tax', 'description' => 'Tax in %'),
			array('form_type' => 'select', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date', 'value' => $days),
			array('form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference', 'help' => trans('helper.BillingBase_MandateRef').$cols , 'options' => ['placeholder' => \App\Http\Controllers\BaseViewController::translate_label('e.g.: String - {number}')]), 
			array('form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types', 'help' => trans('helper.BillingBase_SplitSEPA'), 'space' => 1),

			array('form_type' => 'text', 'name' => 'cdr_offset', 'description' => trans('messages.cdr_offset'), 'help' => trans('helper.BillingBase_cdr_offset')),
			array('form_type' => 'text', 'name' => 'voip_extracharge_default', 'description' => trans('messages.voip_extracharge_default'), 'help' => trans('helper.BillingBase_extra_charge')),
			array('form_type' => 'text', 'name' => 'voip_extracharge_mobile_national', 'description' => trans('messages.voip_extracharge_mobile_national'), 'space' => 1),

			array('form_type' => 'checkbox', 'name' => 'termination_fix', 'description' => 'Item Termination only end of month', 'help' => trans('helper.BillingBase_ItemTermination')),
		];
	}

}