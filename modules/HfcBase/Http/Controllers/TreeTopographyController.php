<?php

namespace Modules\HfcBase\Http\Controllers;

use Acme\php\ArrayHelper;
use Modules\HfcReq\Entities\NetElement;

/*
 * Tree Topography Controller
 *
 * One Object represents one Topography View KML File
 *
 * @author: Torsten Schmidt
 */
class TreeTopographyController extends HfcBaseController
{
    protected $edit_left_md_size = 12;

    /*
     * Local tmp folder required for generating the kml files
     * (relative to /storage/app)
     */
    public static $path_rel = 'data/hfcbase/kml/';

    /*
     * Public folder, where our assets are stored (*.png used by kml)
     * (relative to /public)
     */
    private $path_images = 'modules/hfcbase/kml/';

    /*
     * Constructor: Set local vars
     */
    public function __construct()
    {
        // the relative (to /storage/app) file path based on a random hash
        $this->file = self::$path_rel.sha1(uniqid(mt_rand(), true)).'.kml';

        return parent::__construct();
    }

    /**
     * Show Cluster or Network Entity Relation Diagram
     *
     * @param field: search field name in tree table
     * @param search: the search value to look in tree table $field
     * @return view with KML file
     *
     * @author: Torsten Schmidt
     */
    public function show($field, $search)
    {
        // prepare search
        $s = "$field='$search'";
        if ($field == 'all') {
            $s = 'id>2';
        }

        // Generate KML file
        $file = $this->kml_generate(NetElement::whereRaw($s)->whereNotNull('pos')->where('pos', '!=', ' '));
        if (! $file) {
            return \View::make('errors.generic')->with('message', 'No NetElements with Positions available!');
        }

        // Prepare and Topography Map
        $target = $this->html_target;

        $route_name = 'Tree';
        $view_header = 'Topography';
        $body_onload = 'init_for_map';

        $tabs = TreeErdController::getTabs($field, $search);

        // MPS: get all Modem Positioning Rules
        $mpr = $this->mpr(NetElement::whereRaw($s));

        // NetElements: generate kml_file upload array
        $kmls = $this->kml_file_array(NetElement::whereRaw($s)->whereNotNull('pos')->where('pos', '!=', ' ')->get());
        $file = route('HfcBase.get_file', ['type' => 'kml', 'filename' => basename($file)]);

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact('file', 'target', 'route_name', 'view_header', 'tabs', 'body_onload', 'field', 'search', 'mpr', 'kmls')));
    }

    /*
     * MPS: Modem Positioning Rules
     * return multi array with MPS rules and Geopositions, like
     *   [ [mpr.id] => [0 => [0=>x,1=>y], 1 => [0=>x,1=>y], ..], .. ]
     * enable and see dd() for a more detailed view
     *
     * @param trees: The Tree Objects to be displayed, without ->get() call
     * @return array of MPS rules and geopos for all $tree objects
     *
     * @author: Torsten Schmidt
     */
    public function mpr($trees)
    {
        $ret = [];
        if (! \Module::collections()->has('HfcCustomer')) {
            return $ret;
        }

        foreach ($trees->get() as $tree) {
            foreach ($tree->mprs as $mpr) {
                $rect = [];
                foreach ($mpr->mprgeopos as $pos) {
                    array_push($rect, [$pos->x, $pos->y]);
                }

                if (isset($rect[0])) {
                    $ret[$mpr->id] = $rect;
                }
            }
        }

        // dd($ret);
        return $ret;
    }

    /*
     * Generate the KML File
     *
     * @param _trees: The Tree Objects to be displayed, without ->get() call
     * @return the path of the generated *.kml file, could be included via asset ()
     *
     * @author: Torsten Schmidt
     */
    public function kml_generate($_trees)
    {
        $file = $this->file_pre(asset($this->path_images));
        //
        // Note: OpenLayer draws kml file in parse order,
        // this requires to build kml files in the following order:
        //  a) Lines
        //  b) Customer Lines
        //  c) Customer Bubbles
        //  d) Pos Elements (Amps, Nodes ..)

        //
        // Draw: Parent - Child - Relationship
        //
        $trees = $_trees->orderBy('pos')->get();

        if (! $trees->count()) {
            return;
        }

        foreach ($trees as $tree) {
            $parent = $tree->parent;
            $pos1 = $tree->pos;
            $pos2 = $parent ? $parent->pos : null;
            $name = $tree->id;
            $type = $tree->type;
            $tp = $tree->tp;

            // skip empty pos and lines to elements not in search string
            if ($pos2 == null ||
                $pos2 == '' ||
                $pos2 == '0,0' ||
                ! ArrayHelper::objArraySearch($trees, 'id', $tree->parent->id)) {
                continue;
            }

            // Line Color - Style
            $style = '#BLACKLINE';
            if ($type == 'AMP' || $tp == 'FOSTRA') {
                $style = '#REDLINE';
            }

            if ($type == 'NODE') {
                $style = '#BLUELINE';
            }

            // Draw Line
            $file .= "

			<Placemark>
				<name>$parent -> $name</name>
				<description><![CDATA[]]></description>
				<styleUrl>$style</styleUrl>
				<LineString>
					<tessellate>1</tessellate>
					<coordinates>
						$pos1,0.000000
						$pos2,0.000000
					</coordinates>
				</LineString>
			</Placemark>";
        }

        //
        // Customer
        //
        if (\Module::collections()->has('HfcCustomer')) {
            $modem_helper = 'Modules\HfcCustomer\Entities\ModemHelper';

            $n = 0;
            foreach ($trees as $tree) {
                $id = $tree->id;
                $name = $tree->name;
                $pos_tree = $tree->pos;

                $pos = $modem_helper::ms_avg_pos($tree->id);

                if ($pos['x']) {
                    $xavg = $pos['x'];
                    $yavg = $pos['y'];
                    $icon = $modem_helper::ms_state_to_color($modem_helper::ms_state($id));
                    $icon .= '-CUS';

                    // Draw Line - Customer - Amp
                    $file .= "

					<Placemark>
						<name></name>
						<description><![CDATA[]]></description>
						<styleUrl>#BLACKLINE2</styleUrl>
						<LineString>
							<tessellate>1</tessellate>
							<coordinates>
								$xavg,$yavg,0.000000
								$pos_tree,0.000000
							</coordinates>
						</LineString>
					</Placemark>";

                    // Draw Customer Marker
                    $file .=
                    '
					<Placemark>
						<name></name>
						<description><![CDATA[';

                    $num = $modem_helper::ms_num($id);
                    $numa = $modem_helper::ms_num_all($id);
                    $pro = $numa ? round(100 * $num / $numa, 0) : 0;
                    $cri = $modem_helper::ms_cri($id);
                    $avg = $modem_helper::ms_avg($id);
                    $url = \BaseRoute::get_base_url()."/Customer/netelement_id/$id";

                    $file .= "Amp/Node: $name<br><br>Number All CM: $numa<br>Number Online CM: $num ($pro %)<br>Number Critical CM: $cri<br>US Level Average: $avg<br><br><a href=\"$url\" target=\"".$this->html_target.'" alt="">Show all Customers</a>';

                    $file .= "]]></description>
							<styleUrl>#$icon</styleUrl>
							<Point>
								<coordinates>$xavg,$yavg,0.000000</coordinates>
							</Point>
						</Placemark>";
                }
            }
        }

        //
        // Fetch unique Geo Positions ..
        //
        $p1 = '';

        foreach ($trees as $tree) {
            $p2 = $tree->pos;

            if ($p1 != $p2) {
                $rstate = 0;
                $ystate = 0;
                $router = 0;
                $fiber = 0;

                $file .= '
					<Placemark>
					<name></name>
					<description><![CDATA[';
            }

            $type = $tree->type;
            $parent = $tree->parent ? $tree->parent->id : null;
            $state = $tree->get_bsclass();

            if ($tree->state == 'warning') {
                $ystate += 1;
            }

            if ($tree->state == 'danger') {
                $rstate += 1;
            }

            if (($type == 'CMTS') || ($type == 'CLUSTER') || ($type == 'DATA') || ($type == 'NET')) {
                $router += 1;
            }

            if ($type == 'NODE') {
                $fiber += 1;
            }

            if ($p1 != $p2) {
                $icon = 'OK';
                if ($ystate) {
                    $icon = 'YELLOW';
                }
                if ($rstate) {
                    $icon = 'RED';
                }

                if ($router) {
                    $icon .= '-ROUTER';
                } elseif ($fiber) {
                    $icon .= '-FIB';
                } elseif ($parent == 1) {
                    $icon = 'blue-CUS';
                }

                $file .= "$p2";
                $file .= "]]></description>
				<styleUrl>#$icon</styleUrl>
				<Point>
					<coordinates>$p2,0.000000</coordinates>
				</Point>
				</Placemark>";
            }

            $p1 = $p2;
        }

        //
        // Write KML File ..
        //
        $file .= $this->file_post;
        \Storage::put($this->file, $file);

        return str_replace(storage_path(), '', \Storage::getAdapter()->applyPathPrefix($this->file));
    }

    private $file_post = '

			</Document>
		</kml>';

    private function file_pre($p)
    {
        return "

		<kml xmlns=\"http://earth.google.com/kml/2.2\">
		<Document>
			<name>mbg - amplifier</name>

			<Style id=\"OK\">
				<IconStyle>
					<Icon>
						<href>$p/green-amp.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"YELLOW\">
				<IconStyle>
					<Icon>
						<href>$p/yellow-amp.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"RED\">
				<IconStyle>
					<Icon>
						<href>$p/red-amp.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"OK-FIB\">
				<IconStyle>
					<Icon>
						<href>$p/green-fib.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"YELLOW-FIB\">
				<IconStyle>
					<Icon>
						<href>$p/yellow-fib.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"RED-FIB\">
				<IconStyle>
					<Icon>
						<href>$p/red-fib.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"OK-ROUTER\">
				<IconStyle>
					<Icon>
						<href>$p/router.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"RED-ROUTER\">
				<IconStyle>
					<Icon>
						<href>$p/router-red.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"YELLOW-ROUTER\">
				<IconStyle>
					<Icon>
						<href>$p/router-yellow.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id='green-CUS'>
				<IconStyle>
					<Icon>
						<href>$p/green-dot.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id='yellow-CUS'>
				<IconStyle>
					<Icon>
						<href>$p/yellow-dot.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id='red-CUS'>
				<IconStyle>
					<Icon>
						<href>$p/red-dot.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id='blue-CUS'>
				<IconStyle>
					<Icon>
						<href>$p/blue-dot.png</href>
					</Icon>
				</IconStyle>
			</Style>

			<Style id=\"BLUELINE\">
				<LineStyle>
					<color>FFFF0000</color>
					<width>2</width>
				</LineStyle>
			</Style>

			<Style id=\"REDLINE\">
				<LineStyle>
					<color>FF0000FF</color>
					<width>2</width>
				</LineStyle>
			</Style>

			<Style id=\"BLACKLINE\">
				<LineStyle>
					<color>AA000000</color>
					<width>2</width>
				</LineStyle>
			</Style>

			<Style id=\"BLACKLINE2\">
				<LineStyle>
					<color>AA000000</color>
					<width>1</width>
				</LineStyle>
			</Style>

			";
    }
}
