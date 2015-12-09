<?php

// Authentification is necessary before accessing a route
Route::group(array('before' => 'auth'), function() {

	Route::get('provmon/{id}', array ('as' => 'Provmon.index', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@ping'));

});
