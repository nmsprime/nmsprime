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
		// this comes from config/app.php (key 'url')
		$this->base_url = \Config::get('app.url');

		parent::__construct();
	}


	/**
	 * Execute the console command.
	 * Basically this does two jobs:
	 *   - first get csv containing all (phonenumber related) orders from envia and update database using ProvVoipEnvia model ⇒ this will get possible orders that has been manually created
	 *   - second get status for each single order ⇒ this will update contract/customer related orders as well
	 *
	 * @return null
	 */
	public function fire()
	{

		Log::info($this->description);

		echo "\n";
		$this->_get_all_orders_csv();

		echo "\n";
		$this->_get_orders();

		echo "\n";
		$this->_update_orders();

		echo "\n";

	}

	/**
	 * Gets CSV for all phonenumber related orders from envia and updates database via model ProvVoipEnvia
	 *
	 * @author Patrick Reichel
	 */

	protected function _get_all_orders_csv() {

		Log::debug('Getting all orders csv');

		// create URL suffix
		$url_suffix = \URL::route("ProvVoipEnvia.cron", array('job' => 'misc_get_orders_csv', 'really' => 'True'), false);

		// build complete URL
		$url = $this->base_url.$url_suffix;

		// execute using cURL
		$this->_perform_curl_request($url);
	}


	/**
	 * Get all the Envia orders to be updated.
	 * Currently this is an simple select – later we could add some more checks: E.g. don't get updates for orders in final state.
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_orders() {

		Log::debug('Getting orders from database');
		$this->orders = EnviaOrder::all();

	}

	/**
	 * Update the relevant orders.
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_orders() {

		foreach ($this->orders as $order) {

			$order_id = $order->orderid;

			// if current order is not in final state: update
			if (!EnviaOrder::orderstate_is_final($order)) {
				Log::debug('Updating order '.$order_id);

				// get the relative URL to execute the cron job for updating the current order_id
				$url_suffix = \URL::route("ProvVoipEnvia.cron", array('job' => 'order_get_status', 'order_id' => $order_id, 'really' => 'True'), false);

				$url = $this->base_url.$url_suffix;

				$this->_perform_curl_request($url);

				if ($this->_updated($order_id)) {
					Log::info("Updated Order id ".$order_id.".");
				}
			}

		}

	}

	/**
	 * Update an order (using a curl request against the given URL.
	 * Since updating uses the same functionality as updating via frontend we accessing the cron method in ProvVoipEnviaController using cURL.
	 *
	 * This may be not the best way – but the one without bigger refactoring of the sources…
	 * TODO: Evaluate other solutions…
	 *
	 * @author Patrick Reichel
	 *
	 * @param $url URL to be accessed by cURL
	 */
	protected function _perform_curl_request($url) {

		$ch = curl_init();

		$opts = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_SSL_VERIFYPEER => false,	// no valid cert for “localhost” – so we don't check
			CURLOPT_RETURNTRANSFER => TRUE,		// return result instead of instantly printing to screen
		);

		curl_setopt_array($ch, $opts);

		$res = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code != 200) {
			Log::error("HTTP error ".$http_code." occured in scheduled updating of envia orders calling ".$url);
		}

		curl_close($ch);
	}

	/**
	 * Check if the given order id has been updated.
	 * This simply compares database “updated_at” against the system clock.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $order_id ID of the order to be checked
	 */
	protected function _updated($order_id) {

		// not older than 1 hours (this is relatively long; but there are some timing issues and
		// the script is run late at night…)
		$timedelta_max = 60 * 60 * 1;
		$compare_time = date('Y-m-d H:i:s', time() - $timedelta_max);

		$order = EnviaOrder::withTrashed()->where('orderid', '=', $order_id)->first();

		// if order is deleted it has been updated
		if (boolval($order->deleted_at)) {
			return true;
		}

		// else: compare times
		if ($order->updated_at >= $compare_time) {
			return true;
		}

		return false;

	}

}
