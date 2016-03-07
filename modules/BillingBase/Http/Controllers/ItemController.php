<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\Price;
use Config;

class ItemController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Item;

		$items = Price::where('type', '=', 'device')->orWhere('type', '=', 'other')->get();
		$b[0] = date('Y-m-01', time());
		for ($i=0; $i < 24; $i++)
		{ 
			$b[date('Y-m-01', strtotime('+1 months', strtotime(end($b))))] = date('Y-m-01', strtotime('+1 months', strtotime(end($b))));
		}
		$b[0] = null;

		// dd($b, Config::get('app.timezone'));

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->contract(), 'hidden' => '1'),
			array('form_type' => 'select', 'name' => 'price_id', 'description' => 'Item', 'value' => $model->html_list($items, 'name'), 'hidden' => '0'), 
			array('form_type' => 'select', 'name' => 'payment_from', 'description' => 'Payment from', 'value' => $b),
			array('form_type' => 'select', 'name' => 'payment_to', 'description' => 'Payment to', 'value' => $b)
		);
	}	
}