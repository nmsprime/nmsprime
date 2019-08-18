<?php

namespace Modules\BillingBase\Providers;

use Illuminate\Support\Facades\Facade;

/**
 * Provider to get currency string for the whole application
 */
class Currency extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'currency';
    }
}
