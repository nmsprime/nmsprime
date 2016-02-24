<?php namespace Modules\Hfccustomer\Entities;
   
use Illuminate\Database\Eloquent\Model;


/*
 * Modem Positioning Rule Model
 *
 * This Model will hold all rules for Entity Relation and 
 * Topograhpy Card Bubbles. See MprGeopos for more brief view.
 *
 * Relations: Tree <- Mpr <- MprGeopos
 * Relations: Modem <- Tree
 */
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


	/*
	 * MPR: refresh all bubbles on Entity Relation Diagram and Topography Card
	 * This will perform an updated on all matched Modems tree_id value, based
	 * on the added rules in Modem Positioning System: Mpr, MprGeopos. This function
	 * will be used by artisan command nms:mps
	 *
	 * NOTE: for priotity we will simply use mpr->prio field. So lower values in
	 *       prio will run first

	 * TODO: use a better (more complex) priority algorithm
	 *
	 * @author: Torsten Schmidt
	 */
	public static function refresh ()
	{
		// Foreach MPR
		// lower priority integers first
		foreach (Mpr::where('id', '>', '0')->orderBy('prio')->get() as $mpr)
		{
			// parse rectangles for MPR 
			if (count($mpr->mprgeopos) == 2)
			{
				// get ordered MPR Positions
				// Note: that MprGeopos is not ordered
				if ($mpr->mprgeopos[0]->x < $mpr->mprgeopos[1]->x)
				{
					$x1 = $mpr->mprgeopos[0]->x;
					$x2 = $mpr->mprgeopos[1]->x;
				}
				else
				{
					$x1 = $mpr->mprgeopos[1]->x;
					$x2 = $mpr->mprgeopos[0]->x;
				}

				if ($mpr->mprgeopos[0]->y < $mpr->mprgeopos[1]->y)
				{
					$y1 = $mpr->mprgeopos[0]->y;
					$y2 = $mpr->mprgeopos[1]->y;
				}
				else
				{
					$y1 = $mpr->mprgeopos[1]->y;
					$y2 = $mpr->mprgeopos[0]->y;
				}

				$id = $mpr->tree_id;

				$r = \Modules\ProvBase\Entities\Modem::whereRaw("(x > $x1) AND (x < $x2) AND (y > $y1) AND (y < $y2)")->update(['tree_id' => $id]);

				echo 'UPDATE: '.$id.', '.$mpr->name.' -> num updated :'.$r."\n";
			}
		}
	}
}