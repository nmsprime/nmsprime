<?php

namespace Modules\HfcCustomer\Entities;

class ModemHelper extends \BaseModel
{
    // TODO: use should be from a global config api
    public static $single_critical_us = 55;
    public static $single_warning_us = 50;
    public static $avg_critical_percentage = 50;
    public static $avg_warning_percentage = 70;
    public static $avg_critical_us = 52;
    public static $avg_warning_us = 45;

    public static function _ms_state($onl, $all, $avg)
    {
        if (! $all) {
            return 'OK';
        }

        if ($onl / $all * 100 < self::$avg_critical_percentage || $avg > self::$avg_critical_us) {
            return 'CRITICAL';
        }
        if ($onl / $all * 100 < self::$avg_warning_percentage || $avg > self::$avg_warning_us) {
            return 'WARNING';
        }

        return 'OK';
    }

    public static function ms_state($netelem)
    {
        $all = $netelem->modems_count;
        if ($all == 0) {
            return -1;
        }

        $onl = $netelem->modems_online_count;
        $avg = $netelem->modemsUsPwrAvg;

        if ($onl / $all * 100 < self::$avg_critical_percentage || $avg > self::$avg_critical_us) {
            return 'CRITICAL';
        }
        if ($onl / $all * 100 < self::$avg_warning_percentage || $avg > self::$avg_warning_us) {
            return 'WARNING';
        }

        return 'OK';
    }

    public static function ms_state_to_color($s)
    {
        if ($s == 'OK') {
            return 'green';
        }
        if ($s == 'WARNING') {
            return 'yellow';
        }
        if ($s == 'CRITICAL') {
            return 'red';
        }

        return -1;
    }
}
