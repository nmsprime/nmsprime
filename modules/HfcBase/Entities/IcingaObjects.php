<?php namespace Modules\HfcBase\Entities;

class IcingaObjects extends \BaseModel {

	// SQL connection
	protected $connection = 'mysql-icinga2';
	// The associated SQL table for this Model
	public $table = 'icinga_objects';

	static public function db_exists()
	{
		try {
			$ret = \Schema::connection('mysql-icinga2')->hasTable('icinga_objects');
		}
		catch (\PDOException $e) {
			// Code 1049 == Unknown database '%s'
			if($e->getCode() == 1049)
				return false;
			// Don't catch other PDOExceptions
			throw $e;
		}

		return $ret;
	}

	public function icingahoststatus()
	{
		return $this->hasOne('Modules\HfcBase\Entities\IcingaHoststatus', 'host_object_id',  'object_id');
	}

}
