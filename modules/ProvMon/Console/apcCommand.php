<?php

namespace Modules\provmon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Cmts;
use Modules\ProvMon\Http\Controllers\ProvMonController;
use Modules\ProvBase\Entities\ProvBase;

class apcCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:apc';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Execute Automatic Power Control based on measured SNR';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command. Automatically adjust power level of all CMTS
	 *
	 * @return true
	 * @author: Ole Ernst
	 */
	public function fire()
	{
		foreach (Cmts::all() as $cmts)
		{
			$ctrl = new ProvMonController();
			$ctrl->realtime_cmts($cmts, ProvBase::first()->ro_community, true);
			unset($ctrl);
		}
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
