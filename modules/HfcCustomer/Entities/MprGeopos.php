<?php namespace Modules\Hfccustomer\Entities;
   
use Illuminate\Database\Eloquent\Model;

class MprGeopos extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'mprgeopos';


	// Relation MPR
	public function mprgeopos()
	{
		return $this->belongsTo('Modules\Hfccustomer\Entities\Mpr');
	}

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' => 'required|string',
			'x' => 'required|numeric',
			'y' => 'required|numeric'
		);
	}

	// Name of View
	public static function get_view_header()
	{
		return 'Modem Positioning Rule Geoposition';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return 'GEOPOS'.$this->id.' : '.$this->x.', '.$this->y;
	}	

	// Relation to Tree
	// NOTE: HfcBase Module is required !
	public function mpr()
	{
		return $this->belongsTo('Modules\HfcCustomer\Entities\Mpr');
	}
	

	/*
	 * Relation Views
	 */
	public function view_belongs_to ()
	{
		return $this->mpr;
	}


}