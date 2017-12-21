<?php namespace Modules\Ccc\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ForwardUpdate extends Command {

	// required tables to copy
	protected $tables = 'contract sepamandate item';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'otc:forward';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'forward update: hard copy required tables from DB:nmsprime to DB:nmsprime_ccc (!dev/unstable!)';

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
	public function fire()
	{
		$u1 = \Config::get('database.connections.mysql.username');
		$p1 = \Config::get('database.connections.mysql.password');

		$u2 = \Config::get('database.connections.mysql-ccc.username');
		$p2 = \Config::get('database.connections.mysql-ccc.password');


		$sql = 'mysqldump --opt -u '.$u1.' --password='.$p1.' nmsprime '.$this->tables.' | mysql -u '.$u2.' --password='.$p2.' nmsprime_ccc';
		exec($sql);
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
