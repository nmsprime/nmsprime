<?php

namespace Modules\Ccc\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Contract;

class CreateConnectionInformations extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ccc:connInfo';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a list of connection informations as a single concatenated PDF';

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
		$contracts1 = $contracts2 = $contracts = $ids = $values = $files = [];

		// get list of contracts
		if ($this->option('file'))
		{
			$content = file_get_contents($this->option('file'));
			$content = str_replace(["\n\r", "\n", "\r",], ',', $content);
			$values  = explode(',', $content);
		}

		if ($this->option('list'))
			$values = explode(',', $this->option('list'));

		foreach ($values as $v)
			$ids[] = trim($v);

		$contracts1 = Contract::whereIn('id', $ids, 'or')->get()->all();

		if ($this->option('after'))
			$contracts2 = Contract::where('created_at', '>', $this->option('after'))->get()->all();

		$contracts = array_merge($contracts1, $contracts2);


		// Create PDF
		$controller = new \Modules\Ccc\Http\Controllers\CccAuthuserController;

		foreach ($contracts as $c)
			$files[] = '"'.$controller->connection_info_download($c->id, false).'"';

		$files_string = implode(' ', $files);

		$dir_path = storage_path('app/tmp/');
		$fn 	  = 'connInfos.pdf';

		\Log::debug("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=".$dir_path.$fn." <files>");
		system("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=".$dir_path.$fn." $files_string", $ret);

		// Delete temp files
		foreach ($files as $path) {
			if (is_file($path))
				unlink($path);
		}

		return $dir_path.$fn;

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
			array('after', null, InputOption::VALUE_OPTIONAL, 'Date String - all Contracts that where add after a specific Date, e.g. 2017-07-21', null),
			array('file', null, InputOption::VALUE_OPTIONAL, 'File with Contract IDs - separated by newline and/or comma', []),
			array('list', null, InputOption::VALUE_OPTIONAL, 'Comma separated list of Contract IDs, e.g. 500034, 512456,634612', []),
		);
	}

}
