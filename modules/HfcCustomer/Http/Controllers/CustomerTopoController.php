<?php

namespace Modules\HfcCustomer\Http\Controllers;

use Modules\HfcCustomer\Entities\ModemHelper;
use Modules\HfcReq\Http\Controllers\NetElementController;

use Modules\ProvBase\Entities\Modem;
use App\Http\Controllers\BaseViewController;


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
 *       show_diagrams() call make_right_panel_links() which generates topography and diagram links
 *       to show_modem_ids().
 *
 * @author: Torsten Schmidt
 */
class CustomerTopoController extends NetElementController {

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
				<href>http://maps.gstatic.com/intl/de_de/mapfiles/ms/micons/red-dot.png</href>
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

	private $file_end = "</Document></kml>";


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
		if($field == 'all')
			$s = 'id>2';

		return $this->show_topo(Modem::whereRaw($s), \Input::get('row'));
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
		return $this->show_topo(Modem::whereRaw("(($x1 < x) AND (x < $x2) AND ($y1 < y) AND (y < $y2))"), \Input::get('row'));
	}


	/**
	* Show all customers in proximity (radius in meters)
	*
	* @author: Ole Ernst
	*/
	public function show_prox()
	{
		return $this->show_topo(Modem::whereRaw(Modem::find(\Input::get('id'))->proximity_search(\Input::get('radius'))));
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
	public function show_topo($modems, $row = null)
	{
		if (!$modems->count())
			return \View::make('errors.generic')->with('message', 'No Modem Entry found');
		if (!$row)
			$row = 'us_pwr';

		// Generate SVG file
		$file = $this->kml_generate ($modems, $row);

		if(!$file)
			return \View::make('errors.generic')->with('message', 'Failed to generate SVG file');

		// Prepare and Topography Map
		$target      = $this->html_target;
		$route_name  = 'Tree';
		$view_header = "Topography - Modems";
		$body_onload = 'init_for_map';
		$panel_right = $this->make_right_panel_links($modems);

		return \View::make('hfcbase::Tree.topo', $this->compact_prep_view(compact('file', 'target', 'route_name', 'view_header', 'body_onload', 'modems', 'panel_right')));
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
	public function show_diagrams ($modems)
	{
		// check if ProvMon is installed
		if (!\PPModule::is_active('ProvMon'))
			return \View::make('errors.generic')->with('message', 'Module Provisioning Monitoring (ProvMon) not installed');

		$monitoring = array();

		// load a new ProvMon object
		$provmon = new \Modules\ProvMon\Http\Controllers\ProvMonController;

		// Log: prepare time measurement
		$before = microtime(true);

		// foreach modem
		foreach ($modems->orderBy('city', 'street')->get() as $modem)
		{
			// load per modem diagrams
			$dia_ids = $provmon->monitoring_get_graph_template_id('DOCSIS Overview');
			$dia = $provmon->monitoring($modem, $dia_ids);

			// valid diagram's ?
			if ($dia != false)
			{
				// Description Line per Modem
				$descr = $modem->lastname.' - '.$modem->zip.', '.$modem->city.', '.$modem->street.' '.$modem->house_number.' - '.$modem->mac;
				$dia['descr']  = \HTML::linkRoute('Modem.edit', $descr, $modem->id);

				// Add diagrams to monitoring array (goes directly to view)
				$monitoring[$modem->id] = $dia;
			}
		}

		// prepare/load panel right
		$panel_right = $this->make_right_panel_links($modems);

		// Log: time measurement
		$after = microtime(true);
		\Log::info ('DIA: load of entire set takes '.($after-$before).' s');


		// show view
		return \View::make('hfccustomer::Tree.dias', $this->compact_prep_view(compact('monitoring', 'panel_right')));
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
	public function show_modem_ids ($topo, $_ids)
	{
		if (!is_array ($_ids))
			$ids = explode ('+', $_ids);

		$modems = Modem::whereIn('id', $ids);

		if ($topo == 'true')
			return $this->show_topo($modems);
		else
			return $this->show_diagrams($modems);

	}


	/*
	* Prepare $panel_right vaiable for switching topography/diagrams mode
	*
	* @param modems: the preselected Modem model, like Modem::where()
	* @return: prepared $panel_right variable
	*
	* @author: Torsten Schmidt
	*/
	private function make_right_panel_links ($modems)
	{
		$ids = '0';
		foreach ($modems->get() as $modem)
			$ids .= '+'.$modem->id;

		return [['name' => 'Topography', 'route' => 'CustomerModem.show', 'link' => ['true', $ids, 'row' => \Input::get('row')]],
		        ['name' => 'Diagramms', 'route' => 'CustomerModem.show', 'link' => ['false', $ids, 'row' => \Input::get('row')]]];
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
		$str   = '';
		$descr = '';
		$states = ['okay', 'critical', 'offline'];
		$file  = $this->file_pre;

		foreach ($modems->where('contract_id', '>', '0')->orderByRaw('10000000*x+y')->get() as $modem)
		{
			#
			# Print Marker AND Reset Vars IF new GPS position
			#
			if ($x != $modem->x || $y != $modem->y)
			{
				# Print Marker
				$clr = ($x) ? round(array_sum($clrs)/count($clrs)) : '';
				$style = "#style$clr"; # green, yellow, red

				# Reset Vars
				$clrs = [];
				$pos ="$x, $y, 0.000000";


				if ($x)                  # ignore (0,0)
				{
					$file .= "\n <Placemark><name>1</name>
						 <description><![CDATA[$descr]]></description>
						 <styleUrl>$style</styleUrl>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
					$file .= "\n <Placemark><name>$num</name>
						 <Point><coordinates>$pos</coordinates></Point></Placemark>";
				}

				# Reset Var's
				$state = 3;      # unknown
				$descr = '<br>'; # new line for descr
				$x = $modem->x;  # get next GPS pos ..
				$y = $modem->y;
				$num = 0;
			}


			# modem
			$mid    = $modem->id;
			$mac    = $modem->mac;

			$row_val = $modem->{$row};
			$cur_clr = BaseViewController::get_quality_color_orig(explode('_',$row)[0], explode('_',$row)[1], [$row_val])[0];
			$clrs[] = $cur_clr;

			#
			# Contract
			#
			$contract   = $modem->contract;
			$contractid = $contract->id;
			$lastname   = $contract->lastname;

			# Headline: Address from DB
			if ($str != $modem->street || $city != $modem->city || $zip != $modem->zip)
			{
				$str = $modem->street;
				$city = $modem->city;
				$zip = $modem->zip;
				$descr .= "<b>$zip, $city, $str</b><br>";
			}

			# add descr line
			$descr .= "<a target=\"".$this->html_target."\" href='".\BaseRoute::get_base_url()."/Modem/$mid/edit'>$mac</a>, $contractid, $lastname, $states[$cur_clr] ($row_val)<br>";
			$num += 1;
		}


		#
		# Print Last Marker
		#
		$clr = round(array_sum($clrs)/count($clrs));
		$style = "#style$clr"; # green, yellow, red
		$pos ="$x, $y, 0.000000";
		if ($x)
		{
			$file .= "\n <Placemark><name></name>
				 <description><![CDATA[$descr]]></description>
				 <styleUrl>$style</styleUrl>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
			$file .= "\n <Placemark><name>$num</name>
				 <Point><coordinates>$pos</coordinates></Point></Placemark>";
		}



		# Write Files ..
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
		if (file_exists($path))
			return \Response::file($path);
		else
			return \App::abort(404);
	}

}

