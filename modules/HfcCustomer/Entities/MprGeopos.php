<?php namespace Modules\Hfccustomer\Entities;

use Illuminate\Database\Eloquent\Model;


/*
 * Modem Positioning Rule Geo Position Model
 *
 * This Model will hold all geopos for Entity Relation and
 * Topograhpy Card Bubbles. One Mpr (Modem Pos Rule) can hold
 * multiple MprGeopos, which means one Rule can have Multiple
 * Geopos. two Positions per Mpr rule means rectangle. More than
 * two Pos is a polygon. This is not implemented yet and requires
 * update to OpenLayers 3 first.
 *
 * Relations: Tree <- Mpr <- MprGeopos
 */
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
	public static function view_headline()
	{
		return 'Modem Positioning Rule Geoposition';
	}

	// link title in index view
	public function view_index_label()
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