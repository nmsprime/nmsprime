<?php

namespace Modules\BillingBase\Database\Seeders;

use Modules\BillingBase\Entities\Item;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\Product;

class ItemTableSeeder extends \BaseSeeder
{
    protected static $contract_index;
    protected static $item_index;
    protected static $item_type;
    protected static $contract_ids;
    protected static $products;
    protected static $tariff_ids;
    protected static $other_ids;

    public function run()
    {
        self::$contract_ids = \DB::table('contract')->whereNull('deleted_at')->pluck('id');

        foreach (range(0, 4) as self::$contract_index) {
            foreach (range(1, 2) as self::$item_index) {
                foreach (['tariff', 'other'] as self::$item_type) {
                    // creating items is very slow – ItemObserver::created() calls Contract::daily_conversion!
                    Item::create(static::get_fake_data('seed'));
                }
            }
        }
    }

    /**
     * Returns an array with faked item data; used e.g. in seeding and testing
     *
     * @param $topic Context the method is used in (seed|test)
     * @param $contract contract to create the item at; used in testing
     *
     * @author Nino Ryschawy, Patrick Reichel
     */
    public static function get_fake_data($topic, $contract = null)
    {
        $faker = &\NmsFaker::getInstance();

        if (! self::$products) {
            self::$products = Product::all();
        }
        if (! self::$tariff_ids) {
            $tariffs = self::$products->whereIn('type', ['Internet', 'Voip', 'TV']);
            foreach ($tariffs as $prod) {
                self::$tariff_ids[] = $prod->id;
            }
        }
        if (! self::$other_ids) {
            $others = self::$products->whereIn('type', ['Device', 'Credit', 'Other']);
            foreach ($others as $prod) {
                self::$other_ids[] = $prod->id;
            }
        }

        $contract_id = $product_id = $costcenter_id = $credit_amount = $payed_month = 0;
        $count = 1;

        // set some data depending on topic
        if ($topic == 'seed') {
            $k = self::$contract_index % count(self::$contract_ids);
            $contract_id = self::$contract_ids[$k];
        } elseif ($topic == 'test') {
            // use the given contract to create itam at
            $contract_id = $contract->id;

            // depending on number of existing items: choose item type to create
            // we items_in_db
            $items_in_db = \DB::table('item')->count();
            self::$item_type = ($items_in_db % 4) < 2 ? 'tariff' : 'other';
            self::$item_index = ($items_in_db % 2) == 0 ? 1 : 2;
        }

        // create data for current item type
        if (self::$item_type == 'tariff') {
            $product_id = self::$tariff_ids[rand(0, count(self::$tariff_ids) - 1)];
            if ($topic == 'seed') {
                $valid_from = date('Y-m-d', strtotime('-'.rand(1, 20).' month'));
                $valid_to = rand(0, 10) > 7 ? null : date('Y-m-d', strtotime('+'.rand(1, 5).' month'));
            } elseif ($topic == 'test') {
                // in testing mode the validation rules are in use!
                $valid_from = date('Y-m-d', strtotime('+'.rand(1, 12).' month'));
                $valid_to = null;
            }
            $valid_from_fixed = 1;
        } elseif (self::$item_type == 'other') {
            $credit_amount = 0;
            if ($topic == 'seed') {
                $valid_from = self::$item_index == 2 ? null : date('Y-m-d', strtotime('first day of last month'));
                $valid_to = self::$item_index == 2 ? null : date('Y-m-d', strtotime('+2 month'));
            } elseif ($topic == 'test') {
                $valid_from = self::$item_index == 2 ? null : date('Y-m-d', strtotime('+'.rand(1, 12).' month'));
                $valid_to = null;
            }
            $product_id = self::$other_ids[array_rand(self::$other_ids)];
            $valid_from_fixed = 0;

            if (self::$products->find($product_id)->type == 'Credit') {
                $credit_amount = 10 * self::$contract_index;
            }
        }

        return [
            'contract_id' 	=> $contract_id,
            'product_id' 	=> $product_id,
            'count' 		=> $count,
            'valid_from' 	=> $valid_from,
            'valid_from_fixed' => $valid_from_fixed,
            'valid_to' 		=> $valid_to,
            'credit_amount' => $credit_amount,
            'costcenter_id' => $costcenter_id,
            'payed_month' 	=> $payed_month,
        ];
    }
}
