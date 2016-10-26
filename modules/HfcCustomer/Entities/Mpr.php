<?php namespace Modules\HfcCustomer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\ProvBase\Entities\Modem;


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
	public static function view_headline()
	{
		return 'Modem Positioning Rule';
	}

	// link title in index view
	public function view_index_label()
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
		return $this->hasMany('Modules\HfcCustomer\Entities\MprGeopos');
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
	 * @param modem: could be a modem->id or a set of pre-selected modem models filtered with Modem::where() or false for all modems
	 * @return: if param modem is a id the function returns the id of the matched mpr tree_id, in all other cases 0
	 * @author: Torsten Schmidt
	 */
	public static function refresh ($modem = null)
	{
		// prep vars
		$single_modem = false;
		$return = $r = 0;

		// if param modem is integer select modem with this integer value (modem->id)
		if (is_int($modem))
		{
			$single_modem = true;
			\Log::info('mps: perform mps rule matching for a single modem');
		} else {
			\Log::info('mps: perform mps rule matching');
			// reset all tree_ids if all modems are being matched,
			// because we don't know if old matches are still valid
			Modem::where('id', '>', '0')->update(['tree_id' => 0]);
		}

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

				// the tree_id for the actual rule
				$id = $mpr->tree_id;

				// the selected modems to use for update
				if ($single_modem)
					$tmp = Modem::where('id', '=', $modem);
				else
					// if no modem is set in parameters -> means: select all modems
					$tmp = Modem::where('id', '>', '0');

				$select = $tmp->where('x', '>', $x1)->where('x', '<', $x2)->where('y', '>', $y1)->where('y', '<', $y2);

				// for a single modem do not perform a update() either return the tree_id
				// Note: This is required because we can not call save() from observer context.
				//       this will re-call all oberservs and could lead to a potential hazard
				if ($single_modem)
				{
					$r = $select->count();
					// single_modem is within the current mpr area
					if($r)
						$return = $id;
				}
				else
					$r = $select->update(['tree_id' => $id]);

				// Log
				$log = 'mps: UPDATE: '.$id.', '.$mpr->name.' - updated modems: '.$r;
				\Log::info ($log);
				echo $log."\n";
			}
		}

		return $return;
	}
}
