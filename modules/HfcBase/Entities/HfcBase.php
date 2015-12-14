<?php

namespace Modules\HfcBase\Entities;

class HfcBaseModel extends \BaseModel {

	// The associated SQL table for this Model
	protected $table = 'hfcbase';

	// Don't forget to fill this array
	protected $fillable = ['name', 'type', 'ip', 'pos', 'link', 'state', 'options', 'descr', 'parent', 'access', 'net', 'cluster', 'layer', 'kml_file'];

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' => 'required|string',
			'ip' => 'ip',
			'pos' => 'geopos'
		);
	}
	
	// Name of View
	public static function get_view_header()
	{
		return 'Hfc Base Config';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return "";
	}	


}