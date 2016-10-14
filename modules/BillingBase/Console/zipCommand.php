<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\BillingBase\Entities\BillingLogger;
use Storage;

class zipCommand extends Command {

	/**
	 * The console command & table name
	 *
	 * @var string
	 */
	protected $name 		= 'billing:zip';
	protected $description 	= 'Build Zip File file with all relevant Accounting Files for one specified Month';
	protected $signature 	= 'billing:zip {year?} {month?}';


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
	 * Find all Files for last or specified month and Package them - stored in appropriate accounting directory
	 * NOTE: this Command is called in accounting Command!
	 *
	 * @author Nino Ryschawy
	 *
	 * TODO: Add more Error checking
	 */
	public function fire()
	{
		$logger = new BillingLogger;

		$year  = $this->argument('year');
		$month = $this->argument('month');

		$time = strtotime('first day of last month');

		if ($year && $month)
			$time = strtotime($year.'-'.$month);

		$filename 	= date('Y_m', $time).'.zip';		
		$dir_abs_path 				= storage_path('app/data/billingbase');
		$dir_abs_path_acc_files 	= $dir_abs_path.'/accounting/'.date('Y-m', $time);
		$dir_abs_path_invoice_files = $dir_abs_path.'/invoice';

		// find all invoices and concatenate them
		$invoices 	= sprintf('%s.pdf', date('Y_m', $time));
		$cdrs 		= sprintf('%s_cdr.pdf', date('Y_m', strtotime('-1 month', $time)));
		$tmp 		= exec("find $dir_abs_path_invoice_files -type f -name $invoices -o -name $cdrs | sort -r", $files, $ret);

		$files = implode(' ', $files);
		$fname = \App\Http\Controllers\BaseViewController::translate_label('Invoices');
		system("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=$dir_abs_path_acc_files/$fname.pdf $files", $ret);

		if ($ret != 0)
			$logger->addError('Could not concatenate invoice files! Missing Ghostscript?');


		// Zip all
		chdir($dir_abs_path_acc_files);
		system("zip -r $filename *");
		system('chmod -R 0700 '.$dir_abs_path_acc_files);
		system('chown -R apache '.$dir_abs_path_acc_files);

	}



	/**
	 * Get the console command arguments / options
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			// ['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
		];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}