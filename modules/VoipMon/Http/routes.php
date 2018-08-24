<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Cdr', 'Modules\VoipMon\Http\Controllers\CdrController');
});
