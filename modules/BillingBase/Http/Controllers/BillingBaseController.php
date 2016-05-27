<?php namespace Modules\Billingbase\Http\Controllers;

use Schema;
use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\ProvBase\Entities\Contract;

class BillingBaseController extends \BaseModuleController {

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
			// array('form_type' => 'text', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date'),
			array('form_type' => 'select', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date', 'value' => $days),
			array('form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')),
			array('form_type' => 'text', 'name' => 'tax', 'description' => 'Tax in %'),
			array('form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference', 'help' => "A Template can be built with sql columns of contract or mandate table - possible fields: \n".$cols , 'options' => ['placeholder' => 'e.g.: String - {number}']),
			array('form_type' => 'text', 'name' => 'invoice_nr_start', 'description' => 'Invoice Number Start', 'help' => 'Invoice Number Counter starts every new year with this number'),
			array('form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types', 'help' => 'Sepa Transfers are split to different XML-Files dependent of their transfer type'),
			array('form_type' => 'checkbox', 'name' => 'termination_fix', 'description' => 'Item Termination only end of month', 'help' => 'Allow Customers only to terminate booked products on last day of month'),
		];
	}

}