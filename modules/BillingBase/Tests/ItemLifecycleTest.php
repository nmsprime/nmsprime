<?php

namespace Modules\BillingBase\Tests;

use Modules\BillingBase\Entities\Item;

/**
 * Run the lifecycle test for Item.
 */
class ItemLifecycleTest extends \BaseLifecycleTest
{
    // item can only be created from Contract.edit
    protected $create_from_model_context = '\Modules\ProvBase\Entities\Contract';

    // fields to be used in update test
    protected $update_fields = [
        'name',
        'price',
    ];

    // there are some specialities for item MVC
    protected $tests_to_be_excluded = [
        'testIndexViewVisible', // no index view
    ];

    /**
     * Extended to modify $testrun_count.
     * Cannot be done in constructor as we need a running app to use Item…
     *
     * @author Patrick Reichel
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // create 4 items per contract
        $this->testrun_count *= 4;

        return $app;
    }
}
