<?php namespace Modules\hfccustomer\Providers;

use Illuminate\Support\ServiceProvider;

class HfcCustomerServiceProvider extends ServiceProvider {

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
	];

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
