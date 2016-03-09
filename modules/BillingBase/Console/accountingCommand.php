<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Contract;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Modules\BillingBase\Entities\Price;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use File;
use DB;

class accountingCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:accounting';

	protected $tablename = 'accounting';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create accounting records table from contracts and related items';

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
		/*
		 * TODO
		 * add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
		 * add every new accounting record to the table for every contract once in a month
		 * table columns: name, contract_id, price    --- needed for creating the direct debit xml
		 * if there is already one entry in this month we should stop executing and not adding same entries again - only executable once in a month
		 * every item that was added to a contract after the last run of this function has to be considered during the next run
		 */

		$logger = new Logger('billing_logger');
		$logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'log'), Logger::DEBUG, false);
		$logger->addInfo(' #####	Creating Accounting Record table 	#####');


		$now = date('Y-m-d');
		$last_month = date('Y-m-01', strtotime('-1 months', strtotime($now)));
		$this_month = date('Y-m-01');
		$next_month = date('Y-m-01', strtotime('+1 months', strtotime($now)));


		// remove all entries of this month (if entries were already created) and create them new
		$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this_month)->where('created_at', '<=', $next_month)->first();
		if (is_object($actually_created))
		{
			$logger->addInfo('Table was already created this month - Recreate it!');
			DB::update('DELETE FROM '.$this->tablename.' WHERE created_at>='.$this_month);
		}


		$last_run = DB::table($this->tablename)->orderBy('created_at', 'desc')->select('created_at')->first();
		if (is_object($last_run))
			$last_run = $last_run->created_at;
		else
			$last_run = date('1970-01-01');
		$logger->addInfo('Last creation of table was on '.$last_run);
		
		// dd($now, $next_month, $actually_created, $last_run);

		/*
		 * Loop over all Contracts
		 * TODO: Log all Contracts that don't have a valid sepa mandate -> 
		 */
		$cs = Contract::all();
		foreach ($cs as $c)
		{
			/*
			 * Contracts
			 	* proof if mandate exists and is already/still valid (-> no mandate log)
			 	* proof if contract expires
			 		* data & voip costs proportional to month

			 * add monthly item costs for following items:
			 	* monthly (and no payment_to date ??)
			 	* once and created within last billing period
			 	* once and actual run is within payment_from and payment_to date
			 	* what's with yearly payed items ??
			 */
			$items = $c->items;
			foreach ($items as $item)
			{
				$price_entry = Price::find($item->price_id);
				$entry_cost = 0;
				switch($price_entry->billing_cycle)
				{
					case 'Monthly':
						$entry_cost = $price_entry->price;
						break;
					case 'Once': 
						if ($item->created_at > $last_run || $item->payment_to > $last_run)
							$entry_cost = $price_entry->price;
						break;
					case 'Yearly': break;
				}
				var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$price_entry->name.'", '.$entry_cost.", NOW())");
				DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$price_entry->name.'", '.$price_entry->price.", NOW())");
			}

			// add internet and voip tariffs
			if ($c->contract_end < $now)
				continue;
			$tarif['inet'] = Price::find($c->price_id);
			$tarif['voip'] = Price::find($c->voip_id);

			foreach ($tarif as $t)
			{
				if ($t)
				{
					var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$t->name.'", '.$t->price.", NOW())");
					DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$t->name.'", '.$t->price.", NOW())");					
				}
			}


		}
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
