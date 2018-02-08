<?php

namespace Modules\ProvVoipEnvia\Providers;

use Illuminate\Support\ServiceProvider;

class ProvVoipEnviaServiceProvider extends ServiceProvider {

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
		'\Modules\ProvVoipEnvia\Console\EnviaContractGetterCommand',
		'\Modules\ProvVoipEnvia\Console\EnviaContractReferenceGetterCommand',
		'\Modules\ProvVoipEnvia\Console\EnviaCustomerReferenceGetterCommand',
		'\Modules\ProvVoipEnvia\Console\EnviaCustomerReferenceFromCSVUpdaterCommand',
		'\Modules\ProvVoipEnvia\Console\EnviaOrderUpdaterCommand',
		'\Modules\ProvVoipEnvia\Console\EnviaOrderProcessorCommand',
		'\Modules\ProvVoipEnvia\Console\VoiceDataUpdaterCommand',
		];

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		\View::addNamespace('provvoipenvia', __DIR__.'/../Resources/views');
		$this->commands($this->commands);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
