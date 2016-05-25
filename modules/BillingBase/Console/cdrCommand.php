<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use \Chumper\Zipper\Zipper;
use Storage;
use Modules\BillingBase\Entities\BillingLogger;

class cdrCommand extends Command {

	/**
	 * The console command & table name
	 *
	 * @var string
	 */
	protected $name 		= 'billing:cdr';
	protected $description 	= 'Get Call Data Record from Envia';
	protected $signature 	= 'billing:cdr {month? : 1 (Jan) to 12 (Dec)}';


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
	 * Execute the console command
	 *
	 * TODO: create array of URLs (files with BICs) and store each file as CSV
	 */
	public function fire()
	{
		$https_user = 'erznet_Reseller';
		$https_password = 'password';
		// $https_user = $_ENV['PROVVOIPENVIA__RESELLER_USERNAME'];
		// $https_password = $_ENV['PROVVOIPENVIA__RESELLER_PASSWORD'];
		$logger = new BillingLogger;

		$month = $this->argument('month') >= 1 && $this->argument('month') <= 12 ? sprintf('%02d', $this->argument('month')) : date('m', strtotime('-2 months'));
		//TODO: remove following line - only during testing
		$month = '04';

		$file 	  = 'cdr.zip';
		$tmp_path = storage_path('app/tmp/');

		$data = file_get_contents("https://$https_user:$https_password@www.enviatel.de/portal/vertrieb2/reseller/evn/K8000002961/2016/$month");
		if (!$data)
		{
			$logger->addAlert('Could not get Call Data Records from Envia for month: '.$month);
			return -1;
		}

		Storage::put("tmp/$file", $data);

		$zipper = new Zipper;
		//TODO: remove following line - only during testing
		$month = '03';
		$target_dir = storage_path('app/data/billingbase/accounting/'.date("Y-".sprintf('%02d', $month + 2)).'/');

		if (!is_dir($target_dir))
			mkdir($target_dir, '0744', true);

		$zipper->make($tmp_path.$file)->extractTo($target_dir);

		Storage::delete('tmp/cdr.zip');
	}



	/**
	 * Get the console command arguments / options
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		// return [
		// 	['month', InputArgument::OPTIONAL, '1 (Jan) to 12 (Dec)'],
		// ];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}