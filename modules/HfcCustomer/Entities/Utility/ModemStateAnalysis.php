<?php

namespace Modules\HfcCustomer\Entities\Utility;

class ModemStateAnalysis
{
    /**
     * Number of all online modems.
     *
     * @var int
     */
    protected $onlineModems;

    /**
     * Number of all modems.
     *
     * @var int
     */
    protected $allModems;

    /**
     * Average upstream power of a modem cluster.
     *
     * @var int
     */
    protected $UsPwrAvg;

    /**
     * State in human readable words (OK|WARNING|CRITICAL)
     *
     * @var string?
     */
    protected $state;

    /**
     * Create a new ModemStateAnalysis instance.
     *
     * @return void
     */
    public function __construct($online, $all, $modemUsPwrAvg = 40)
    {
        $this->onlineModems = $online;
        $this->usPwrAvg = $modemUsPwrAvg;
        $this->allModems = $all;
        $this->state = null;
    }

    /**
     * This runs the main analysis and returns the state of the cluster.
     *
     * @return string|bool
     */
    public function get()
    {
        if (! $this->isPossible()) {
            return false;
        }

        foreach (['critical', 'warning'] as $state) {
            if (
                $this->modemOnlinePercentage() < config("hfccustomer.threshhold.avg.percentage.{$state}") ||
                $this->usPwrAvg > config("hfccustomer.threshhold.avg.us.{$state}")
            ) {
                return $this->state = strtoupper($state);
            }
        }

        return $this->state = 'OK';
    }

    /**
     * Determines the color code for the calculated state. This color is used
     * to color the Modem Bubbles in the tree erd view.
     *
     * @return string
     */
    public function toColor()
    {
        if ($this->isPossible() && ! $this->state) {
            $this->get();
        }

        $lookup = [
            'OK' => 'green',
            'WARNING' => 'yellow',
            'CRITICAL' => 'red',
        ];

        return $lookup[$this->state];
    }

    /**
     * Determines if an analysis is possible.
     *
     * @return bool
     */
    protected function isPossible()
    {
        return $this->allModems && $this->allModems !== 0;
    }

    /**
     * Calculates the percentage of online modems.
     *
     * @return int
     */
    protected function modemOnlinePercentage()
    {
        return $this->onlineModems / $this->allModems * 100;
    }
}
