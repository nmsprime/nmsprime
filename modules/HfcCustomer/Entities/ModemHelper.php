<?php

namespace Modules\HfcCustomer\Entities;

use Modules\ProvBase\Entities\Modem;

class ModemHelper extends \BaseModel
{
    // TODO: use should be from a global config api
    public static $single_critical_us = 55;
    public static $single_warning_us = 50;
    public static $avg_critical_percentage = 50;
    public static $avg_warning_percentage = 70;
    public static $avg_critical_us = 52;
    public static $avg_warning_us = 45;

    public static function ms_num($s)
    {
        return Modem::where('netelement_id', $s)->where('us_pwr', '>', '0')->count();
    }

    public static function ms_num_all($s)
    {
        return Modem::where('netelement_id', $s)->count();
    }

    public static function ms_avg($s)
    {
        return round(Modem::where('netelement_id', $s)->where('us_pwr', '>', '0')->avg('us_pwr'), 1);
    }

    public static function ms_cri($s)
    {
        $c = self::$single_critical_us;

        return Modem::where('netelement_id', $s)->where('us_pwr', '>', $c)->count();
    }

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

    public static function ms_state($s)
    {
        $all = self::ms_num_all($s);
        if ($all == 0) {
            return -1;
        }

        $onl = self::ms_num($s);
        $avg = self::ms_avg($s);

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

    public static function ms_avg_pos($s)
    {
        $q = Modem::where('netelement_id', $s)
            ->where('us_pwr', '>', '0')
            ->where('x', '<>', '0')
            ->where('y', '<>', '0');

        return ['x' => round($q->avg('x'), 4), 'y' => round($q->avg('y'), 4)];
    }
}
