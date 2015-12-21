<?php

namespace Modules\provmon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Modem;
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
		foreach (Modem::all() as $modem)
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

			// Run Artisan Command for adding a cacti host
			$cmd = base_path()."/modules/ProvMon/Console/cacti_add.sh $name $hostname $community";
			$result_short = exec($cmd, $result);
			$result = implode("\n", $result);

			// Info Message
			echo "\ncacti: create diagrams for Modem: $name - CMD: $result_short";
			\Log::info("cacti: create diagrams for Modem: $name - CMD: $result");
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
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
