<?php

namespace Modules\BillingBase\Providers;

use Modules\BillingBase\Entities\BillingBase;

/**
 * Set Currency globally for the app to avoid multiple database calls
 */
class BillingConfProvider
{
    /**
     * @var string
     */
    protected $currency;

    /**
     * @var float
     */
    protected $tax;

    public static $labels = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
    ];

    public static $latexEncoding = [
        '€' => '\\euro',
        '$' => '\\$',
    ];

    public function __construct()
    {
        $this->setConf();
    }

    private function setConf()
    {
        $conf = BillingBase::first();

        $this->currency = self::$labels[strtoupper($conf->currency)] ?? '$';
        $this->tax = $conf->tax / 100;
    }

    public function currency()
    {
        return $this->currency;
    }

    public function tax()
    {
        return $this->tax;
    }

    /**
     * Get LaTeX utf8 encoding for global currency - used in invoice templates
     */
    public function currencyLatex()
    {
        return self::$latexEncoding[$this->currency] ?? '\\$';
    }
}
