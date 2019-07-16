<?php

namespace Modules\HfcCustomer\Http\Controllers;

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
        // prepare search
        $s = "$field='$search'";
        if ($field == 'all') {
            $s = 'id>2';
        }

        $modems = $this->filterModel(Modem::whereRaw($s));

        return $this->show_topo($modems['selectedModel'], \Input::get('row'), $modems['allModels']);
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
        $modems = $this->filterModel(Modem::whereRaw("(($x1 < x) AND (x < $x2) AND ($y1 < y) AND (y < $y2))"));

        return $this->show_topo($modems['selectedModel'], \Input::get('row'), $modems['allModels']);
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
        foreach (\DB::table('modem')->select('id', 'x', 'y')->where('deleted_at', null)->get() as $modem) {
            if (Mpr::point_in_polygon([$modem->x, $modem->y], $polygon)) {
                array_push($ids, $modem->id);
            }
        }

        $modems = $this->filterModel(Modem::whereIn('id', $ids));

        return $this->show_topo($modems['selectedModel'], null, $modems['allModels']);
    }

    /**
     * Show all customers in proximity (radius in meters)
     *
     * @author: Ole Ernst
     */
    public function show_prox()
    {
        $modems = $this->filterModel(Modem::whereIn('id', Modem::find(\Input::get('id'))->proximity_search(\Input::get('radius'))));

        return $this->show_topo($modems['selectedModel'], \Input::get('row'), $modems['allModels']);
    }

    /**
     * Show customers with an upstream power of bigger than 50dBmV
     *
     * @author: Ole Ernst
     */
    public function show_impaired()
    {
        $modems = Modem::where('us_pwr', '>', '50');

        // return back if all modems are fine
        if (! $modems->count()) {
            return back();
        }

        $modems = $this->filterModel($modems);

        return $this->show_topo($modems['selectedModel'], null, $modems['allModels']);
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
    public function show_topo($modems, $row = null, $allModels = null)
    {
        if (! $modems->count()) {
            return \View::make('errors.generic')->with('message', 'No Modem Entry found');
        }

        if (! $row) {
            $row = 'us_pwr';
        }

        foreach (isset($allModels) ? $allModels->get() : $modems->get() as $modem) {
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

        // Prepare and Topography Map
        $target = $this->html_target;
        $route_name = 'Tree';
        $view_header = 'Topography - Modems';
        $body_onload = 'init_for_map';
        $tabs = $this->makeTabs($modems);
        $kmls = $this->__kml_to_modems($modems);
        $file = route('HfcCustomer.get_file', ['type' => 'kml', 'filename' => basename($file)]);

        return \View::make('HfcBase::Tree.topo', $this->compact_prep_view(compact('file', 'target', 'route_name', 'view_header', 'body_onload', 'modems', 'tabs', 'kmls', 'models')));
    }

    /**
     * Filter modems in topography.
     * Only show the selcted model.
     *
     * @author Roy Schneider
     * @param Illuminate\Database\Eloquent\Builder $modems
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function filterModel($modems)
    {
        $model = \Input::get('model');

        if ($model == '') {
            return ['selectedModel' => $modems, 'allModels' => null];
        }

        return ['allModels' => clone $modems, 'selectedModel' => $modems->where('model', $model)];
    }

    /*
     * KML Upload Array: Generate the KML file array
     * based on the provided $modems. Show all related
     * kml files which are in relation to a modem cluster.
     *
     * @param modems: modems list, without ->get() call
     * @return array of KML files, like ['file', 'descr']
     *
     * @author: Torsten Schmidt
     */
    private function __kml_to_modems($modems)
    {
        $a = [];

        // foreach modem with a distinct netelement_id
        foreach ($modems->select('netelement_id')->distinct('netelement_id')->get() as $m) { // $m is a modem object
            // if netelement has a valid cluster, push cluster id to $a[]
            if (isset($m->nelelement->cluster)) {
                array_push($a, $m->nelelement->cluster);
            }
        }

        // parse all NetElement's with a cluster id in $a[]
        return $this->kml_file_array(NetElement::whereIn('cluster', $a)->whereNotNull('pos')->where('pos', '!=', ' ')->get());
    }

    /*
    * Show Modems Diagrams
    *
    * TODO: - add cacti graph template id's to ENV
    *
    * @param modems the preselected Modem model, like Modem::where()
    * @return view with modem diagrams
    *
    * @author: Torsten Schmidt
    */
    public function show_diagrams($modems)
    {
        // check if ProvMon is installed
        if (! \Module::collections()->has('ProvMon')) {
            return \View::make('errors.generic')->with('message', 'Module Provisioning Monitoring (ProvMon) not installed');
        }

        $monitoring = [];

        // load a new ProvMon object
        $provmon = new \Modules\ProvMon\Http\Controllers\ProvMonController;

        // Log: prepare time measurement
        $before = microtime(true);

        $types = ['ds_pwr', 'ds_snr', 'us_snr', 'us_pwr'];

        // foreach modem
        foreach ($modems->orderBy('city')->orderBy('street')->orderBy('house_number')->get() as $modem) {
            // load per modem diagrams
            $dia_ids = [$provmon->monitoring_get_graph_template_id('DOCSIS Overview')];
            if (! \Input::has('row')) {
                $dia_ids[] = $provmon->monitoring_get_graph_template_id('DOCSIS US PWR');
            } elseif (in_array(\Input::get('row'), $types)) {
                $dia_ids[] = $provmon->monitoring_get_graph_template_id('DOCSIS '.strtoupper(str_replace('_', ' ', \Input::get('row'))));
            } elseif (\Input::get('row') == 'all') {
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
                $dia['row'] = \Input::has('row') ? \Input::get('row') : 'us_pwr';

                // Add diagrams to monitoring array (goes directly to view)
                $monitoring[$modem->id] = $dia;
            }
        }

        // prepare/load panel right
        $tabs = $this->makeTabs($modems);

        // Log: time measurement
        $after = microtime(true);
        \Log::info('DIA: load of entire set takes '.($after - $before).' s');

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
        if (! is_array($_ids)) {
            $ids = explode('+', $_ids);
        }

        $modems = Modem::whereIn('id', $ids);

        if ($topo == 'true') {
            return $this->show_topo($modems);
        } else {
            return $this->show_diagrams($modems);
        }
    }

    /*
    * Prepare $tabs vaiable for switching topography/diagrams mode
    *
    * @param modems: the preselected Modem model, like Modem::where()
    * @return: prepared $tabs variable
    *
    * @author: Torsten Schmidt
    */
    private function makeTabs($modems)
    {
        $ids = '0';
        foreach ($modems->get() as $modem) {
            $ids .= '+'.$modem->id;
        }

        return [['name' => 'Edit', 'route' => 'NetElement.edit', 'link' => $modem->netelement_id],
                ['name' => 'Topography', 'route' => 'CustomerModem.show', 'link' => ['true', $ids, 'row' => \Input::get('row')]],
                ['name' => 'Diagramms', 'route' => 'CustomerModem.show', 'link' => ['false', $ids, 'row' => \Input::get('row')]], ];
    }

    /**
     * Generate KML File with Customer Modems Inside
     *
     * @param modems the Modem models to display, like Modem::where()
     * @returns the path of the generated *.kml file to be included via asset ()
     *
     * @author: Torsten Schmidt
     */
    public function kml_generate($modems, $row)
    {
        $x = 0;
        $y = 0;
        $num = 0;
        $clrs = [];
        $str = $descr = $city = $zip = $nr = '';
        $states = [-1 => 'offline', 0 => 'okay', 1 => 'impaired', 2 => 'critical'];
        $file = $this->file_pre;

        foreach ($modems->where('contract_id', '>', '0')->orderByRaw('10000000*x+y')->get() as $modem) {
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

            // modem
            $mid = $modem->id;
            $mac = $modem->mac;

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

            //
            // Contract
            //
            $contract = $modem->contract;
            $contractid = $contract->id;
            $lastname = $contract->lastname;

            // Headline: Address from DB
            if ($str != $modem->street || $city != $modem->city || $zip != $modem->zip || $nr != $modem->house_number) {
                $str = $modem->street;
                $city = $modem->city;
                $zip = $modem->zip;
                $nr = $modem->house_number;
                $descr .= "<b>$zip, $city, $str, $nr</b><br>";
            }

            if (ProvBase::first()->modem_edit_page_new_tab) {
                $this->html_target = '_blank';
            }
            // add descr line
            $descr .= '<a target="'.$this->html_target."\" href='".\BaseRoute::get_base_url()."/Modem/$mid'>$mac</a>, $contractid, $lastname, $states[$cur_clr] ($row_val)<br>";
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
