<?php

// Authentification is necessary before accessing a route
Route::group(array('before' => 'auth'), function() {

	Route::get('provmon/{id}', array ('as' => 'Provmon.index', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));
	Route::get('provmon_cpe/{id}', array ('as' => 'Provmon.cpe', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis'));	

});
