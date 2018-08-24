<?php

namespace Modules\BillingBase\Tests;

use Modules\BillingBase\Entities\Product;

/**
 * Run the lifecycle test for Product.
 */
class ProductLifecycleTest extends \BaseLifecycleTest
{
    // fields to be used in update test
    protected $update_fields = [
        'name',
        'price',
    ];

    /**
     * Extended to modify $testrun_count.
     * Cannot be done in constructor as we need a running app to use Product…
     *
     * @author Patrick Reichel
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // run $testrun_count tests for each product type
        // type to be used is calculated in seeder depending on the number of existing product table entries
        $this->testrun_count *= count(Product::getPossibleEnumValues('type'));

        return $app;
    }
}
