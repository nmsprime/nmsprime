<?php

namespace Modules\BillingBase\Entities;

use DB;
use Modules\BillingBase\Entities\SepaAccount;
//use Modules\BillingBase\Entities\Product;

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

	// View Icon
	public static function view_icon()
	{
		return '<i class="fa fa-th-list"></i>';
	}

	// link title in index view
	public function view_index_label()
	{
		$bsclass = $this->get_bsclass();

		return ['index' => [$this->type, $this->name, $this->price],
		        'index_header' => ['Type', 'Name', 'Price'],
		        'bsclass' => $bsclass,
		        'header' => $this->type.' - '.$this->name.' | '.$this->price.' €'];
	}

	// AJAX Index list function
	// generates datatable content and classes for model
	public function view_index_label_ajax()
	{
		$bsclass = $this->get_bsclass();

		return ['table' => $this->table,
				'index_header' => [$this->table.'.type', $this->table.'.name',  $this->table.'.price'],
				'header' =>  $this->type.' - '.$this->name.' | '.$this->price.' €',
				'bsclass' => $bsclass,
				'orderBy' => ['0' => 'asc']];  // columnindex => direction
	}

	public function get_bsclass(){
		
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

		return $bsclass;
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


	/**
	 *	Recursive delete of all children objects
	 *
	 *	@author Torsten Schmidt
	 *
	 *	@return void
	 *
	 *  @todo return state on success, should also take care of deleted children
	 */
	public function delete()
	{
		foreach ($this->item as $child)
		{
			$child->accounting_text = $this->name;
			$child->save();
		}

		$this->_delete();
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

    /**
     * Returns an array of available product types
     *
     * @return array|null
     * @throws \Exception
     */
	public static function get_product_types()
    {
        $ret_val = null;

        try {
            $products = DB::table('product')->groupBy('type')->get();
            foreach ($products as $product) {
                $ret_val[] = $product->type;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $ret_val;
    }
}
