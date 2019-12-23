<?php

namespace Modules\OverdueDebts\Providers;

use Modules\OverdueDebts\Entities\OverdueDebts;

class OverdueDebtsConfig
{
    protected $config;

    public function __construct()
    {
        $this->config = OverdueDebts::first();
    }

    public function get()
    {
        return $this->config;
    }
}
