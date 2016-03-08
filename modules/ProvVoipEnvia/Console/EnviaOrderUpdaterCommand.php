<?php namespace Modules\ProvvoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Modules\ProvVoipEnvia\Entities\EnviaOrder;
use \Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaOrderUpdaterCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'provvoipenvia:update_envia_orders';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update Envia orders database';

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
	 * @return null
	 */
	public function fire()
	{

		$this->_get_orders();

		$this->_update_orders();

	}

	protected function _get_orders() {

		$this->orders = EnviaOrder::all();

	}

	protected function _update_orders() {

		$c = new ProvVoipEnviaController();
		/* foreach ($this->orders as $order) { */
		/* 	 $order_id = $order->orderid; */
		/* } */
	}

}
