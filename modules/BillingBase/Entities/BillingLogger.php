<?php

namespace Modules\BillingBase\Entities;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BillingLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('Billing');
        // $this->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);
        $this->pushHandler(new StreamHandler(storage_path().'/logs/billing.log'), Logger::DEBUG, false);
    }
}
