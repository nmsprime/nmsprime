<?php

namespace Modules\HfcCustomer\Entities;

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
class Mpr extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'mpr';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name' => 'required|string',
        ];
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

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return ['table' => $this->table,
                'index_header' => ['id', $this->table.'.name', 'prio', 'netelement.name'],
                'header' =>  $this->name,
                'order_by' => ['0' => 'asc'], // columnindex => direction
                'eager_loading' => ['netelement'], ];
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
    public function view_belongs_to()
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

    /**
     * MPR: refresh all bubbles on Entity Relation Diagram and Topography Card
     * This will perform an updated on all matched Modems netelement_id value, based
     * on the added rules in Modem Positioning System: Mpr, MprGeopos. This function
     * will be used by artisan command nms:mps
     *
     * NOTE: For priority we will simply use mpr->prio field. So lower values in prio will run first
     * 		 Multiple MPRs with different prio's are superseded by polygons!
     *
     * @param 	object 	single Modem or null for all modems
     * @author: Torsten Schmidt, Nino Ryschawy
     */
    public static function ruleMatching($modem = null)
    {
        $r = 0;

        // if param modem is integer select modem with this integer value (modem->id)
        if ($modem) {
            \Log::info('MPS: perform mps rule matching for a single modem');
        } else {
            \Log::info('MPS: perform mps rule matching');
            // reset all netelement_ids if all modems are being matched,
            // because we don't know if old matches are still valid
            Modem::where('id', '>', '0')->update(['netelement_id' => 0]);

            echo "Get all modems from DB...\n";
            $modems = Modem::all();
        }

        // Foreach MPR
        // lower priority integers first
        foreach (self::orderBy('prio')->get() as $mpr) {
            // parse rectangles for MPR
            if (count($mpr->mprgeopos) == 2) {
                // get ordered MPR Positions
                // Note: that MprGeopos is not ordered
                if ($mpr->mprgeopos[0]->x < $mpr->mprgeopos[1]->x) {
                    $x1 = $mpr->mprgeopos[0]->x;
                    $x2 = $mpr->mprgeopos[1]->x;
                } else {
                    $x1 = $mpr->mprgeopos[1]->x;
                    $x2 = $mpr->mprgeopos[0]->x;
                }

                if ($mpr->mprgeopos[0]->y < $mpr->mprgeopos[1]->y) {
                    $y1 = $mpr->mprgeopos[0]->y;
                    $y2 = $mpr->mprgeopos[1]->y;
                } else {
                    $y1 = $mpr->mprgeopos[1]->y;
                    $y2 = $mpr->mprgeopos[0]->y;
                }

                // the netelement_id for the actual rule
                $id = $mpr->netelement_id;

                // the selected modems to use for update
                if ($modem) {
                    $query = Modem::where('id', '=', $modem->id);
                } else {
                    // if no modem is set in parameters -> means: select all modems
                    $query = Modem::where('id', '>', '0');
                }

                $query = $query->where('x', '>', $x1)->where('x', '<', $x2)->where('y', '>', $y1)->where('y', '<', $y2);

                // Do not call save() on modem as this would call the observers again and this function is
                // triggered from observer -> result would be an endless loop
                $r = $query->update(['netelement_id' => $id]);

                // Log
                $log = 'MPS: UPDATE: '.$id.', '.$mpr->name.' - updated modems: '.$r;
                \Log::debug($log);
                if (env('APP_ENV') != 'testing') {
                    echo $log."\n";
                }
            } elseif (count($mpr->mprgeopos) > 2) {
                // populate polygon array according to mprgeopostions, this will be used by point_in_polygon()
                $polygon = [];
                foreach ($mpr->mprgeopos as $geopos) {
                    $polygon[] = [$geopos->x, $geopos->y];
                }

                $cnt = 0;
                foreach ($modem ? [$modem] : $modems as $m) {
                    if (self::point_in_polygon([$m->x, $m->y], $polygon)) {
                        Modem::where('id', '=', $m->id)->update(['netelement_id' => $mpr->netelement_id]);
                        $cnt++;
                    }
                }

                $log = "MPS: UPDATE: $mpr->netelement_id, $mpr->name - updated modems: $cnt";
                \Log::debug($log);
                echo $log."\n";
            }
        }
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
    public static function point_in_polygon($p, $polygon)
    {
        $c = 0;
        $p1 = $polygon[0];
        $n = count($polygon);

        for ($i = 1; $i <= $n; $i++) {
            $p2 = $polygon[$i % $n];
            if ($p[1] > min($p1[1], $p2[1])
                && $p[1] <= max($p1[1], $p2[1])
                && $p[0] <= max($p1[0], $p2[0])
                && $p1[1] != $p2[1]) {
                $xinters = ($p[1] - $p1[1]) * ($p2[0] - $p1[0]) / ($p2[1] - $p1[1]) + $p1[0];
                if ($p1[0] == $p2[0] || $p[0] <= $xinters) {
                    $c++;
                }
            }
            $p1 = $p2;
        }
        // even number of edges passed -> point not in the polygon
        return $c % 2 != 0;
    }

    /**
     * BOOT:
     * - init Mpr Observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new MprObserver);
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
