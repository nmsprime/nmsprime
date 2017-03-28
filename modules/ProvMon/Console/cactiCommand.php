<?php

namespace Modules\provmon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Cmts;
use Modules\ProvMon\Http\Controllers\ProvMonController;
use Modules\ProvBase\Entities\ProvBase;

class cactiCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:cacti';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create all missing Cablemodem Diagrams';

	protected $connection = 'mysql-cacti';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/*
	 * DEBUG: use for monitoring km3 Marienberg Modems
	 *        uncomment line in fire() for usage
	 * TODO: delete me :)
	 */
	private function _debug_ip ()
	{
		return '10.42.2.'.rand(2,253);
	}


	/**
	 * Execute the console command. Create all missing Cacti Diagrams
	 *
	 * TODO: delete of unused diagrams
	 *
	 * @return true
	 * @author: Torsten Schmidt
	 */
	public function fire()
	{
		$matches = array();
		$path = '/usr/share/cacti/cli';

		try {
			if(!\Schema::connection($this->connection)->hasTable('host'))
				return false;
		}
		catch (\PDOException $e) {
			// Code 1049 == Unknown database '%s' -> cacti is not installed yet
			if($e->getCode() == 1049)
				return false;
			// Don't catch other PDOExceptions
			throw $e;
		}

		$modems = $this->option('modem-id') === false ? Modem::all() : Modem::where('id', '=', $this->option('modem-id'))->get();
		foreach ($modems as $modem)
		{
			// Skip all $modem's that already have cacti graphs
			if (ProvMonController::monitoring_get_graph_ids($modem))
				continue;

			// Prepare VARs
			$name      = $modem->hostname;
			$hostname  = $modem->hostname.'.'.ProvBase::first()->domain_name;
			// DEBUG: use for monitoring km3 Marienberg Modems
			// $hostname  = $this->_debug_ip();
			$community = ProvBase::first()->ro_community;


			// Assumption: host template and graph tree are named 'cablemodem' (case-insensitive)
			$host_template_id = \DB::connection($this->connection)->table('host_template')
				->where('name', '=', 'cablemodem')
				->select('id')->first()->id;

			$graph_template_ids = \DB::connection($this->connection)->table('host_template_graph')
				->join('host_template', 'host_template_graph.host_template_id', '=', 'host_template.id')
				->where('host_template.name', '=', 'cablemodem')
				->select('host_template_graph.graph_template_id')->get();
			$graph_template_ids = array_flatten(array_map(function($item) { return get_object_vars($item); }, $graph_template_ids));

			$tree_id = \DB::connection($this->connection)->table('graph_tree')
				->where('name', '=', 'cablemodem')
				->select('id')->first()->id;

			$out = system("php -q $path/add_device.php --description=$name --ip=$hostname --template=$host_template_id --community=$community --avail=snmp --version=2");
			preg_match('/^Success - new device-id: \(([0-9]+)\)$/', $out, $matches);
			if(count($matches) != 2)
				continue;

			// add host to cabelmodem tree
			system("php -q $path/add_tree.php --type=node --node-type=host --tree-id=$tree_id --host-id=$matches[1]");

			// create all graphs belonging to host template cablemodem
			foreach ($graph_template_ids as $id)
				system("php -q $path/add_graphs.php --host-id=$matches[1] --graph-type=cg --graph-template-id=$id");

			// Info Message
			echo "\ncacti: create diagrams for Modem: $name";
			\Log::info("cacti: create diagrams for Modem: $name");
		}

		$cmtss = $this->option('cmts-id') === false ? Cmts::all() : Cmts::where('id', '=', $this->option('cmts-id'))->get();
		foreach ($cmtss as $cmts)
		{
			// Skip all $cmts's that already have cacti graphs
			if (ProvMonController::monitoring_get_graph_ids($cmts))
				continue;

			$name      = $cmts->hostname;
			$hostname  = $cmts->ip;
			$community = $cmts->get_ro_community();

			// Assumption: host template and graph tree are named e.g. '$company cmts' (case-insensitive)
			$host_template = \DB::connection($this->connection)->table('host_template')
				->where('name', '=', $cmts->company.' cmts')
				->select('id')->first();
			// we don't have a template for the company, skip adding the cmts
			if(!$host_template)
				continue;

			$tree_id = \DB::connection($this->connection)->table('graph_tree')
				->where('name', '=', 'cmts')
				->select('id')->first()->id;

			$out = system("php -q $path/add_device.php --description=\"$name\" --ip=$hostname --template=$host_template->id --community=\"$community\" --avail=snmp --version=2");
			preg_match('/^Success - new device-id: \(([0-9]+)\)$/', $out, $matches);
			if(count($matches) != 2)
				continue;

			// add host to cmts tree
			system("php -q $path/add_tree.php --type=node --node-type=host --tree-id=$tree_id --host-id=$matches[1]");
		}

		echo "\n";
		return true;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('cmts-id', null, InputOption::VALUE_OPTIONAL, 'only consider modem identified by its id, otherwise all', false),
			array('modem-id', null, InputOption::VALUE_OPTIONAL, 'only consider cmts identified by its id, otherwise all', false),
		);
	}

}
