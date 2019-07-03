<?php

namespace Modules\BillingBase\Providers;

/**
 * Set Currency globally for the app to avoid multiple database calls
 */
class CurrencyProvider
{
    /**
     * @var string
     */
    protected $currency;

    public static $labels = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
    ];

    public function __construct()
    {
        $this->setCurrency();
    }

    private function setCurrency()
    {
        $currency = \Modules\BillingBase\Entities\BillingBase::first(['currency'])->currency;

        $this->currency = self::$labels[strtoupper($currency)] ?? '$';
    }

    public function get()
    {
        return $this->currency;
    }
}
