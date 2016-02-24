<?php namespace Modules\Hfccustomer\Entities;
   
use Illuminate\Database\Eloquent\Model;

class Mpr extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'mpr';


	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' => 'required|string'
		);
	}

	// Name of View
	public static function get_view_header()
	{
		return 'Modem Positioning Rule';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->id.' : '.$this->name;
	}	

	// Relation to Tree
	// NOTE: HfcBase Module is required !
	public function tree()
	{
		return $this->belongsTo('Modules\HfcBase\Entities\Tree');
	}

	// Relation to Tree
	// NOTE: HfcBase Module is required !
	public function trees()
	{
		return \Modules\HfcBase\Entities\Tree::all();
	}

	// Relation to MPR Geopos
	public function mprgeopos()
	{
		return $this->hasMany('Modules\Hfccustomer\Entities\MprGeopos');
	}
	

	/*
	 * Relation Views
	 */
	public function view_belongs_to ()
	{
		return $this->tree;
	}


	/*
	 * Relation Views
	 */
	public function view_has_many()
	{
		return array(
			'MprGeopos' => $this->mprgeopos
		);

	}
}