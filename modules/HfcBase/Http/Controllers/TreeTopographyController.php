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
    public static $path_images = 'modules/hfcbase/kml/';

    /*
    @author: John Adebayo
    Private property determines the range of intensity of the Heatmap, for the amplitude values
    of the modem
    $min_value is the minimum for the database query
    $max_value is the max for the database query
    */
    private $min_value;
    private $max_value;

    /*
     * Constructor: Set local vars
     */
    public function __construct()
    {
        // the relative (to /storage/app) file path based on a random hash
        $this->file = self::$path_rel.sha1(uniqid(mt_rand(), true)).'.kml';

        return parent::__construct();
    }

    /*
    @author: John Adebayo
    This function sets the value of the display in percentage
    e.g 100% = 5 i.e the variable max_value above
    */
    public function set_maxmin()
    {
        //\Input::all();
        //$max = \Input::get('max');
        //$percent = \Input::get('percent');
        //$max = 5; $percent = 20;
        //$min = $percent / 100 * $max;
        $min = 0;
        $max = 5;
        $this->max_value = $max;
        $this->min_value = $min;
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
            ->with('parent', 'mprs.mprgeopos', 'modemsUpstreamAndPositionAvg')->get();

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

        $dim = [];
        $point = [];
        $thresh = $this->set_maxmin();
        //$work = \DB::table('modem')->max('fft_max');
        //$work = \DB::table('modem')->where('fft_max', '=', 6.48)->select('id')->get();
        //dd($work);
        $max = \DB::table('modem')->where('fft_max', '>', $this->min_value)->where('fft_max', '<', $this->max_value)->orderBy('street')->limit(1000)->max('fft_max');
        foreach (\Modules\ProvBase\Entities\Modem::where('fft_max', '>', $this->min_value)->where('fft_max', '<', $this->max_value)->orderBy('street')->limit(1000)->get() as $modem => $value) {
            $point[] = $value['y'];
            $point[] = $value['x'];
            $point[] = $value['tdr'].'';
            $temp = round($value['tdr'] / 111111.1, 4);
            for ($i = 0; $i <= 360; $i += 10) {
                $dim[] = $temp * cos($i) + $value['y'];
                $dim[] = $temp * sin($i) + $value['x'];
                $dim[] = $value['fft_max'] / $max * \Input::get('percent') / 100;
            }
        }

        $dim = array_chunk($dim, 3);
        $point = array_chunk($point, 3);

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact(
            'file', 'tabs', 'field', 'search', 'mpr', 'kmls', 'body_onload', 'view_header', 'route_name', 'dim', 'point'
        )));
    }

    /**
     * MPS: Modem Positioning Rules
     * return multi array with MPS rules and Geopositions, like
     *   [ [mpr.id] => [0 => [0=>x,1=>y], 1 => [0=>x,1=>y], ..], .. ]
     * enable and see dd() for a more detailed view
     *
     * @author: Torsten Schmidt
     * @param Illuminate\Database\Eloquent\Builder $trees The Tree Objects to be displayed
     * @return array MPS rules and geopos for all $tree objects
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
        $file = $this->file_pre(asset(self::$path_images));
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

            // skip empty pos and lines to elements not in search string
            if ($pos2 == null ||
                $pos2 == '' ||
                $pos2 == '0,0' ||
                ! ArrayHelper::objArraySearch($netelements, 'id', $netelement->parent->id)) {
                continue;
            }

            // Line Color - Style - See NetElementType::undeletables for id
            $style = '#BLACKLINE';
            if ($netelement->netelementtype_id == 4) {
                $style = '#REDLINE';
            }

            if ($netelement->netelementtype_id == 5) {
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
            foreach ($netelements as $netelement) {
                $id = $netelement->id;
                $name = $netelement->name;
                $pos_tree = $netelement->pos;
                $pos = $netelement->modemsUsPwrPosAvgs;

                $onlineModems = $netelement->modems_online_count;
                $allModems = $netelement->modems_count;
                $modemUsPwrAvg = $netelement->modemsUsPwrAvg;
                $modemStateAnalysis = new \Modules\HfcCustomer\Helpers\ModemStateAnalysis(
                    $onlineModems, $allModems, $modemUsPwrAvg);

                if ($pos->x_avg != null && $pos->y_avg != null) {
                    $xavg = round($pos->x_avg, 4);
                    $yavg = round($pos->y_avg, 4);
                    $icon = $modemStateAnalysis->toColor();
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

                    $file .= "Amp/Node: $name<br><br>Number All CM: $numa<br>Number Online CM: $num ($pro %)<br>Number Critical CM: $cri<br>US Level Average: $avg<br><br><a href=\"$url\" target=\"\" alt=\"\">Show all Customers</a>";

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
                $fiber = $router = $rstate = $ystate = 0;

                $file .= '
                    <Placemark>
                    <name></name>
                    <description><![CDATA[';
            }

            $type = $netelement->netelementtype_id;
            $parent = $netelement->parent ? $netelement->parent_id : null;

            if ($netelement->state == 'warning') {
                $ystate += 1;
            }

            if ($netelement->state == 'danger') {
                $rstate += 1;
            }

            // See NetElementType::undeletables for id
            if (in_array($type, [1, 2, 3, 6])) {
                $router += 1;
            }

            if ($type == 5) {
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

        <kml xmlns=\"https://www.opengis.net/kml/2.2\">
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
