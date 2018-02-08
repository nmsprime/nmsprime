<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
	realpath(__DIR__.'/../')
);


/*
|--------------------------------------------------------------------------
| Read the config files
|--------------------------------------------------------------------------
|
| We use different .env files (e.g. for modules).
| This method reads them all.
| @author Patrick Reichel
*/

// directory holding our .env files
$env_dir = '/etc/nmsprime/env/';

// get all .env files from /etc
$files = scandir($env_dir);

// read environmental data from files ending with .env
foreach ($files as $f) {
	if (substr($f, -4) == '.env') {
		$dotenv = new \Dotenv\Dotenv($env_dir, $f);
		$dotenv->overload();
	}
}


/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
	'Illuminate\Contracts\Http\Kernel',
	'App\Http\Kernel'
);

$app->singleton(
	'Illuminate\Contracts\Console\Kernel',
	'App\Console\Kernel'
);

$app->singleton(
	'Illuminate\Contracts\Debug\ExceptionHandler',
	'App\Exceptions\Handler'
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
