<?php

namespace Modules\BillingBase\Entities;

use DB;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\Product;

class Product extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'product';

	// Add your validation rules here
	public static function rules($id = null)
	{
		// Pay attention to the prepare_rules()-function in Controller
		return array(
			'name' 	=> 'required|unique:product,name,'.$id.',id,deleted_at,NULL',
			'type' 	=> "required|not_null",
			// 'type' => "required|not_null|unique:product,type,$id,id,type,Credit,deleted_at,NULL",	// if credit shall exist only once
			'voip_sales_tariff_id' => 'required_if:type,Voip',
			'voip_purchase_tariff_id' => 'required_if:type,Voip',
			'qos_id' => 'required_if:type,Internet',
			'price'  => 'required_if:type,Internet,Voip,TV,Other,Device,Mixed',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Product Entry';
	}

	// link title in index view
	public function view_index_label()
	{
		// return $this->type.' - '.$this->name.' | '.$this->price.' €';

		switch ($this->type)
		{
			case 'Internet':	$bsclass = 'info'; break; // online
			case 'TV': $bsclass = 'warning'; break; // warning
			case 'Voip': $bsclass = 'success'; break; // critical
			case 'Device': $bsclass = 'warning'; $status = 'offline'; break; // offline
			case 'Credit': $bsclass = 'danger'; $status = 'offline'; break; // offline
			case 'Other': $bsclass = 'info'; $status = 'offline'; break; // offline

			default: $bsclass = 'danger'; break;
		}

		return ['index' => [$this->type, $this->name, $this->price],
		        'index_header' => ['Type', 'Name', 'Price'],
		        'bsclass' => $bsclass,
		        'header' => $this->type.' - '.$this->name.' | '.$this->price.' €'];
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('type')->get();
	}


	/**
	 * Relationships:
	 */
	public function quality ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Qos', 'qos_id');
	}

	public function item ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\Item');
	}

	public function costcenter ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\CostCenter', 'costcenter_id');
	}


	/*
	 * Other Functions
	 */

	/**
	 * Returns an array with all ids of a specific product type
	 * Note: until now only Internet & Voip is needed
	 * @param String/Enum 	product type
	 * @return Array 		of id's
	 *
	 * @author Nino Ryschawy
	 */
	public static function get_product_ids($type)
	{
		switch ($type)
		{
			case 'Internet':
				$prod_ids = DB::table('product')->where('type', '=', $type)->where('qos_id', '!=', '0')->select('id')->get();
				break;
			case 'Voip':
				$prod_ids = DB::table('product')->where('type', '=', $type)->where('voip_sales_tariff_id', '!=', '0')->orWhere('voip_purchase_tariff_id', '!=', '0')->select('id')->get();
				break;
			case 'TV':
				$prod_ids = DB::table('product')->where('type', '=', 'TV')->select('id')->get();
				goto make_list;
			default:
				return null;
		}


make_list:

		$ids = array();

		foreach ($prod_ids as $prod)
			array_push($ids, $prod->id);

		return $ids;
	}


	/*
	 * Init Observers
	 */
	public static function boot()
	{
		Product::observe(new ProductObserver);
		parent::boot();
	}

}



/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class ProductObserver
{

	/**
	 * Add textual informäation about bundling with voip to internet products
	 * First remove the existing suffix, then add a suffix depending on the bundling state.
	 *
	 * This behaviour can be discussed – we also could add this information in all views…
	 *
	 * @author Patrick Reichel
	 */
	protected function _set_bundled_with_voip_textual_information($name, $type, $bundled) {

		// change only for internet products, leave the rest untouched
		if ($type != 'Internet') {
			return $name;
		}

		// remove old markers
		$name = str_replace(' (bundled with VoIP)', '', $name);
		$name = str_replace(' (standalone)', '', $name);

		// add information for bundling on internet names
		if (boolval($bundled)) {
			$name .= ' (bundled with VoIP)';
		}
		else {
			$name .= ' (standalone)';
		}

		return $name;
	}

	/**
	 * @author Patrick Reichel
	 */
	public function creating($product) {

		$product->name = $this->_set_bundled_with_voip_textual_information($product->name, $product->type, $product->bundled_with_voip);
	}


	/**
	 * @author Patrick Reichel
	 */
	public function updating($product) {

		$product->name = $this->_set_bundled_with_voip_textual_information($product->name, $product->type, $product->bundled_with_voip);

	}

}
