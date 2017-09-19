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
    
    public static function get_new_number($type)
    {
        $numberrange = \DB::table('numberrange')->where('type', $type)->first();
        if (is_null($numberrange->last_generated_number)) {
            $new_number = $numberrange->start;
        } else {
            $new_number = $numberrange->last_generated_number + 1;
        }

        if ($new_number > $numberrange->end) {
            // Todo: Throw Exception
        }

        // save last genrated number
        \DB::table('numberrange')
            ->where('id', $numberrange->id)
            ->update(['last_generated_number' => $new_number]);

        return $new_number;
    }
}
