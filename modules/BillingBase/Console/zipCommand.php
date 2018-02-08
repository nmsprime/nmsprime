<?php 
namespace Modules\BillingBase\Console;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\Invoice;

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
		$offset = intval(BillingBase::first()->cdr_offset);

		$year  = $this->argument('year');
		$month = $this->argument('month');

		$time = $year && $month ? strtotime($year.'-'.$month) : strtotime('second day last month');
		$cdr_time = $offset ? strtotime("second day -$offset month", $time) : $time;

		\ChannelLog::info('billing', 'Zip accounting files for Month '.date('Y-m', $time));

		$dir_abs_path 				= storage_path('app/data/billingbase');
		$dir_abs_path_acc_files 	= $dir_abs_path.'/accounting/'.date('Y-m', $time);
		// $dir_abs_path_invoice_files = $dir_abs_path.'/invoice';

		// find all invoices and concatenate them
		// NOTE: This probably has to be replaced by DB::table for more than 10k contracts as Eloquent gets too slow then
		$invoices = Invoice::where('type', '=', 'Invoice')
			->where('year', '=', date('Y', $time))->where('month', '=', date('m', $time))
			->orWhere(function ($query) use ($cdr_time) { $query
				->where('type', '=', 'CDR')
				->where('year', '=', date('Y', $cdr_time))->where('month', '=', date('m', $cdr_time));})
			->join('contract as c', 'c.id', '=', 'invoice.contract_id')
			->orderBy('c.number', 'desc')->orderBy('invoice.type')
			->get()->all();

		$files = '';
		foreach ($invoices as $inv)
			$files .= $inv->get_invoice_dir_path().$inv->filename.' ';

		// $invoices 	= sprintf('%s.pdf', date('Y_m', $time));
		// $cdrs 		= sprintf('%s_cdr.pdf', date('Y_m', $cdr_time));
		// $tmp 		= exec("find $dir_abs_path_invoice_files -type f -name $invoices -o -name $cdrs | sort -r", $files, $ret);
		// $files = implode(' ', $files);

		$fname = \App\Http\Controllers\BaseViewController::translate_label('Invoices');
		$out = exec("gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=$dir_abs_path_acc_files/$fname.pdf $files", $output, $ret);

		// NOTE: This is an indirect check if all invoices where created correctly as this is actually not possible while
		// executing pdflatex in background (see Invoice)
		if ($ret != 0) {
			$text = "Could not concatenate invoice files! $out - ".(isset($output[0]) ? $output[0] : '');
			\ChannelLog::error('billing', $text, [$ret]);
		}
		else
			\ChannelLog::info('billing', 'Concatenate all Invoice files to one PDF: Success!');

		// Zip all
		$filename = date('Y_m', $time).'.zip';
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
