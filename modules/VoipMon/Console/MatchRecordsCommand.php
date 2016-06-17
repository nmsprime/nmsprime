<?php namespace Modules\Voipmon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class matchRecordsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'voipmon:match_records';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Match voipmonitor call monitoring records to phonenumbers';

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
	 * @author: Ole Ernst
	 */
	public function fire()
	{
		/**
		 * If call originated from our network (i.e. *caller* matches) and
		 * has not been processed yet (i.e. created_at is NULL) take *a* MOS value
		 * If MOS value is not valid (i.e. less than 10) set it to 45 (best)
		 */
		\DB::table('voipmonitor.cdr as c')->join('db_lara.phonenumber as p', 'c.caller', '=', \DB::raw('concat(p.prefix_number, p.number)'))->whereNull('c.created_at')->update(['c.phonenumber_id' => \DB::raw('p.id'), 'c.mos_min_mult10' => \DB::raw('IF(c.a_mos_f1_min_mult10 >= 10, c.a_mos_f1_min_mult10, 45)')]);
		/**
		 * If call originated from external network (i.e. *called* matches) and
		 * has not been processed yet (i.e. created_at is NULL) take *b* MOS value
		 * If MOS value is not valid (i.e. less than 10) set it to 45 (best)
		 */
		\DB::table('voipmonitor.cdr as c')->join('db_lara.phonenumber as p', 'c.called', '=', \DB::raw('concat(p.prefix_number, p.number)'))->whereNull('c.created_at')->update(['c.phonenumber_id' => \DB::raw('p.id'), 'c.mos_min_mult10' => \DB::raw('IF(c.b_mos_f1_min_mult10 >= 10, c.b_mos_f1_min_mult10, 45)')]);

		// Set {created,updated}_at to callend to signify that matching was done
		\DB::table('voipmonitor.cdr as c')->update(['c.created_at' => \DB::raw('c.callend'), 'c.updated_at' => \DB::raw('c.callend')]);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
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
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
