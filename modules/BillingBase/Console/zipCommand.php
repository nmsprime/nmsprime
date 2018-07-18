<?php
namespace Modules\BillingBase\Console;

use Storage;
use App\Http\Controllers\BaseViewController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\Invoice;
use Symfony\Component\Console\Input\{ InputOption, InputArgument};

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
	 * @var Maximum number of files ghost script shall concatenate at once
	 *
	 * Take care: Ghost scripts argument length must not be exceeded when you change this number
	 */
	private $split = 1000;

	/**
	 * @var global var to store total number of splits to generate percentual output
	 */
	private $num;


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
	 * Package all Files in accounting directory if specified month (or last month as default)
	 * NOTE: this Command is called in accounting Command!
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


		// Get all invoices
		// NOTE: This probably has to be replaced by DB::table for more than 10k contracts as Eloquent gets too slow then
		$invoices = Invoice::where('type', '=', 'Invoice')
			->where('year', '=', $target_t->__get('year'))->where('month', '=', $target_t->__get('month'))
			->orWhere(function ($query) use ($cdr_target_t) { $query
				->where('type', '=', 'CDR')
				->where('year', '=', $cdr_target_t->__get('year'))->where('month', '=', $cdr_target_t->__get('month'));})
			->join('contract as c', 'c.id', '=', 'invoice.contract_id')
			->orderBy('c.number', 'desc')->orderBy('invoice.type')
			->get()->all();

		$files = [];
		foreach ($invoices as $inv)
			$files[] = $inv->get_invoice_dir_path().$inv->filename;

		// Prepare and start output
		$num = count($files);
		$this->num = $num >= $this->split ? ((int) ($num / $this->split)) + 1 + ($num % $this->split ? 1 : 0) : null;

		if ($this->output) {
			$this->bar = $this->output->createProgressBar($this->num);
			$this->bar->start();
		}
		else
			accountingCommand::push_state(0, 'Zip Files');


		/**
		 * Concat Invoices
		 */
		$files = $this->_concat_split_pdfs($files);

		$fname = BaseViewController::translate_label('Invoices').'.pdf';

		// concat temporary files to final target file
		concat_pdfs($files, $acc_files_dir_abs_path.$fname);

		if ($this->output) {
			$this->bar->advance();
			echo "\n";
		}
		else
			accountingCommand::push_state(99, 'Zip Files');

		echo "New file (concatenated invoices): $acc_files_dir_abs_path"."$fname\n";


		// Zip all - suppress output of zip command
		$filename = $target_t->format('Y-m').'.zip';
		chdir($acc_files_dir_abs_path);

		ob_start();
		system("zip -r $filename *");
		ob_end_clean();
		echo "New file (Zip): $acc_files_dir_abs_path"."$filename\n";


		system('chmod -R 0700 '.$acc_files_dir_abs_path);
		system('chown -R apache '.$acc_files_dir_abs_path);

		// delete temp files
		if (count($files) >= $this->split) {
			foreach ($files as $fn ) {
				if (is_file($fn))
					unlink($fn);
			}
		}
		Storage::delete('tmp/accCmdStatus');
	}


	/**
	 * Split the number of Invoices to defined number (see global variable) and concat them to temporary files
	 *
	 * Note: This reduces performance dramatically, but is necessary to make sure the order of invoices
	 * is kept and the argument length limit for ghost script is not exceeded
	 *
	 * @param Array 	invoice files
	 */
	private function _concat_split_pdfs($files)
	{
		static $count = 0;

		if (count($files) < $this->split)
			return $files;

		$arr = array_chunk($files, $this->split);

		// recursive splitting
		if (count($arr) > $this->split)
		{
			foreach ($arr as $files2)
				$tmp_pdfs = $this->_concat_split_pdfs($files2);
		}

		foreach ($arr as $files2)
		{
			$tmp_fn = storage_path('app/tmp/').'tmp_inv_'.$count.'.pdf';
			$tmp_pdfs[] = $tmp_fn;
			$count++;

			concat_pdfs($files2, $tmp_fn);

			// Status update
			if ($this->output)
				$this->bar->advance();
			else
				accountingCommand::push_state((int) $count/$this->num*100, 'Zip Files');
		}

		return $tmp_pdfs;
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
