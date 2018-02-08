<?php
namespace Modules\BillingBase\Console;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\Invoice;

use Carbon\Carbon;
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
	 * ATTENTION: As is PDF Concatenation should scale up to appr. 3 million Invoices+CDRs
	 *
	 * @author Nino Ryschawy
	 *
	 * TODO: Add more Error checking
	 */
	public function fire()
	{
		$conf = BillingBase::first();
		$offset = intval($conf->cdr_offset);
		\App::setLocale($conf->userlang);

		$year  = $this->argument('year');
		$month = $this->argument('month');

		$target_t = $year && $month ? Carbon::create($year, $month) : Carbon::create()->subMonth();

		$cdr_target_t = clone $target_t;
		$cdr_target_t->subMonthNoOverflow($offset);
		$acc_files_dir_abs_path = storage_path('app/data/billingbase/accounting/') . $target_t->format('Y-m').'/';

		\ChannelLog::debug('billing', 'Zip accounting files for Month '.$target_t->toDateString());

		// find all invoices and concatenate them
		// NOTE: This probably has to be replaced by DB::table for more than 10k contracts as Eloquent gets too slow then
		$invoices = Invoice::where('type', '=', 'Invoice')
			->where('year', '=', $target_t->__get('year'))->where('month', '=', $target_t->__get('month'))
			->orWhere(function ($query) use ($cdr_target_t) { $query
				->where('type', '=', 'CDR')
				->where('year', '=', $cdr_target_t->__get('year'))->where('month', '=', $cdr_target_t->__get('month'));})
			->join('contract as c', 'c.id', '=', 'invoice.contract_id')
			->orderBy('c.number', 'desc')->orderBy('invoice.type')
			->get()->all();

		// Attention: Ghost script argument length (strlen($files)) must be less than 131k chars long
		// TODO: Check if strlen is server dependent
		$files = '';
		$tmp_pdfs = [];
		$i = $k = 0;
		$split = 1000;
		$num = count($invoices);
		$num = $num >= $split ? ((int) ($num / $split)) + 1 + ($num % $split ? 1 : 0) : null;

		if ($this->output && $num)
			$bar = $this->output->createProgressBar($num);

		// NOTE: Splitting the invoice creation dramatically reduces performance of this command
		// Maybe another tool than gs can handle a longer argument length??
		foreach ($invoices as $inv)
		{
			$i++;
			$files .= $inv->get_invoice_dir_path().$inv->filename.' ';
			// $files .= $inv->contract_id.'/'.$inv->filename.' ';

			if ($i == $split)
			{
				// create temporary PDFs and concat them later to not violate argument length restriction
				$tmp_fn = storage_path('app/tmp/').'tmp_inv_'.$k.'.pdf';
				$tmp_pdfs[] = $tmp_fn;

				concat_pdfs($files, $tmp_fn);

				$k++;
				if ($this->output)
					$bar->advance();

				$i = 0;
				$files = '';
			}
		}

		if ($tmp_pdfs)
		{
			if ($files)
			{
				$tmp_fn = storage_path('app/tmp/').'tmp_inv_'.$k.'.pdf';
				$tmp_pdfs[] = $tmp_fn;

				concat_pdfs($files, $tmp_fn);

				if ($this->output)
					$bar->advance();
			}

			$files = implode(' ', $tmp_pdfs);
		}

		$fname = \App\Http\Controllers\BaseViewController::translate_label('Invoices').'.pdf';

		// NOTE: This is an indirect check if all invoices where created correctly as this is actually not possible while
		// executing pdflatex in background (see Invoice)
		concat_pdfs($files, $acc_files_dir_abs_path.$fname);

		if ($this->output && $num) {
			$bar->advance();
			echo "\n";
		}

		echo "Stored concatenated Invoices: $acc_files_dir_abs_path"."$fname\n";

		// Zip all - suppress output of zip command
		$filename = $target_t->format('Y-m').'.zip';
		chdir($acc_files_dir_abs_path);

		ob_start();
		system("zip -r $filename *");
		ob_end_clean();

		system('chmod -R 0700 '.$acc_files_dir_abs_path);
		system('chown -R apache '.$acc_files_dir_abs_path);

		// delete temp files
		foreach ($tmp_pdfs as $fn ) {
			if (is_file($fn))
				unlink($fn);
		}
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
