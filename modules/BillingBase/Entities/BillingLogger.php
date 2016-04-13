<?php

namespace Modules\BillingBase\Entities;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BillingLogger extends Logger {

	public function __construct()
	{
		$logger = new Logger('Billing');
		$logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);

		return $logger;
	}

}