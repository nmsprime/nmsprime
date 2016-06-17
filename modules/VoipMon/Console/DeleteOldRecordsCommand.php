<?php namespace Modules\Voipmon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class deleteOldRecordsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'voipmon:delete_old_records';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deletes voipmonitor call monitoring records older than 14 days';

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
	 * Execute the console command.
	 *
	 * @author: Ole Ernst
	 */
	public function fire()
	{
		\DB::table('voipmonitor.cdr')->where('calldate', '<', \DB::raw('DATE_SUB(NOW(), INTERVAL 14 DAY)'))->delete();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
