<?php

namespace Modules\BillingBase\Providers;

use Illuminate\Support\ServiceProvider;

class BillingBaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The artisan commands provided by this module
     */
    protected $commands = [
        'Modules\BillingBase\Console\SettlementRunCommand',
        'Modules\BillingBase\Console\fetchBicCommand',
        'Modules\BillingBase\Console\cdrCommand',
        'Modules\BillingBase\Console\ZipSettlementRun',
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);

        $this->app->alias(\Modules\BillingBase\Providers\SettlementRunProvider::class, 'settlementrun');
        $this->app->alias(\Modules\BillingBase\Providers\BillingConfProvider::class, 'billingconf');
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('billingbase.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'billingbase'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/billingbase');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/billingbase';
        }, \Config::get('view.paths')), [$sourcePath]), 'billingbase');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/billingbase');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'billingbase');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'billingbase');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            SettlementRunProvider::class,
            BillingConfProvider::class,
        ];
    }
}
