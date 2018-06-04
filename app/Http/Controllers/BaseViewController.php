<?php

namespace App\Http\Controllers;

use App, Auth, BaseModel, Config, File, GlobalConfig, Input, Log, Module, Redirect, Route, Validator, View ;

/*
 * BaseViewController: Is a special Controller which will be a kind of middleware/sub-layer/helper
 *                     between the classical Controllers and Views.

 * Purpose: This Controller is manly used to reduce the logical hard code php stuff from generic views
 *          and to bring the view related stuff from BaseController to a better place - BaseViewController.
 *          This leads to a kind of theoretical sub-layer concept – in fact it is not! See later ..
 *
 * At the time it is not a full qualified sub-layer (API) in this manner that all stuff goes through this
 * Controller, whats between all Controllers and Views. It's more a kind of "Helper" to increase the logical
 * sructuring. This has the advantage that we do not need a complete re-write.

 * Usage: Most of the function here are used in a simple static context from BaseController like
 *        BaseViewController::do_prepare_view_xyz().
 *
 * @author: Torsten Schmidt
 */
class BaseViewController extends Controller {

	/**
	 * Searches for a string in the language files under resources/lang/ and returns it for the active application language
	 * Searches for a "*" (required field), deletes it for trans function and appends it at the end
	 * used in everything Form related (Labels, descriptions)
	 * @author Nino Ryschawy, Christian Schramm
	 */
	public static function translate_label($string)
	{
		if (App::getLocale() == 'en')
			return $string;

		// cut the star at the end of value if there is one for the translate function and append it after translation
		$star = '';
		if (strpos($string, '*'))
		{
			$string = str_replace(' *', '', $string);
			$star = ' *';
		}

		if (strpos($string, 'messages.') !== false)
			return trans($string).$star;

		$translation = trans("messages.$string");

		// found in lang/{}/messages.php
		if (strpos($translation, 'messages.') === false)
			return $translation.$star;

		return $string.$star;
	}

	/**
	 * Searches for a string in the language files under resources/lang/ and returns it for the active application language
	 * used in everything view related
	 * @param string: 	string that is searched in resspurces/lang/{App-language}/view.php
	 * @param type: 	can be Header, Menu, Button, jQuery, Search
	 * @param count: 	standard at 1 , For plural translation - needs to be seperated with pipe "|""
	 *					example: Index Headers -> in view.php: 'Header_Mta'	=> 'MTA|MTAs',
	 * @author Christian Schramm
	 */
	public static function translate_view($string, $type, $count = 1)
	{
		if (strpos($string, 'view.'.$type.'_'))
			return trans($string);

		$translation = trans_choice('view.'.$type.'_'.$string, $count);

		// found in lang/{}/messages.php
		if (strpos($translation, 'view.'.$type.'_') === false)
			return $translation;

		return $string;
	}


	// TODO: take language from user setting or the language with highest priority from browser
	// @Nino Ryschawy
	public static function get_user_lang()
	{
		$user = Auth::user();

		$language = $user ? $user->language : 'browser';

		if ($language == 'browser')
		{
			// default
			if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
				return 'en';

			$languages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if (strpos($languages[0], 'de') !== false)
				return 'de';
			else
				return 'en';
		}
		return $language;
	}


	/**
	 * This function is used to prepare the resulting view_form_field array for edit view.
	 * So all general preparation stuff to view_form_fields() will be done here.
	 *
	 * Tasks:
	 *  1. Add a (*) to fields description if validation rule contains required
	 *  2. Add Placeholder YYYY-MM-DD for all date fields
	 *  3. Hide all parent view relation select fields (works only in edit context)
	 *  4. auto-fill field_value with correlating model data (from sql)
	 *  5. IP online check for form_type = 'ip' || 'ping'
	 *
	 * @param fields: the view_form_fields array()
	 * @param model: the model to view. Note: could be get_model_obj()->find($id) or get_model_obj()
	 * @return: the modifeyed view_form_fields array()
	 *
	 * @author: Torsten Schmidt
	 */
	public static function prepare_form_fields($fields, $model)
	{
		$ret = [];

		// get the validation rules for related model object
		$rules = $model->rules();
		$view_belongs_to = $model->view_belongs_to();

		// for all fields
		foreach ($fields as $field)
		{
			// rule exists for actual field ?
			if (isset ($rules[$field['name']]))
			{
				// 1. Add a (*) to fields description if validation rule contains required
				if (preg_match('/(.*?)required(.*?)/', $rules[$field['name']]))
					$field['description'] = $field['description']. ' *';

				// 2. Add Placeholder YYYY-MM-DD for all date fields
				if (preg_match('/(.*?)date(.*?)/', $rules[$field['name']]))
					$field['options']['placeholder'] = 'YYYY-MM-DD';

			}

			// 3. Hide all parent view relation select fields (in edit context)
			//    NOTE: this will not work in create context, because view_belongs_to() returns null !
			//          Hiding in create context will only work with hard coded 'hidden' => 1 entry in view_form_fields()
			if (
				// does a view relation exists?
				(is_object($view_belongs_to))
				&&
				// not a n:m relation (in which case we have an pivot table)
				(!($view_belongs_to instanceof \Illuminate\Support\Collection))
				&&
				// view table name (*_id) == field name ?
				($view_belongs_to->table.'_id' == $field['name'])
				&&
				// hidden was not explicitly set
				(!isset($field['hidden']))
			) {
				$field['hidden'] = '1';
			}

			// 4. set all field_value's to actual SQL data
			if (array_key_exists('eval', $field)) {
				// dont't remove $name, as it is used in $field['eval'] (might be set in view_form_fields())
				$name = $model[$field['name']];
				$eval = $field['eval'];
				$field['field_value'] = eval("return $eval;");
			} else
				$field['field_value'] = $model[$field['name']];

			// NOTE: Input::get should actually include $_POST global var and $_GET!!
			// 4.(sub-task) auto-fill all field_value's with HTML Input
			if (\Input::get($field['name']))
				$field['field_value'] = \Input::get($field['name']);

			// 4.(sub-task) auto-fill all field_value's with HTML POST array if supposed
			if (isset($_POST[$field['name']]))
				$field['field_value'] = $_POST[$field['name']];

			// 4. (sub-task)
			// write explicitly given init_value to field_value
			// this is needed e.g. by Patrick to prefill new PhonenumberManagement and PhonebookEntry with data from Contract
			if (array_key_exists('init_value', $field) && $field['init_value']) {
				$field['field_value'] = $field['init_value'];
			}

			// 5. ip online check
			if ($field['form_type'] == 'ip' || $field['form_type'] == 'ping')
			{
				// Ping: Only check if ip is online
				if ($model[$field['name']]) {
					// $model[$field['name']] is null e.g. on Cmts/create
					exec ('sudo ping -c1 -i0 -w1 '.$model[$field['name']], $ping, $offline);

					if($offline)
					{
						$field['help'] = 'Device seems to be Offline!';
						$field['help_icon'] = 'fa-exclamation-triangle text-warning';
					}
					else
					{
						$field['help'] = 'Device is Online';
						$field['help_icon'] = 'fa-check-circle-o text-success';
					}
				}
				else {
					// there is no device to ping – so we do not provide info about online status
					$field['help'] = '';
					$field['help_icon'] = '';
				}

				$field['form_type'] = 'text';
			}

			array_push ($ret, $field);
		}

		return $ret;
	}


	/**
	 * Add ['html'] element to each $fields entry
	 *
	 * The html element contains the HTML formated code to display each HTML field.
	 * You could use 'html' parameter inside the view_form_fields() functions to
	 * overwrite default behavior. The best advice to use these parameter is to
	 * debug the return array of this function and adapt it to you requirements.
	 *
	 * @param fields: the prepared view_form_fields array(), each array element represents on (HTML) field
	 * @param context: edit|create - context from which this function is called
	 * @return: array() of fields with added ['html'] element containing the preformed html content
	 *
	 * @author: Torsten Schmidt
	 *
	 * TODO: split prepare form fields and this compute form fields function -> rename this to "make_html()" or sth more appropriate
	 */
	public static function add_html_string($fields, $context = 'edit')
	{
		// init
		$ret = [];

		// background color's to toggle through
		$color_array = ['whitesmoke', 'gainsboro'];
		$color = $color_array[0];

		// foreach fields
		foreach ($fields as $field)
		{
			$s = '';

			// ignore fields with 'html' parameter
			if (isset($field['html']))
			{
				array_push($ret, $field);
				continue;
			}

			// hidden stuff
			if (array_key_exists('hidden', $field))
			{
				$hidden = $field['hidden'];

				if ($hidden =! 0 && ( // == 0 -> explicitly set to always show, no matter if other conditions are met
					($context == 'edit' && strpos($hidden, 'E') !== false) || // hide edit context only?
					($context == 'create' && strpos($hidden, 'C') !== false) || // hide create context only?
					$hidden == 1) // hide globally?
				)
					{
						// For hidden fields it's also important that default values are set
						$value = ($field['field_value'] === null) && isset($field['value']) ? $field['value'] : $field['field_value'];
						// Note: setting a selection by giving an array doesnt make sense as you can not choose anyway - it also would throw an exception as it's not allowed for hidden fields
						$s .= \Form::hidden ($field["name"], is_array($value) ? '' : $value);
						goto finish;
					}
			}


			// prepare value and options vars
			$value   = isset($field["value"]) ? $field["value"] : [];
			$options = isset($field["options"]) ? $field["options"] : [];

			// field color
			if(!isset($options['style']))
				$options['style'] = '';
			$options['style'] .= $options['style'] == 'simple' ? '' : "background-color:$color";

			// Help: add help msg to form fields - mouse on hover
			if (isset($field['help']))
				$options["title"] = $field['help'];

			// select field: used for jquery (java script) realtime based showing/hiding of fields
			$select = null;
			if (isset($field['select']) && is_string($field['select']))
				$select = ['class' => $field['select']];

			// checkbox field: used for jquery (java script) realtime based showing/hiding of fields
			$checkbox = null;
			if (isset($field['checkbox']) &&  is_string($field['checkbox'])) {
				$checkbox = ['class' => $field['checkbox']];
			}

			// combine the classes to trigger show/hide from select and checkbox
			// the result is either null or an array containing the classes in key 'class'
			$additional_classes = $select;
			if (is_array($additional_classes)) {
				if (is_array($checkbox)) {
					$additional_classes['class'] .= " ".$checkbox['class'];
				}
			}
			else {
				$additional_classes = $checkbox;
			}

			// handle collapsible classes
			if (isset($options['class']) && $options['class'] == 'collapse')
			{
				$additional_classes['class'] = ' collapse';

				// TODO: add the collapse button
				// $s .= "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"collapse\" data-target=\"#number2\">+</button>";
			}

			// Open Form Group
			$s .= \Form::openGroup($field["name"], $field["description"], $additional_classes, $color);

			// Output the Form Elements
			switch ($field["form_type"])
			{
				case 'checkbox' :
					// Checkbox - where pre-checked is enabled
					if ($value == [])
						$value = 1;

					if ($context == 'create')
						// only take care of checked statement if we are called in context create
						$checked = (isset($field['checked'])) ? $field['checked'] : $field['field_value'];
					else
						$checked = $field['field_value'];

					$s .= \Form::checkbox($field['name'], $value, null, $checked, $options);
					break;

				case 'file':
					$s .= \Form::file($field['name'], $options);
					break;

				case 'select' :
					if (isset($options['multiple']) && isset($field['selected']))
						$field['field_value'] = array_keys($field['selected']);

					$s .= \Form::select($field["name"], $value, $field['field_value'], $options);
					break;

				case 'password' :
					$s .= \Form::password($field['name']);
					break;

				case 'link':
					$s .= \Form::link($field['name'], $field['url'], isset($field['color']) ? : 'default');
					break;

				default:
					$form = $field["form_type"];
					$s .= \Form::$form($field["name"], $field['field_value'], $options);
					break;
			}

			// Help: add help icon/image behind form field
			if (isset($field['help']))
				$s .= '<div name='.$field['name'].'-help class="col-1"><a data-toggle="popover" data-container="body"
							data-trigger="hover" title="'. BaseViewController::translate_label($field['description']) .'" data-placement="right" data-content="'.$field['help'].'">'.
							'<i class="fa fa-2x text-info p-t-5 '.(isset($field['help_icon']) ? $field['help_icon'] : 'fa-question-circle').'"></i></a></div>';

			// Close Form Group
			$s .= \Form::closeGroup();



finish:
			// Space Element between fields and color switching
			if (array_key_exists('space', $field))
			{
				$s .= "<div class=col-md-12><br></div>";
				$color_array = \Acme\php\ArrayHelper::array_rotate ($color_array);
				$color = $color_array[0];
			}

			// add ['html'] parameter
			$add = $field;
			$add['html'] = $s;
			array_push($ret, $add);
		}

		return $ret;
	}


	/**
	 * Add simple html input String to the Fields-Array  -- without label - for use in HTML Tables
	 */
	public static function get_html_input($field)
	{
		$s = '';

		$value   = isset($field["value"]) ? $field["value"] : [];
		$options = isset($field["options"]) ? $field["options"] : [];

		// \Form::set_layout(['label' => 5, 'form' => 6]);
		// d(\Form::get_layout());

		switch ($field["form_type"])
		{
			case 'checkbox' :
				// Checkbox - where pre-checked is enabled
				if ($value == [])
					$value = 1;

				$s .= \Form::checkbox($field['name'], $value, null, $field['field_value']);
				break;

			case 'select' :
				$s .= \Form::select($field["name"], $value, $field['field_value'], $options);
				break;

			case 'password' :
				$s .= \Form::password($field['name']);
				break;

			case 'link':
				$s .= \Form::link($field['name'], $field['url'], isset($field['color']) ? : 'default');
				break;

			default:
				if (in_array('readonly', $options))
					return '<p name="'.$field['name'].'">'. $field['field_value'] .'</p>';

				$form = $field["form_type"];
				$s .= \Form::$form($field["name"], $field['field_value'], $options);
				break;
		}

		return $s;
	}


	/*
	 * Return the global prepared header links for Main Menu and provide Symbols for Modules
	 *
	 * NOTE: this function must take care of installed modules!
	 *
	 * @return: array() of header links, like
	 * ['module name' => ['icon' => '...' ,'submodule' => [ 'name of submodule' => ['link' => 'route.entry', 'icon' => '...'], ... ] ...]
	 *
	 * @author: Torsten Schmidt, Christian Schramm
	 */
	public static function view_main_menus ()
	{
		$ret = array();
		$modules = \Module::enabled();

		// global page
		$array = include(app_path().'/Config/header.php');
		foreach ($array as $lines)
		{
			// array_push($ret, $lines);
			foreach ($lines as $k => $line)
			{
				if (\Auth::user()->has_permissions(app()->getNamespace(), substr($line['link'], 0, -6))) {
					$key = \App\Http\Controllers\BaseViewController::translate_view($k, 'Menu');
					$ret['Global']['icon'] = 'fa-globe';
					$ret['Global']['submenu'][$key] = $line;
				}
			}
		}

		// foreach module
		foreach ($modules as $module)
		{
			if (File::exists($module->getPath().'/Config/header.php'))
			{
				/*
				 * TODO: use Config::get()
				 *       this needs to fix namespace problems first
				 */
				$name = ($module->get('description') == '' ? $module->name : $module->get('description')); // module name
				$icon = ($module->get('icon') == '' ? '' : $module->get('icon'));
				$ret[$name]['icon'] = $icon;

				$array = include ($module->getPath().'/Config/header.php');
				foreach ($array as $lines)
				{
					foreach ($lines as $k => $line)
					{

						if (\Auth::user()->has_permissions($module->name, substr($line['link'], 0, -6))) {
							$key = \App\Http\Controllers\BaseViewController::translate_view($k, 'Menu');
							$ret[$name]['submenu'][$key] = $line;
						}
					}
				}
			}
		}

		// cleanup menu
		foreach ($ret as $menu_name => $entries) {
			if (count($entries) == 0) {
				unset($ret[$menu_name]);
			}
		}

		return $ret;
	}


	/**
	 * This is a local helper to be able to show HTML code (like images) in breadcrumb
	 * @author: Torsten Schmidt
	 * @todo: move to a generic helper class
	 */
	private static function __link_route_html ($name, $title = null, $parameters = [], $attributes = [])
	{
		return \HTML::decode(\HTML::linkRoute($name, $title, $parameters, $attributes));
	}


	/**
	 * Get the ICON of the class or object or from actual context
	 * @param $class_or_obj: the class or object to look for the icon
	 * @return the HTML icon (with HTML tags)
	 * @author: Torsten Schmidt
	 */
	public static function __get_view_icon ($class_or_obj)
	{
		// NOTE: this does the trick: fetch the image when no object
		//       is present, like on create page
		$class = \NamespaceController::get_model_name();

		if (is_object($class_or_obj))
			$class = get_class ($class_or_obj);

		if (class_exists($class_or_obj))
			$class = $class_or_obj;

		return $class::view_icon();
	}


	/**
	 * Generate Top Header Link (like e.g. Contract > Modem > Mta > ..)
	 * Shows the html links of the related objects recursively
	 *
	 * @param $route_name: route name of actual controller
	 * @param $view_header: the view header name
	 * @param $view_var: the object to generate the link from
	 * @param $html: the HTML GET array. See note bellow!
	 * @return the HTML link line to be directly included in blade
	 * @author Torsten Schmidt, Patrick Reichel
	 *
	 * NOTE: in create context we are forced to work with HTML GET array in $html.
	 *       The first request will also work with POST array, but if validation fails
	 *       there is no longer any POST array we can work with. Note that POST array is
	 *       generated in relation.blade.
	 *
	 *       To avoid this we must ensure that every relational create send it's correlating
	 *       model key, like contract_id=xyz in HTML GET request.
	 */
	public static function compute_headline ($route_name, $view_header, $view_var, $html = null)
	{
		$breadcrumb_path = "";
		$breadcrumb_paths = [];

		// only for create context: parse headline from HTML POST context array
		if (!is_null($html) && isset(array_keys($html)[0]))
		{
			$key        = array_keys($html)[0];
			$class_name = BaseModel::_guess_model_name(ucwords(explode ('_id', $key)[0]));

			if (class_exists($class_name))
			{
				$class      = new $class_name;
				$view_var   = $class->find($html[$key]);
			}
		}

		// lambda function to extend the current breadcrumb by its predecessor
		// code within this function originally written by Torsten
		$extend_breadcrumb_path = function($breadcrumb_path, $model, $i) {

			// following is the original source code written by Torsten
			$tmp = explode('\\',get_class($model));
			$view = end($tmp);

			// get header field name
			// NOTE: for historical reasons check if this is a array or a plain string
			// See: Confluence API  - get_view_headline()
			$name = static::__get_view_icon($model);
			if(is_array($model->view_index_label()))
				$name .= $model->view_index_label()['header'];
			else
				$name .= $model->view_index_label();

			if ($i == 0)
				$breadcrumb_path = "<li class='nav-tabs'>".static::__link_route_html($view.'.edit', BaseViewController::translate_view($name, 'Header'), $model->id).$breadcrumb_path."</li>";
			else
				$breadcrumb_path = "<li>".static::__link_route_html($view.'.edit', BaseViewController::translate_view($name, 'Header'), $model->id)."</li>".$breadcrumb_path;

			return $breadcrumb_path;
		};


		if ($view_var != null) {

			// Recursively parse all relations from view_var
			$parent = $view_var;
			$i = 0;
			while ($parent)	{

				if (
					// if $parent is not a Collection we have a 1:1 or 1:n relation
					(!($parent instanceof \Illuminate\Support\Collection))
					||
					// there is a potential n:m relation, but only one model is really connected
					($parent->count() == 1)
				) {
					// this means we have an explicit next step in our breadcrumb path

					// if we got a collection we first have to extract the model
					if ($parent instanceof \Illuminate\Support\Collection) {
						$parent = $parent->pop();
					}

					// add the current model to breadcrumbs
					$breadcrumb_path = $extend_breadcrumb_path($breadcrumb_path, $parent, $i);

					// get view parent
					$parent = $parent->view_belongs_to();
					$i++;
				}
				else {
					// $parent is a collection with more than one entry – this means we have a multiple parents
					// for example Phonenumber:EnviaOrder is such a n:m relatio
					// we show breadcrumb paths for all of them, but then stopping further processing
					// to avoid shredding the layout
					$breadcrumb_path_before_split = $breadcrumb_path;
					$multicrumbs = '';

					foreach ($parent as $p) {

						// get the breadcrumb for the current parent
						$extended_path  = $extend_breadcrumb_path($breadcrumb_path, $p, $i);
						$breadcrumb = str_replace($breadcrumb_path_before_split, '', $extended_path);

						// overwrite style (default needs to much space on page)
						$new_style = [
							'font-size: 80%',
							'display: block !important',
							'padding-top: 0px !important',
							'padding-bottom: 1px !important',
						];
						$new_style = implode('; ', $new_style);
						$breadcrumb = str_replace('<a ', '<a style="'.$new_style.'" ', $breadcrumb);

						// collect all parent breadcrumbs
						if (!$multicrumbs) {

							// the first one is simple :-)
							$multicrumbs = $breadcrumb;
						}
						else {

							// insert the current breadcrumb into the existing <li> element

							// therefore we extract all text from the first opening to the last closing <a> tag
							// from the current breadcrumb
							$pattern_a_tag = '#\<a .*\</a\>#';
							preg_match($pattern_a_tag, $breadcrumb, $matches);
							$stripped_breadcrumb = $matches[0];

							// then we extract the same pattern from the collected breadcrumbs
							// and replace it by itself (backreference) extended by our cleaned current breadcrumb
							$replacement = '$0'.$stripped_breadcrumb;
							$multicrumbs = preg_replace($pattern_a_tag, $replacement, $multicrumbs);
						}
					}

					// add all the parent breadcrumbs as a single breadcrumb path element
					array_push($breadcrumb_paths, $multicrumbs.$breadcrumb_path_before_split);

					// don't add more predecessors as this can really shred the layout
					// (think about cascading n:m relations…)
					break;
				}
			}
		}

		// Base Link to Index Table in front of all relations
		// if (in_array($route_name, BaseController::get_config_modules()))	// parse: Global Config requires own link
		// 	$s = \HTML::linkRoute('Config.index', BaseViewController::translate_view('Global Configurations', 'Header')).': '.$s;
		// else if (Route::has($route_name.'.index'))
		// 	$s = \HTML::linkRoute($route_name.'.index', $route_name).': '.$s;
		if (in_array($route_name, BaseController::get_config_modules())) {	// parse: Global Config requires own link
			$breadcrumb_path_base = "<li class='active'>".static::__link_route_html('Config.index', static::__get_view_icon($view_var).BaseViewController::translate_view('Global Configurations', 'Header'))."</li>";
		}
		else {
			$breadcrumb_path_base = Route::has($route_name.'.index') ? '<li class="active">'.static::__link_route_html($route_name.'.index', static::__get_view_icon($view_var).$view_header)."</li>" : '';
		}

		if (!$breadcrumb_paths) {	// if this array is still empty: put the one and only breadcrumb path in this array
			array_push($breadcrumb_paths, $breadcrumb_path_base.$breadcrumb_path);
		}
		else {	// multiple breadcrumb paths: show overture on a single line
			array_unshift($breadcrumb_paths, $breadcrumb_path_base);
		}

		return implode('', $breadcrumb_paths);
	}



	/*
	 * Return the API Version of view_has_many() as normal incremental integer
	 *
	 * @param view_has_many_array: the returned view_has_many() array
	 * @return: api version starting from 1, 2, ..
	 *
	 * @autor: Torsten Schmidt
	 */
	public static function get_view_has_many_api_version ($view_has_many_array)
	{
		if (\Acme\php\ArrayHelper::array_depth($view_has_many_array) < 2)
			return 1;

		return 2;
	}


	/**
	 * Prepare Right Panels to View
	 *
	 * @param $view_var: object/model to be displayed
	 * @return: array() of fields with added ['html'] element containing the preformed html content
	 *
	 * @author: Torsten Schmidt
	 */
	public static function prep_right_panels ($view_var)
	{
		$arr = $view_var->view_has_many();
		$api = static::get_view_has_many_api_version($arr);

		if ($api == 1)
		{
			$relations = $arr;
		}

		if ($api == 2)
		{
			// API 2: use HTML GET 'blade' to switch between tabs
			// TODO: validate Input blade
			$blade = 0;
			if(Input::get('blade') != '')
				$blade = Input::get('blade');


			// get actual blade to $b from array of all blades in $arr
			// $arr = $view_var->view_has_many();

			if (count($arr) == 1)
				return current($arr);

			$b = current($arr);
			for ($i = 0; $i < $blade; $i++)
				$b = next($arr); // move to next blade/tab

			$relations = $b;
		}

		return ($relations);
	}


	/*
	 * Prepare Index Entry Table (<tr>) Colors
	 *
	 * @param $object: the object to look at
	 * @param $rotate_after: rotate color array after number of entries
	 * @return: bootstrap table index class/color [success|warning|danger|info]
	 *
	 * @autor: Torsten Schmidt
	 */
	public static function prep_index_entries_color($object, $rotate_after = 5)
	{
		static $color_array = ['success', 'warning', 'danger', 'info'];
		static $i;

		$class = current($color_array);

		// Check if class object has a own color definition
		if (isset($object->view_index_label()['bsclass']))
			$class = $object->view_index_label()['bsclass'];
		else
		{
			// Rotate Color through $color_array every $rotate_after entries
			if ($i++ % $rotate_after == 0)
			{
				$color_array = \Acme\php\ArrayHelper::array_rotate($color_array);
				$class = $color_array[0];
			}
		}

		return $class;
	}

	/**
	* Evaluate according to given limits
	*
	* @param val: the value to be evaluated
	* @param limits: array of size 2 or 4, containing the limits
	* @param invert the results (good <--> bad)
	* @return: evaluation results - good(0), average(1) or bad(2)
	*
	* @author: Ole Ernst
	*/
	private static function _colorize($val, $limit, $inv = false)
	{
		if ($val < $limit[0] || (isset($limit[3]) && $val > $limit[3]))
			return $inv ? 0 : 2;

		if ($val >= $limit[1]) {
			if (!isset($limit[2]))
				return $inv ? 2: 0;

			if ($val <= $limit[2])
				return $inv ? 2: 0;
		}

		return 1;
	}

	/**
	* Evaluate if the value is good(0), average(1) or bad(2) in the given context
	*
	* @param dir: downstream, upstream
	* @param entity: the entity to check (power, modulation, ureflections)
	* @param value: array containing all values (can be used for several us/ds channels)
	* @return: array of same size as $value containing evaluation results
	*
	* @author: Ole Ernst
	*/
	public static function get_quality_color($dir, $mod, $entity, $val)
	{
	$ret= "3";

	switch ($entity) {
		case 'rx power dbmv':
			$ret = self::_colorize($val, [-3, -1, 15, 20]);
			break;
		case 'power dbmv':
			if ($dir == 'downstream')
				$ret = self::_colorize($val, [-20, -10, 15, 20]);
			if ($dir == 'upstream')
				$ret = self::_colorize($val, [22, 27, 50, 56]);
				break;
		case 'microreflection -dbc':
			$ret = self::_colorize($val, [20, 30]);
			break;
		case "avg utilization %":
			$ret = self::_colorize($val, [0,0,70,90]);
			break;
		case 'snr db' :
		case 'mer db':
			if ($mod == 'qpsk')
				$ret = self::_colorize($val, [14, 17]);
			if ($mod == '16qam')
				$ret = self::_colorize($val, [20, 23]);
			if ($mod == '32qam')
				$ret = self::_colorize($val, [22, 25]);
			if ($mod == '64qam' || $mod == '0') // no docsIfCmtsModulationTable entry
				$ret = self::_colorize($val, [26, 29]);
			if ($mod == 'qam64')
				$ret = self::_colorize($val, [26, 29]);
			if ($mod == 'qam256')
				$ret = self::_colorize($val, [32, 35]);
				break;
		}

		return $ret;
	}

	public static function get_quality_color_orig($dir, $entity, $values)
	{
		$ret = [];
		if($entity == 'snr' && $dir == 'ds')
			$entity = '256qam';
		if($entity == 'snr' && $dir == 'us')
			$entity = '64qam';

		foreach ($values as $val) {
			switch ($entity) {
			case 'pwr':
				if($dir == 'ds')
					$ret[] = self::_colorize($val, [-20, -10, 15, 20]);
				if($dir == 'us')
					$ret[] = self::_colorize($val, [22, 27, 50, 56]);
				break;
			case 'qpsk':
				$ret[] = self::_colorize($val, [14, 17]);
				break;
			case '16qam':
				$ret[] = self::_colorize($val, [20, 23]);
				break;
			case '32qam':
				$ret[] = self::_colorize($val, [22, 25]);
				break;
			case '64qam':
				$ret[] = self::_colorize($val, [26, 29]);
				break;
			case '256qam':
				$ret[] = self::_colorize($val, [32, 35]);
				break;
			case 'urefl':
				$ret[] = self::_colorize($val, [20, 30]);
				break;
			case 'us':
				if($dir == 'ds')
					$ret[] = self::_colorize($val, [5, 12], true);
				break;
			}
		}

		return $ret;
	}
}
