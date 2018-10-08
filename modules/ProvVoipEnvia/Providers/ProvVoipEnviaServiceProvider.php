<?php

namespace Modules\ProvVoipEnvia\Providers;

use Illuminate\Support\ServiceProvider;

class ProvVoipEnviaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * The artisan commands provided by this module
     */
    protected $commands = [
        '\Modules\ProvVoipEnvia\Console\EnviaContractGetterCommand',
        '\Modules\ProvVoipEnvia\Console\EnviaContractReferenceGetterCommand',
        '\Modules\ProvVoipEnvia\Console\EnviaCustomerReferenceGetterCommand',
        '\Modules\ProvVoipEnvia\Console\EnviaCustomerReferenceFromCSVUpdaterCommand',
        '\Modules\ProvVoipEnvia\Console\EnviaOrderUpdaterCommand',
        '\Modules\ProvVoipEnvia\Console\EnviaOrderProcessorCommand',
        '\Modules\ProvVoipEnvia\Console\VoiceDataUpdaterCommand',
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
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('provvoipenvia.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'provvoipenvia'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/provvoipenvia');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/provvoipenvia';
        }, \Config::get('view.paths')), [$sourcePath]), 'provvoipenvia');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/provvoipenvia');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'provvoipenvia');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'provvoipenvia');
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
