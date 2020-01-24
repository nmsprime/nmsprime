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

/*
 * Show Customers (Modems) on Topography
 *
 * One Object Represents one Topography View - KML File
 *
 * Workflow: See Confluence
 * - Route: Customer/{field}/{search} --> show($field, $search) --> show_topo($modems)
 * - Route: CustomerRect/{x1}/{x2}/{y1}/{y2} -> show_rect($x1, $x2, $y1, $y2) -> show_topo($modems)
 * - Route: CustomerModem/{topo_dia}/{ids} -> show_modem_ids() -> show_topo() or show_diagrams()
 *
 * Note: Right Panel for Switching between topography and diagrams does only use show_modem_ids().
 *       There is no seperate diagram function for show() and show_rect(). Instead show_topo and
 *       show_diagrams() call makeTabs() which generates topography and diagram links
 *       to show_modem_ids().
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
     * File Specific Stuff
     */
    private $file_pre = "<?xml version='1.0' encoding='UTF-8'?>
		<kml xmlns='http://earth.google.com/kml/2.2'>
		<Document>
		  <name>mbg - Kunden</name>
		  <description><![CDATA[]]></description>


		  <Style id='style-1'>
			<IconStyle>
			  <Icon>
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/red-dot.png</href>
			  </Icon>
			</IconStyle>
		  </Style>
		  <Style id='style0'>
			<IconStyle>
			  <Icon>
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/green-dot.png</href>
			  </Icon>
			</IconStyle>
		  </Style>

		  <Style id='style1'>
			<IconStyle>
			  <Icon>
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/yellow-dot.png</href>
			  </Icon>
			</IconStyle>
		  </Style>

		  <Style id='style2'>
			<IconStyle>
			  <Icon>
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/orange-dot.png</href>
			  </Icon>
			</IconStyle>
		  </Style>

		  <Style id='styleunknown'>
			<IconStyle>
			  <Icon>
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/blue-dot.png</href>
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

    private $file_end = '</Document></kml>';

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

        return $this->show_topo($modemQuery['selectedModel'], Request::get('row'), $modemQuery['allModels']);
    }

    /*
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

        return $this->show_topo($modemQuery['selectedModel'], Request::get('row'), $modemQuery['allModels']);
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

        return $this->show_topo($modems['selectedModel'], null, $modems['allModels']);
    }

    /**
     * Show all customers in proximity (radius in meters)
     *
     * @author: Ole Ernst
     */
    public function show_prox()
    {
        $modems = $this->filterModel(Modem::whereIn('id', Modem::find(Request::get('id'))->proximity_search(Request::get('radius'))));

        return $this->show_topo($modems['selectedModel'], Request::get('row'), $modems['allModels']);
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

        return $this->show_topo($modemQuery['selectedModel'], null, $modemQuery['allModels']);
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
    public function show_topo($modemQuery, $row = null, $allModels = null)
    {
        $models = $allModels ?: clone $modemQuery;
        $models = $models->whereNotNull('model')->groupBy('model')->get(['model'])->pluck('model')->all();

        $modems = $modemQuery->where('contract_id', '>', '0')->orderByRaw('10000000*modem.x+modem.y')->get();

        if (! $models && ! $modems->count()) {
            return \View::make('errors.generic')->with('message', 'No Modem Entry found');
        }

        if (! $row) {
            $row = 'us_pwr';
        }

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

        // Prepare topography map
        $target = $this->html_target;
        $route_name = 'Tree';
        $view_header = 'Topography - Modems';
        $body_onload = 'init_for_map';
        $tabs = $this->makeTabs($modems);
        $kmls = $this->__kml_to_modems($modems);
        $file = route('HfcCustomer.get_file', ['type' => 'kml', 'filename' => basename($file)]);

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact('file', 'target', 'route_name', 'view_header', 'body_onload', 'tabs', 'kmls', 'models')));
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
            ->join('netelement', 'modem.netelement_id', 'netelement.id')
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

    /*
    * Show Modems Diagrams
    *
    * TODO: - add cacti graph template id's to ENV
    *
    * @param modemQuery: QueryBuilder like Modem::where()
    * @return view with modem diagrams
    *
    * @author: Torsten Schmidt
    */
    public function show_diagrams($modemQuery)
    {
        // check if ProvMon is installed
        if (! \Module::collections()->has('ProvMon')) {
            return \View::make('errors.generic')->with('message', 'Module Provisioning Monitoring (ProvMon) not installed');
        }

        $monitoring = [];
        $provmon = new \Modules\ProvMon\Http\Controllers\ProvMonController;
        $before = microtime(true);
        $types = ['ds_pwr', 'ds_snr', 'us_snr', 'us_pwr'];

        $modems = $modemQuery->orderBy('city')->orderBy('street')->orderBy('house_number')->get();

        // foreach modem
        foreach ($modems as $modem) {
            // load per modem diagrams
            $dia_ids = [$provmon->monitoring_get_graph_template_id('DOCSIS Overview')];
            if (! Request::filled('row')) {
                $dia_ids[] = $provmon->monitoring_get_graph_template_id('DOCSIS US PWR');
            } elseif (in_array(Request::get('row'), $types)) {
                $dia_ids[] = $provmon->monitoring_get_graph_template_id('DOCSIS '.strtoupper(str_replace('_', ' ', Request::get('row'))));
            } elseif (Request::get('row') == 'all') {
                $dia_ids = [];
                foreach ($types as $type) {
                    $dia_ids[] = $provmon->monitoring_get_graph_template_id('DOCSIS '.strtoupper(str_replace('_', ' ', $type)));
                }
            }

            $dia = $provmon->monitoring($modem, $dia_ids);

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

        // prepare/load panel right
        $tabs = $this->makeTabs($modems);

        // Log: time measurement
        $after = microtime(true);
        \Log::debug('DIA: load of entire set takes '.($after - $before).' s');

        // show view
        return \View::make('HfcCustomer::Tree.dias', $this->compact_prep_view(compact('monitoring', 'tabs')));
    }

    /*
    * Show Modem Topography or Diagrams with param $ids
    *
    * @param topo: 'true' (string): show topography, other show diagrams
    * @param modem: id's to show, plus (+) seperated string list, like '100000+100001+100002'
    * @return: view with modem diagrams
    *
    * @author: Torsten Schmidt
    */
    public function show_modem_ids($topo, $_ids)
    {
        $ids = [];

        if (! is_array($_ids)) {
            $ids = explode('+', $_ids);
        }

        $modemQuery = $this->getModemBaseQuery()->whereIn('modem.id', $ids);

        if ($topo == 'true') {
            return $this->show_topo($modemQuery);
        }

        return $this->show_diagrams($modemQuery);
    }

    /**
     * Prepare $tabs vaiable for switching topography/diagrams mode
     *
     * @param Collection of Modems
     * @return: prepared $tabs variable
     *
     * @author: Torsten Schmidt
     */
    private function makeTabs($modems)
    {
        $ids = '0';
        foreach ($modems as $modem) {
            $ids .= '+'.$modem->id;
        }

        return [['name' => 'Edit', 'route' => 'NetElement.edit', 'link' => $modem->netelement_id],
            ['name' => 'Topography', 'route' => 'CustomerModem.show', 'link' => ['true', $ids, 'row' => Request::get('row')]],
            ['name' => 'Diagramms', 'route' => 'CustomerModem.show', 'link' => ['false', $ids, 'row' => Request::get('row')]], ];
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
        $file = $this->file_pre;
        $newTab = ProvBase::first()->modem_edit_page_new_tab;
        $baseUrl = \BaseRoute::get_base_url();

        if (! $row) {
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
						 <description><![CDATA[$descr]]></description>
						 <styleUrl>$style</styleUrl>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
                    $file .= "\n <Placemark><name>$num</name>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
                }

                // Reset Var's
                $state = 3;      // unknown
                $descr = '<br>'; // new line for descr
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
                $cur_clr = BaseViewController::get_quality_color_orig(explode('_', $row)[0], explode('_', $row)[1], [$row_val])[0];
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
            $descr .= '<a target="'.$this->html_target."\" href='".$baseUrl."/Modem/$modem->id'>$modem->mac</a>, $modem->contract_id, $modem->lastname, $states[$cur_clr] ($row_val)<br>";
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
				 <description><![CDATA[$descr]]></description>
				 <styleUrl>$style</styleUrl>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
            $file .= "\n <Placemark><name>$num</name>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
        }

        // Write Files ..
        $file .= $this->file_end;
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
}
