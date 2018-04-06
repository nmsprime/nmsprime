<?php namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Collective\Bus\Contracts\SelfHandling;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MpsCommand extends Command implements SelfHandling, ShouldQueue {

	use InteractsWithQueue, SerializesModels;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:mps';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Modem Positioning System: refresh all Bubbles based on Mpr, MprGeopos tables';

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
	 * @return mixed
	 */
	public function handle()
	{
		// Refresh all MPS rules
		\Modules\HfcCustomer\Entities\Mpr::refresh();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			// ['example', InputArgument::REQUIRED, 'An example argument.'],
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
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
