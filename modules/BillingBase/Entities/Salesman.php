<?php

namespace Modules\BillingBase\Entities;

use Storage;
use Modules\ProvBase\Entities\Contract;
use \App\Http\Controllers\BaseViewController;

class Salesman extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'salesman';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'firstname' 	=> 'required',
			'lastname' 		=> 'required',
			'commission'	=> 'required|numeric|between:0,100',
			'products' 		=> 'product',
		);
	}

	/*
	 * Init Observers
	 */
	public static function boot()
	{
		Salesman::observe(new SalesmanObserver);
		parent::boot();
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Salesman';
	}

	// link title in index view
	public function view_index_label()
	{
		return ['index' => [$this->id, $this->lastname, $this->firstname],
		        'index_header' => ['ID', 'Lastname', 'Firstname'],
				'header' => $this->lastname." ".$this->firstname];
	}


	// View Relation.
	public function view_has_many()
	{
		return array(
			'Contract' => $this->contracts,
			);
	}


	/**
	 * Relationships:
	 */
	public function contracts ()
	{
		return $this->hasMany('Modules\ProvBase\Entities\Contract');
	}



	/**
	 * BILLING STUFF
	 */
	public $all_prod_types = [];				// array (list) of all possible types of products -
	protected $total_commission = 0;			// total commission amount during actual billing cycle
	protected $item_names = [];					// all names of items he gets commission for (in actual billing cycle)
	public $filename = 'salesmen_commission';
	public $dir;								// set during init of accounting command - relativ to storage/app/


	// example - $item->product->name == 'Credit Device'
	public function add_item($item)
	{
		$types = explode(',', $this->products);
		foreach ($types as $key => $value)
			$types[$key] = trim($value);

		if ($item->product->type == 'Credit' && in_array('Credit', $types))
		{
			// get credit type from product name
			$credit_type = '';
			foreach ($this->all_prod_types as $type)
			{
				if (strpos($item->product->name, $type) !== false)
					$credit_type = $type;
			}

			// if type is assigned - only add amount if type is in salesmans product list
			if ($credit_type)
			{
				if (!in_array($credit_type, $types))
					return;
			}

			// add all other credits - default
			$this->total_commission += $item->charge;
			isset($this->item_names[$item->product->name]) ? $this->item_names[$item->product->name] += 1 : $this->item_names[$item->product->name] = 1;
			return;
		}

		// all other types that the salesman gets commission for
		if (in_array($item->product->type, $types))
		{
			// $count = $item->count ? $item->count : 1; 		// this is already done in item model
			$this->total_commission += $item->charge;
			isset($this->item_names[$item->product->name]) ? $this->item_names[$item->product->name] += $item->count : $this->item_names[$item->product->name] = $item->count;
		}

		return;
	}

	/**
	 * Return filename of Salesman Commissions with path relativ to storage/app/
	 */
	public function get_storage_rel_filename()
	{
		return $this->dir.BaseViewController::translate_label($this->filename).'.txt';
	}


	public function prepare_output_file()
	{
		Storage::put($this->get_storage_rel_filename(), "ID\t".BaseViewController::translate_label('Name')."\t".BaseViewController::translate_label('Commission in %')."\t".BaseViewController::translate_label('Total Fee')."\t".BaseViewController::translate_label('Commission Amount')."\t".BaseViewController::translate_label('Items')."\n");
	}


	// id, name, commission %, commission amount, all added items as string
	public function print_commission()
	{
		if ($this->total_commission == 0)
			return;

		foreach ($this->item_names as $key => $value)
			$items[] = $value.'x '.$key;

		Storage::append($this->get_storage_rel_filename(), $this->id."\t".$this->firstname.' '.$this->lastname."\t".$this->commission."\t".$this->total_commission."\t".round($this->total_commission * $this->commission / 100, 2)."\t".implode(', ', $items));
		// echo "stored salesmen commissions in $file\n";
	}



}


/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class SalesmanObserver
{
	public function creating($salesman)
	{
		$salesman->products = str_replace(['/', '|', ';'], ',', $salesman->products);
	}

	public function updating($salesman)
	{
		$salesman->products = str_replace(['/', '|', ';'], ',', $salesman->products);
	}

}