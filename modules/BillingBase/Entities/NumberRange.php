<?php namespace Modules\Billingbase\Entities;
   
use Modules\BillingBase\Entities\CostCenter;
use Modules\ProvBase\Entities\Contract;

class NumberRange extends \BaseModel {

    public $table = 'numberrange';

    protected $fillable = [];

	/**
	 * BOOT:
	 * - init observer
	 */
	public static function boot()
	{
		parent::boot();
		NumberRange::observe(new NumberRangeObserver);
	}

    public static function view_headline()
    {
        return 'Numberranges';
    }
    
    public static function view_icon()
    {
        return '<i class="fa fa-globe"></i>';
    }

    public function index_list()
    {
        return $this->orderBy('id', 'asc')->get();
    }

    public function view_index_label()
	{
		return [
			'index' => [
				$this->id, 
				$this->name, 
				$this->prefix,
				$this->suffix,
				$this->start,
				$this->end,
				$this->type,
				CostCenter::withTrashed()->find($this->costcenter_id)->name
			],
			'index_header' => ['Id', 'Name', 'Prefix', 'Suffix', 'Start', 'End', 'Type', 'CostCenter'],
			'header' => $this->id . ' - ' . $this->name
		];
    }

	public function view_index_label_ajax()
	{
		return [
			'table' => $this->table,
			'index_header' => [
				$this->table . '.id',
				$this->table . '.name',
				$this->table . '.prefix',
				$this->table . '.suffix',
				$this->table . '.start',
				$this->table . '.end',
				$this->table . '.type',
				$this->table . '.costcenter.name'
			],
			'header' => $this->table . 'id' . ' - ' . $this->table . 'name',
			'order_by' => ['0' => 'asc'],
			'eager_loading' => ['costcenter']
		];
	}

	/**
	 * Relationships
	 */
	public function costcenter ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\CostCenter', 'costcenter_id');
	}

    public static function get_new_number($type, $costcenter_id)
	{
		$new_number = null;

		switch ($type) {
			case 'contract':
				$new_number = self::get_new_contract_number($costcenter_id);
				break;

			case 'invoice':
				$new_number = self::get_new_invoice_number($costcenter_id);
				break;

			default:
				$new_number = self::get_new_contract_number($costcenter_id);
		}

		return $new_number;
	}

	protected static function get_new_contract_number($costcenter_id)
	{
		$contract_number = null;
		$model = new Contract();
		$numberranges = self::get_numberranges_by_type_and_costcenter('contract', $costcenter_id);

		foreach ($numberranges as $key => $range) {

			$contract_number = self::generate_number($model, $range);

			if (!is_null($contract_number)) {
				break;
			}
		}

		return $contract_number;
	}

	public static function get_numberranges_by_type_and_costcenter($type, $costcenter_id)
	{
		return NumberRange::where('type', $type)
		                  ->where('costcenter_id', $costcenter_id)
		                  ->get();
	}

	protected static function get_new_invoice_number()
	{
		return null;
	}

	protected static function generate_number($model, $range)
	{
		$new_number = null;

		// get last given number
		$last_number = $range->last_generated_number;

		/*
		 * if last given number 0 then start with the first number of the range
		 * otherwise raise the number about 1
		 */
		if ( $last_number == 0 ) {
			$new_number = $range->start;
		} else {
			if ( $last_number < $range->end ) {
				$new_number = $last_number + 1;
			}
		}

		if (!is_null($new_number)) {
			/*
			 * save last number for range
			 */
			$range->last_generated_number = $new_number;
			$range->save();

			// add prefix and suffix
			$new_number = trim($range->prefix) . $new_number . trim($range->suffix);
		}

		return $new_number;
	}

	private static function check_number($model, $number)
	{
		return $model::withTrashed()
		      ->where('number', '=', $number)
		      ->get();
	}
}

/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class NumberRangeObserver
{
	public function created($numberrange)
	{
		return redirect()->route('CostCenter.edit', ['id' => $numberrange->costcenter_id]);
	}
}