<?php

namespace Modules\HfcCustomer\Http\Controllers;

use DB;
use Request;
use Modules\ProvBase\Entities\Modem;
use Modules\HfcCustomer\Entities\Mpr;
use Modules\HfcReq\Entities\NetElement;
use Modules\ProvBase\Entities\ProvBase;
use App\Http\Controllers\BaseViewController;
use Modules\HfcReq\Http\Controllers\NetElementController;

/**
 * Show Customers (Modems) on Topography
 *
 * One Object Represents one Topography View - KML File
 *
 * Workflow: See Confluence
 * - Route: Customer/{field}/{search} --> show($field, $search) --> show_topo($modems)
 * - Route: CustomerRect/{x1}/{x2}/{y1}/{y2} -> show_rect($x1, $x2, $y1, $y2) -> show_topo($modems)
 * - Route: CustomerModem/modems/{ids} -> showModems()
 * - Route: CustomerModem/diagrams/{ids} -> showDiagrams()
 *
 * Note: There is no seperate diagram function for show() and show_rect(). Instead show_topo and
 *       showDiagrams() call tabs() which generates topography and diagram links
 *
 * @author: Torsten Schmidt
 */
class CustomerTopoController extends NetElementController
{
    protected $html_target = '';

    /*
     * Local tmp folder required for generating the images
     * (relative to /storage/app)
     */
    public static $path_rel = 'data/hfccustomer/kml/';

    /*
     * Constructor: Set local vars
     */
    public function __construct()
    {
        $this->file = self::$path_rel.sha1(uniqid(mt_rand(), true)).'.kml';
    }

    /**
     * Show Modems matching Modem sql $field = $value
     *
     * @param field: search field name in tree table
     * @param search: the search value to look in tree table $field
     * @return view with SVG image
     *
     * @author: Torsten Schmidt
     */
    public function show($field, $search)
    {
        $query = $this->getModemBaseQuery();
        $query = $field == 'all' ? $query->where('id', '>', 2) : $query->where($field, $search);

        $modemQuery = $this->filterModel($query);

        return $this->show_topo($modemQuery['selectedModel'], $modemQuery['allModels'], false, $field == 'netelement_id' ? $search : false);
    }

    /**
     * Show Customer in Rectangle
     *
     * @param field: search field name in tree table
     * @param search: the search value to look in tree table $field
     * @return view with SVG image
     *
     * @author: Torsten Schmidt
     */
    public function show_rect($x1, $x2, $y1, $y2)
    {
        $query = $this->getModemBaseQuery()
            ->where('modem.x', '>', $x1)->where('modem.x', '<', $x2)
            ->where('modem.y', '>', $y1)->where('modem.y', '<', $y2);

        $modemQuery = $this->filterModel($query);

        return $this->show_topo($modemQuery['selectedModel'], $modemQuery['allModels']);
    }

    /**
     * Show all customers within the polygon
     *
     * @param poly: the polygon, its vertices are separated by semicolons
     * @author: Ole Ernst
     */
    public function show_poly($poly)
    {
        $ids = [0];
        $poly = explode(';', $poly);
        // every point must have two coordinates
        if (count($poly) % 2) {
            return \Redirect::back();
        }
        // convert from flat array into array of array as expeceted by point_in_polygon
        while ($poly) {
            $polygon[] = [array_shift($poly), array_shift($poly)];
        }
        // add modems which are within the polygon
        $query = $this->getModemBaseQuery();
        foreach ($query->get() as $modem) {
            if (Mpr::point_in_polygon([$modem->x, $modem->y], $polygon)) {
                array_push($ids, $modem->id);
            }
        }

        $modems = $this->filterModel($query->whereIn('modem.id', $ids));

        return $this->show_topo($modems['selectedModel'], $modems['allModels']);
    }

    /**
     * Show all customers in proximity (radius in meters)
     *
     * @author: Ole Ernst
     */
    public function show_prox()
    {
        $modems = $this->filterModel(Modem::whereIn('id', Modem::find(Request::get('id'))->proximity_search(Request::get('radius'))));

        return $this->show_topo($modems['selectedModel'], $modems['allModels']);
    }

    /**
     * Show customers with an upstream power of bigger than 50dBmV
     *
     * @author: Ole Ernst
     */
    public function show_impaired()
    {
        $query = $this->getModemBaseQuery()->where('us_pwr', '>', '50');

        // return back if all modems are fine
        if (! $query->count()) {
            return back();
        }

        $modemQuery = $this->filterModel($query);

        return $this->show_topo($modemQuery['selectedModel'], $modemQuery['allModels']);
    }

    /*
    * Show Modems om Topography
    *
    * @param modems the preselected Modem model, like Modem::where()
    * @param field search field name in tree table, only for display
    * @param search the search value to look in tree table $field, only for display
    * @return view with SVG image
    *
    * @author: Torsten Schmidt
    */
    private function show_topo($modemQuery, $allModels = null, $pnmMap = false, $withHistory = null)
    {
        $models = $allModels ?: clone $modemQuery;
        $models = $models->whereNotNull('model')->groupBy('model')->get(['model'])->pluck('model')->all();

        $modems = $modemQuery->where('contract_id', '>', '0')->orderByRaw('10000000*modem.x+modem.y')->get();

        if (! $models && ! $modems->count()) {
            return \View::make('errors.generic')->with('message', 'No Modem Entry found');
        }

        $row = Request::get('row') ?: 'us_pwr';

        $models = [];
        foreach ($allModels ? $allModels->get() : $modems as $modem) {
            if ($modem->model != '') {
                $models[] = $modem->model;
            }
        }

        sort($models);
        $models = array_unique($models) ?? null;

        // Generate SVG file
        $file = $this->kml_generate($modems, $row);

        if (! $file) {
            return \View::make('errors.generic')->with('message', 'Failed to generate SVG file');
        }

        if ($pnmMap) {
            list($dim, $point) = $this->getHeatMapData($modems);
            $users = $this->getUserMapData();
        }

        $target = $this->html_target;
        $route_name = 'Tree';
        $view_header = 'Topography - Modems';
        $body_onload = 'init_for_map';
        $tabs = self::tabs($modems);
        $breadcrumb = self::breadcrumb($modems);

        // Prepare topography map
        $kmls = $this->__kml_to_modems($modems);
        $file = route('HfcCustomer.get_file', ['type' => 'kml', 'filename' => basename($file)]);

        // History Table and Slider
        if (! $withHistory) {
            $withHistory = $modems->groupBy('netelement_id')->map->count()->sort()->keys()->last();
        }

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact(
            'file', 'target', 'route_name', 'view_header', 'body_onload', 'tabs', 'kmls', 'models', 'breadcrumb', 'dim', 'point', 'withHistory', 'users')));
    }

    private function getHeatMapData($modems)
    {
        $dim = [];
        $point = [];

        $max = $modems->pluck('fft_max')->filter(function ($value) {
            return $value > 0 && $value < 5;
        })->max();

        if (! $max) {
            $max = 1;
        }

        foreach ($modems as $modem) {
            if (! $modem->tdr || $modem->fft_max < 0 || $modem->fft_max > 5) {
                continue;
            }

            $x = floatval($modem->x);
            $y = floatval($modem->y);

            $point[] = $y;
            $point[] = $x;
            $point[] = $modem->tdr;

            $temp = round($modem->tdr / 111111.1, 4);
            $percent = \Request::get('percent') ?: 100;

            for ($i = 0; $i <= 360; $i += 10) {
                $dim[] = $temp * cos($i) + $y;
                $dim[] = $temp * sin($i) + $x;
                $dim[] = $modem->fft_max / $max * $percent / 100;
            }
        }

        return [array_chunk($dim, 3), array_chunk($point, 3)];
    }

    private function getUserMapData()
    {
        return \App\User::whereNotNull('geopos_x')->where('geopos_updated_at', '>', date('Y-m-d H:i:s', time() - 60*60))->get()->toArray();
    }

    /**
     * Get Query for all Modems belonging to a valid contract or being online (us_pwr > 0)
     *
     * @return obj Illuminate\Database\Query\Bilder
     */
    private function getModemBaseQuery()
    {
        return DB::table('modem')
            ->join('contract', 'contract.id', '=', 'modem.contract_id')
            ->leftJoin('netelement', 'modem.netelement_id', 'netelement.id')
            ->whereNull('modem.deleted_at')
            ->whereNull('contract.deleted_at')
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query
                        ->where('contract_start', '>', 'CURRENT_DATE')
                        ->where(whereLaterOrEqual('contract_end', 'CURRENT_DATE'));
                    })
                    ->orWhere('us_pwr', '>', 0);
            })
            ->select(['modem.*', 'netelement.cluster']);
    }

    /**
     * Extend query by filtering specified modems by their ID
     *
     * @return obj Illuminate\Database\Query\Bilder
     */
    private function filterModems($query, $modem_ids)
    {
        $ids = [];

        if (! is_array($modem_ids)) {
            $ids = explode('+', $modem_ids);
        }

        return $query->whereIn('modem.id', $ids);
    }

    /**
     * Compose ERD breadcrumb route for customer map view
     * Note: Extend this function when more breadcrumbs will be used
     *
     * @return string
     * @author Nino Ryschawy
     */
    public static function breadcrumb($modems)
    {
        if (! $modems->count()) {
            return;
        }

        $modem = $modems->where('netelement_id', '!=', null)->first();
        $netelement = $modem ? NetElement::find($modem->netelement_id) : null;

        if (! $netelement) {
            return route('TreeErd.show', ['all', 1]);
        }

        $cluster = $netelement->cluster ?: $netelement->net;
        $cluster = $cluster == $netelement->id ? $netelement : NetElement::find($cluster);

        return route('TreeErd.show', [$cluster->netelementtype->name, $cluster->id]);
    }

    /**
     * Filter modems in topography.
     * Only show the selected model.
     *
     * @author Roy Schneider
     * @param Illuminate\Database\Eloquent\Builder $modems
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function filterModel($modemQuery)
    {
        $model = Request::get('model');

        if ($model == '') {
            return ['selectedModel' => $modemQuery, 'allModels' => null];
        }

        return ['allModels' => clone $modemQuery, 'selectedModel' => $modemQuery->where('model', $model)];
    }

    /**
     * KML Upload Array: Generate the KML file array
     * based on the provided $modems. Show all related
     * kml files which are in relation to a modem cluster.
     *
     * @param Collection modems list
     * @return array of KML files, like ['file', 'descr']
     *
     * @author: Torsten Schmidt, Nino Ryschawy
     */
    private function __kml_to_modems($modems)
    {
        $clusters = array_unique($modems->pluck('cluster')->all());

        $netelements = NetElement::whereIn('cluster', $clusters)
            ->whereNotNull('pos')->where('pos', '!=', ' ')
            ->whereNotNull('kml_file')
            ->get();

        // parse all NetElement's with a cluster id in $clusters[]
        return $this->kml_file_array($netelements);
    }

    /**
     * Show specific Modems on map
     *
     * @param string    modem IDs
     * @return Illuminate\View\View
     *
     * @author Nino Ryschawy
     */
    public function showModems($ids)
    {
        return $this->show_topo($this->filterModems($this->getModemBaseQuery(), $ids));
    }

    public function showPNM($ids)
    {
        return $this->show_topo($this->filterModems($this->getModemBaseQuery(), $ids), null, true);
    }

    /**
     * Show Modems Diagrams
     *
     * TODO: - add cacti graph template id's to ENV
     *
     * @param string
     * @return view with modem diagrams
     *
     * @author: Torsten Schmidt
     */
    public function showDiagrams($modemIds)
    {
        $modemQuery = $this->filterModems($this->getModemBaseQuery(), $modemIds);

        // check if ProvMon is installed
        if (! \Module::collections()->has('ProvMon')) {
            return \View::make('errors.generic')->with('message', 'Module Provisioning Monitoring (ProvMon) not installed');
        }

        $monitoring = [];
        $provmon = new \Modules\ProvMon\Http\Controllers\ProvMonController;
        $before = microtime(true);
        $modems = $modemQuery->orderBy('city')->orderBy('street')->orderBy('house_number')->get();
        $withHistory = $modems->groupBy('netelement_id')->map->count()->sort()->keys()->last();

        // foreach modem
        foreach ($modems as $modem) {
            $dia = $provmon->monitoring($modem, $this->getGraphTemplateId(Request::get('row')));

            // valid diagram's ?
            if ($dia != false) {
                // Description Line per Modem
                $descr = $modem->lastname.' - '.$modem->zip.', '.$modem->city.', '.$modem->street.' '.$modem->house_number.' - '.$modem->mac;
                $dia['descr'] = \HTML::linkRoute('Modem.edit', $descr, $modem->id);
                $dia['row'] = Request::input('row', 'us_pwr');

                // Add diagrams to monitoring array (goes directly to view)
                $monitoring[$modem->id] = $dia;
            }
        }

        $tabs = self::tabs($modems);
        $breadcrumb = self::breadcrumb($modems);

        // Log: time measurement
        $after = microtime(true);
        \Log::debug('DIA: load of entire set takes '.($after - $before).' s');

        // show view
        return \View::make('HfcCustomer::Tree.dias', $this->compact_prep_view(compact('monitoring', 'tabs', 'breadcrumb', 'withHistory')));
    }

    /**
     * Get cacti graph template ids matching the search term
     *
     * @param search: search term or null
     * @return: array of related cacti graph template ids
     *
     * @author: Ole Ernst
     */
    private function getGraphTemplateId(?string $search): array
    {
        if (! $search) {
            $search = 'us_pwr';
        }

        $provmon = new \Modules\ProvMon\Http\Controllers\ProvMonController;

        if ($search == 'all') {
            return $provmon->monitoringGetGraphTemplateId('DOCSIS%')->toArray();
        }

        if (in_array($search, array_keys(config('hfcreq.hfParameters')))) {
            $search = strtoupper(str_replace('_', ' ', $search));
        }

        return $provmon->monitoringGetGraphTemplateId('DOCSIS Overview')
            ->merge($provmon->monitoringGetGraphTemplateId("DOCSIS $search%"))
            ->toArray();
    }

    /**
     * Prepare $tabs variable for switching topography/diagrams mode
     *
     * @param Collection of Modems
     * @return: array
     *
     * @author: Torsten Schmidt, Nino Ryschawy
     */
    public static function tabs($modems)
    {
        $ids = '0';
        foreach ($modems as $modem) {
            $ids .= '+'.$modem->id;
        }

        $tabs = [
            // ['name' => 'Edit', 'icon' => 'pencil', 'route' => 'NetElement.edit', 'link' => $modem->netelement_id],
            ['name' => trans('hfccustomer::view.vicinityGraph'), 'icon' => 'fa-sitemap', 'route' => 'VicinityGraph.show', 'link' => $ids],
            ['name' => 'Topography', 'icon' => 'map', 'route' => 'CustomerModem.showModems', 'link' => [$ids, 'row' => Request::get('row')]],
            ['name' => 'PNM', 'icon' => 'globe', 'route' => 'CustomerModem.showPNM', 'link' => [$ids, 'row' => Request::get('row')]],
            ['name' => trans('view.Diagrams'), 'icon' => 'area-chart', 'route' => 'CustomerModem.showDiagrams', 'link' => [$ids, 'row' => Request::get('row')]],
        ];

        return $tabs;
    }

    /**
     * Generate KML File with Customer Modems Inside
     *
     * @param  obj      Collection of the modems to display
     * @return string   the path of the generated *.kml file to be included via asset ()
     *
     * @author: Torsten Schmidt
     */
    public function kml_generate($modems, $row)
    {
        $x = $y = $num = 0;
        $clrs = [];
        $str = $descr = $city = $zip = $nr = '';
        $states = [-1 => 'offline', 0 => 'okay', 1 => 'impaired', 2 => 'critical'];
        $file = $this->file_pre(asset(\Modules\HfcBase\Http\Controllers\TreeTopographyController::$path_images));
        $newTab = ProvBase::first()->modem_edit_page_new_tab;
        $baseUrl = \BaseRoute::get_base_url();

        if (! $row || ! in_array($row, array_keys(config('hfcreq.hfParameters')))) {
            $row = 'us_pwr';
        }

        foreach ($modems as $modem) {
            //
            // Print Marker AND Reset Vars IF new GPS position
            //
            if ($x != $modem->x || $y != $modem->y) {
                // Print Marker
                // if all modems in one location are offline show a red marker,
                // otherwise the average of all non-offline modem states will determine the color
                $clrs = array_diff($clrs, [-1]);
                if (empty($clrs)) {
                    $clr = -1;
                } else {
                    $clr = ($x) ? round(array_sum($clrs) / count($clrs)) : '';
                }
                $style = "#style$clr"; // green, yellow, red

                // Reset Vars
                $clrs = [];
                $pos = "$x, $y, 0.000000";

                if ($x) {                  // ignore (0,0)
                    $file .= "\n <Placemark><name>1</name>
						 <description><![CDATA[{$descr}</tbody></table>]]></description>
						 <styleUrl>$style</styleUrl>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
                    $file .= "\n <Placemark><name>$num</name>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
                }

                // Reset Var's
                $state = 3;      // unknown
                $descr = '<table><thead><tr><th>MAC</th><th>Contract</th><th>Name</th><th>US<br>PWR</th><th>US<br>SNR</th><th>DS<br>PWR</th><th>DS<br>SNR</th></tr></thead><tbody>';
                $x = $modem->x;  // get next GPS pos ..
                $y = $modem->y;
                $num = 0;
            }

            if ($row == 'ds_us') {
                // DS_ref (50) + US_ref (0) - DS_modem - US_modem
                $row_val = 50 - $modem->ds_pwr - $modem->us_pwr;
            } else {
                $row_val = $modem->{$row};
            }

            if ($modem->us_pwr != 0) {
                $cur_clr = BaseViewController::getQualityColor(explode('_', $row)[0], null, explode('_', $row)[1], $row_val, false);
            } else {
                $cur_clr = -1;
            }
            $clrs[] = $cur_clr;

            // Headline: Address from DB
            if ($str != $modem->street || $city != $modem->city || $zip != $modem->zip || $nr != $modem->house_number) {
                $str = $modem->street;
                $city = $modem->city;
                $zip = $modem->zip;
                $nr = $modem->house_number;
                $descr .= "<b>$zip, $city, $str, $nr</b><br>";
            }

            if ($newTab) {
                $this->html_target = '_blank';
            }

            // add descr line
            $descr .= '<tr>'.
                "<td style='text-align: center;'><a target='{$this->html_target}' href='$baseUrl/Modem/$modem->id'>$modem->mac</a></td>".
                "<td style='text-align: center;'>$modem->contract_id</td>".
                "<td style='text-align: center;'>$modem->lastname</td>";

            $lut = ['green', 'yellow', 'orange', ''];
            foreach (array_keys(config('hfcreq.hfParameters')) as $r) {
                $descrColor = $modem->us_pwr ? $lut[BaseViewController::getQualityColor(explode('_', $r)[0], null, explode('_', $r)[1], $modem->{$r}, false)] : 'red';
                $descr .= "<td style='text-align: center;'><mark style='background-color: $descrColor;'>{$modem->{$r}}</mark></td>";
            }
            $descr .= '</tr>';

            $num += 1;
        }

        //
        // Print Last Marker
        //
        // if all modems in one location are offline show a red marker,
        // otherwise the average of all non-offline modem states will determine the color
        $clrs = array_diff($clrs, [-1]);
        if (empty($clrs)) {
            $clr = -1;
        } else {
            $clr = round(array_sum($clrs) / count($clrs));
        }
        $style = "#style$clr"; // green, yellow, red

        $pos = "$x, $y, 0.000000";
        if ($x) {
            $file .= "\n <Placemark><name></name>
				 <description><![CDATA[{$descr}</tbody></table>]]></description>
				 <styleUrl>$style</styleUrl>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
            $file .= "\n <Placemark><name>$num</name>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
        }

        // Write Files ..
        $file .= '</Document></kml>';
        \Storage::put($this->file, $file);

        return str_replace(storage_path(), '', \Storage::getAdapter()->applyPathPrefix($this->file));
    }

    /**
     * retrieve file if existent, this can be only used by authenticated and
     * authorized users (see corresponding Route::get in Http/routes.php)
     *
     * @author Ole Ernst
     *
     * @param string $filename name of the file
     * @return mixed
     */
    public function get_file($type, $filename)
    {
        $path = storage_path("app/data/hfccustomer/kml/$filename");
        if (file_exists($path)) {
            return \Response::file($path);
        } else {
            return \App::abort(404);
        }
    }

    private function file_pre($p)
    {
        return "<?xml version='1.0' encoding='UTF-8'?>
        <kml xmlns='https://www.opengis.net/kml/2.2'>
        <Document>
          <name>mbg - Kunden</name>
          <description><![CDATA[]]></description>

          <Style id='style-1'>
            <IconStyle>
              <Icon>
                <href>$p/red-dot.png</href>
              </Icon>
            </IconStyle>
          </Style>
          <Style id='style0'>
            <IconStyle>
              <Icon>
                <href>$p/green-dot.png</href>
              </Icon>
            </IconStyle>
          </Style>

          <Style id='style1'>
            <IconStyle>
              <Icon>
                <href>$p/yellow-dot.png</href>
              </Icon>
            </IconStyle>
          </Style>

          <Style id='style2'>
            <IconStyle>
              <Icon>
                <href>$p/orange-dot.png</href>
              </Icon>
            </IconStyle>
          </Style>

          <Style id='styleunknown'>
            <IconStyle>
              <Icon>
                <href>$p/blue-dot.png</href>
              </Icon>
            </IconStyle>
          </Style>

          <Style id=\"YELLOWLINE\">
            <LineStyle>
                <color>55000000</color>
             <width>1</width>
            </LineStyle>
          </Style>
        ";
    }
}
