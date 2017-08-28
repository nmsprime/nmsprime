<?php

namespace Modules\BillingBase\Entities;
use Storage;

class Company extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'company';

    // All HTML Input Fields that are discarded during Database Update
    public $guarded = ['logo_upload', 'conn_info_template_fn_upload'];


	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'name' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
			'name' 		=> 'required',
			'street' 	=> 'required',
			'zip'	 	=> 'required',
			'city'	 	=> 'required',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Company';
	}

	// View Icon
	public static function view_icon()
	{
		return '<i class="fa fa-industry"></i>';
	}

	// link title in index view
	public function view_index_label()
	{
		return ['index' => [$this->name, $this->city, $this->phone, $this->mail],
				'index_header' => ['Name', 'City', 'Phonenumber', 'Mail'],
				'header' => $this->name];
	}

	public function view_has_many ()
	{
		return ['SepaAccount' => $this->accounts];
	}

	/**
	 * Relationships:
	 */

	public function accounts ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\SepaAccount');
	}


	/*
	 * Init Observers
	 */
	public static function boot()
	{
		// Company::observe(new CompanyObserver);
		parent::boot();
	}



	/**
	 * Prepare data array with keys replaced by values in tex templates for pdf creation
	 *
	 * @return array
	 *
	 * @author Nino Ryschawy
	 */
	public function template_data()
	{
		$class  = 'company';
		$ignore = ['created_at', 'updated_at', 'deleted_at', 'id'];
		$data 	= [];

		foreach ($this->attributes as $key => $value)
		{
			if (in_array($key, $ignore))
				continue;

			// separate comma separated values by linebreakings
			if (in_array($key, ['management', 'directorate']))
			{
				$value = explode(',', $value);
				$tmp = [];

				foreach ($value as $name)
					$tmp[] = trim($name);
				
				$data[$class.'_'.$key] = implode('\\\\', $tmp);

				continue;
			}

			$data[$class.'_'.$key] = $value;
		}

		return $data;
	}


}
