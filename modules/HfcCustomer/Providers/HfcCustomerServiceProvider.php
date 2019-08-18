<?php

namespace Modules\HfcCustomer\Providers;

use Illuminate\Support\ServiceProvider;

class HfcCustomerServiceProvider extends ServiceProvider
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
        'Modules\HfcCustomer\Console\MpsCommand',
        'Modules\HfcCustomer\Console\ModemRefreshCommand',
        'Modules\HfcCustomer\Console\ClustersCommand',
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
        \View::addNamespace('hfccustomer', __DIR__.'/../Resources/views');

        $this->commands($this->commands);
    }

    /*
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('hfccustomer.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'hfccustomer'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/HfcCustomer');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/HfcCustomer';
        }, \Config::get('view.paths')), [$sourcePath]), 'HfcCustomer');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/HfcCustomer');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'HfcCustomer');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'HfcCustomer');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
