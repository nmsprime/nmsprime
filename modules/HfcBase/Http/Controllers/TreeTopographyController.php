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
        $operator = '=';

        if ($field == 'all' || ($field == 'id' && $search == 2)) {
            $field = 'id';
            $operator = '>';
            $search = 2;
        }

        $netelements = NetElement::withActiveModems($field, $operator, $search)
            ->whereNotNull('pos')
            ->where('pos', '!=', ' ')
            ->with('mprs.mprgeopos', 'modemsUpstreamAndPositionAvg')->get();

        // Generate KML file
        $file = $this->kml_generate($netelements);

        if (! $file) {
            return \View::make('errors.generic', [
                'error' => 422,
                'message' => trans('messages.no_Netelements'),
            ]);
        }

        $mpr = $this->mpr($netelements);
        $kmls = $this->kml_file_array($netelements);
        $tabs = TreeErdController::getTabs($field, $search);
        $file = route('HfcBase.get_file', ['type' => 'kml', 'filename' => basename($file)]);

        $route_name = 'Tree';
        $view_header = 'Topography';
        $body_onload = 'init_for_map';

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact(
            'file', 'tabs', 'field', 'search', 'mpr', 'kmls', 'body_onload', 'view_header', 'route_name'
        )));
    }

    /**
     * MPS: Modem Positioning Rules
     * return multi array with MPS rules and Geopositions, like
     *   [ [mpr.id] => [0 => [0=>x,1=>y], 1 => [0=>x,1=>y], ..], .. ]
     * enable and see dd() for a more detailed view
     *
     * @param trees: The Tree Objects to be displayed
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

        foreach ($trees as $tree) {
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

        return $ret;
    }

    /**
     * Generate the KML File
     *
     * @param obj  Collection of relevant netelements
     * @return the path of the generated *.kml file, could be included via asset ()
     *
     * @author: Torsten Schmidt
     */
    public function kml_generate($netelements)
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
        if (! $netelements->count()) {
            return;
        }

        foreach ($netelements as $netelement) {
            $parent = $netelement->parent;
            $pos1 = $netelement->pos;
            $pos2 = $parent ? $parent->pos : null;
            $name = $netelement->id;
            // Type is stored in relation, tp doesnt exist
            $type = $netelement->type;
            $tp = $netelement->tp;

            // skip empty pos and lines to elements not in search string
            if ($pos2 == null ||
                $pos2 == '' ||
                $pos2 == '0,0' ||
                ! ArrayHelper::objArraySearch($netelements, 'id', $netelement->parent->id)) {
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
                <name>$parent->name</name>
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
            $ModemHelper = \Modules\HfcCustomer\Entities\ModemHelper::class;

            foreach ($netelements as $netelement) {
                $id = $netelement->id;
                $name = $netelement->name;
                $pos_tree = $netelement->pos;
                $pos = $netelement->modemsUsPwrPosAvgs;

                if (isset($pos->x_avg)) {
                    $xavg = round($pos->x_avg, 4);
                    $yavg = round($pos->y_avg, 4);
                    $icon = $ModemHelper::ms_state_to_color($ModemHelper::ms_state($netelement));
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

                    $num = $netelement->modems_online_count;
                    $numa = $netelement->modems_count;
                    $pro = $numa ? round(100 * $num / $numa, 0) : 0;
                    $cri = $netelement->modems_critical_count;
                    $avg = $netelement->modemsUsPwrAvg;
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

        foreach ($netelements as $netelement) {
            $p2 = $netelement->pos;

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

            $type = $netelement->type;
            $parent = $netelement->parent ? $netelement->parent->id : null;
            $state = $netelement->get_bsclass();

            if ($netelement->state == 'warning') {
                $ystate += 1;
            }

            if ($netelement->state == 'danger') {
                $rstate += 1;
            }

            if (($type == 'NETGW') || ($type == 'CLUSTER') || ($type == 'DATA') || ($type == 'NET')) {
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
