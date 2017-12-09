<?php
namespace Modules\HfcCustomer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\ProvBase\Entities\Modem;


/*
 * Modem Positioning Rule Model
 *
 * This Model will hold all rules for Entity Relation and
 * Topograhpy Card Bubbles. See MprGeopos for more brief view.
 *
 * Relations: NetElement <- Mpr <- MprGeopos
 * Relations: Modem <- Device
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

	public static function view_icon()
	{
		return '<i class="fa fa-compass"></i>';
	}

	// link title in index view
	public function view_index_label()
	{
		return ['index' => [$this->name, $this->netelement ? $this->netelement->name : 'unknown'],
				'index_header' => ['Name', 'Belongs To'],
				'header' => $this->name];

	}

	// AJAX Index list function
	// generates datatable content and classes for model
	public function view_index_label_ajax()
	{
		return ['table' => $this->table,
				'index_header' => [$this->table.'.name', 'netelement.name'],
				'header' =>  $this->name,
				'order_by' => ['0' => 'asc'], // columnindex => direction
				'eager_loading' => ['netelement']];
	}

	// Relation to NetElement
	// NOTE: HfcReq Module is required !
	public function netelement()
	{
		return $this->belongsTo('Modules\HfcReq\Entities\NetElement');
	}

	// NOTE: HfcReq Module is required !
	public function trees()
	{
		return \Modules\HfcReq\Entities\NetElement::all();
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
		return $this->netelement;
	}


	/*
	 * Relation Views
	 */
	public function view_has_many()
	{
		$ret['Edit']['MprGeopos']['class'] = 'MprGeopos';
		$ret['Edit']['MprGeopos']['relation'] = $this->mprgeopos;
		$ret['Edit']['MprGeopos']['options']['hide_create_button'] = 1;

		return $ret;
	}


	/*
	 * MPR: refresh all bubbles on Entity Relation Diagram and Topography Card
	 * This will perform an updated on all matched Modems netelement_id value, based
	 * on the added rules in Modem Positioning System: Mpr, MprGeopos. This function
	 * will be used by artisan command nms:mps
	 *
	 * NOTE: for priotity we will simply use mpr->prio field. So lower values in
	 *       prio will run first

	 * TODO: use a better (more complex) priority algorithm
	 *
	 * @param modem: could be a modem->id or a set of pre-selected modem models filtered with Modem::where() or false for all modems
	 * @return: if param modem is a id the function returns the id of the matched mpr netelement_id, in all other cases 0
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
			\Log::info('MPS: perform mps rule matching for a single modem');
		} else {
			\Log::info('MPS: perform mps rule matching');
			// reset all tree_ids if all modems are being matched,
			// because we don't know if old matches are still valid
			Modem::where('id', '>', '0')->update(['netelement_id' => 0]);
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

				// the netelement_id for the actual rule
				$id = $mpr->netelement_id;

				// the selected modems to use for update
				if ($single_modem)
					$tmp = Modem::where('id', '=', $modem);
				else
					// if no modem is set in parameters -> means: select all modems
					$tmp = Modem::where('id', '>', '0');

				$select = $tmp->where('x', '>', $x1)->where('x', '<', $x2)->where('y', '>', $y1)->where('y', '<', $y2);

				// for a single modem do not perform a update() either return the netelement_id
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
					$r = $select->update(['netelement_id' => $id]);

				// Log
				$log = 'MPS: UPDATE: '.$id.', '.$mpr->name.' - updated modems: '.$r;
				\Log::debug ($log);
				echo $log."\n";
			} elseif (count($mpr->mprgeopos) > 2) {

				// populate polygon array according to mprgeopostions, this will be used by point_in_polygon()
				$polygon = [];
				foreach($mpr->mprgeopos as $geopos)
					$polygon[] = [$geopos->x, $geopos->y];

				foreach ($single_modem ? Modem::where('id', '=', $modem) : Modem::all() as $tmp) {
					if(self::point_in_polygon([$tmp->x,$tmp->y], $polygon)) {
						$tmp->netelement_id = $mpr->netelement_id;
						$tmp->observer_enabled = false;
						$tmp->save();
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Check if point is within the boundaries of the given polygon.
	 * Based on: http://stackoverflow.com/questions/14818567/point-in-polygon-algorithm-giving-wrong-results-sometimes/18190354#18190354
	 *
	 * @param p: point to check (array)
	 * @param polygon: vertices of polygon outline (array of points (array))
	 * @return: true if point in polygon, otherwise false
	 * @author: Ole Ernst
	 */
	public static function point_in_polygon($p, $polygon) {
		$c = 0;
		$p1 = $polygon[0];
		$n = count($polygon);

		for ($i=1; $i<=$n; $i++) {
			$p2 = $polygon[$i % $n];
			if ($p[1] > min($p1[1], $p2[1])
				&& $p[1] <= max($p1[1], $p2[1])
				&& $p[0] <= max($p1[0], $p2[0])
				&& $p1[1] != $p2[1]) {
					$xinters = ($p[1] - $p1[1]) * ($p2[0] - $p1[0]) / ($p2[1] - $p1[1]) + $p1[0];
					if ($p1[0] == $p2[0] || $p[0] <= $xinters)
						$c++;
			}
			$p1 = $p2;
		}
		// even number of edges passed -> point not in the polygon
		return $c%2 != 0;
	}

	/**
	 * BOOT:
	 * - init Mpr Observer
	 */
	public static function boot()
	{
		parent::boot();

		Mpr::observe(new MprObserver);
	}

}


/**
 * Mpr Observer Class
 * Handles changes on MprGeopos, can handle:
 *
 * 'creating', 'created', 'updating', 'updated',
 * 'deleting', 'deleted', 'saving', 'saved',
 * 'restoring', 'restored',
 */
class MprObserver
{
	// unlike MprGeoposObserver we only hook into 'updated' here, as MpsCommand will already
	// be called in MprGeoposObserver if MPRs (including their geopos) are created or deleted
	public function updated($modem)
	{
		\Queue::push(new \Modules\HfcCustomer\Console\MpsCommand);
	}
}
