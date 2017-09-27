<?php namespace Modules\Billingbase\Entities;
   
use Modules\BillingBase\Entities\CostCenter;

class NumberRange extends \BaseModel {

    public $table = 'numberrange';

    protected $fillable = [];
    
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
				CostCenter::find($this->costcenter_id)->name
			],
			'index_header' => ['Id', 'Name', 'Prefix', 'Suffix', 'Start', 'End', 'Type', 'CostCenter'],
			'header' => $this->id . ' - ' . $this->name
		];
    }
    
    public static function get_new_number($type, $costcenter_id)
	{
		$new_number = null;
		$tables = array(
			'contract' => 'contract',
			'invoice' => 'invoice'
		);

		// get numberrange
		$numberrange = \DB::table('numberrange')
		                  ->where('type', $type)
		                  ->where('id', $costcenter_id)
		                  ->first();

		// find all entries for given numberrange and type
		$elements = \DB::table($tables[$type])
		               ->whereBetween('number', [$numberrange->start, $numberrange->end])
		               ->orderBy('number', 'asc')
		               ->get();

		if (count($elements) == 0) {
			$new_number = $numberrange->start;
		} else {
			// get last number
			$last_element = end($elements);
			$new_number = $last_element->number + 1;
		}

		if ($new_number > $numberrange->end) {
			// Todo: Throw Exception
		}

		// suffix
		if (trim($numberrange->suffix) != '') {
			$new_number = $new_number . $numberrange->suffix;
		}

		// prefix
		if (trim($numberrange->prefix) != '') {
			$new_number = $numberrange->prefix . $new_number;
		}

		return $new_number;
	}
}
