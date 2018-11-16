<?php

namespace Modules\ProvMon\Providers;

use Illuminate\Support\ServiceProvider;

class ProvMonServiceProvider extends ServiceProvider
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
        'Modules\ProvMon\Console\cactiCommand',
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
            __DIR__.'/../Config/config.php' => config_path('provmon.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'provmon'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        \View::addNamespace('provmon', __DIR__.'/../Resources/views');

        return;

        $viewPath = base_path('resources/views/modules/provmon');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ]);

        $this->loadViewsFrom([$viewPath, $sourcePath], 'provmon');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/provmon');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'provmon');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'provmon');
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
