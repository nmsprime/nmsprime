<?php namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\HfcReq\Entities\NetElement;
use Modules\HfcCustomer\Entities\ModemHelper;

class MsHelperCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:ms_helper';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get modem summary of all clusters';

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
		$ret = 'OK';
		$perf = '';
		foreach(NetElement::where('id', '>', '2')->get() as $element) {
			$state = ModemHelper::ms_state ("netelement_id = $element->id");
			if ($state == -1)
				continue;
			if ($state == 'WARNING' && $ret == 'OK')
				$ret = $state;
			if ($state == 'CRITICAL')
				$ret = $state;

			$num   = ModemHelper::ms_num("netelement_id = $element->id");
			$numa  = ModemHelper::ms_num_all("netelement_id = $element->id");
			$cm_cri= ModemHelper::ms_cri("netelement_id = $element->id");
			$avg   = ModemHelper::ms_avg("netelement_id = $element->id");
			$warn  = ModemHelper::$avg_warning_percentage / 100 * $numa;
			$crit  = ModemHelper::$avg_critical_percentage / 100 * $numa;

			$perf .= "'$element->name ($avg dBuV, #crit:$cm_cri)'=$num;$warn;$crit;0;$numa ";
		}
		echo $ret . ' | ' . $perf;

		if($ret == 'CRITICAL')
			return 2;
		if($ret == 'WARNING')
			return 1;
		return 0;
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
