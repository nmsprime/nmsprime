<?php

return [

    'apiversion_not_float' => 'PROVVOIPENVIA__REST_API_VERSION in .env has to be a float value (e.g.: 1.4)',

	'carrier_invalid' => ':0 is not a valid carrier_code.',
    'carrier_no_porting_in' => 'If no incoming porting: Carriercode has to be D057 (envia TEL).',
    'customernumber_not_existing' => 'Customernumber does not exist – try using legacy version.',

    'deac_date_not_set' => 'No deactivation date in PhonenumberManagement set.',

    'has_to_be_numeric' => ':value has to be numeric',

    'legacy_customernumber_not_existing' => 'Legacy customernumber does not exist – try using normal version.',

    'no_model_given' => 'No model given',
    'no_trc_set' => 'TRCclass not set.',
    'no_numbers_for_envia_contract' => 'No phonenumbers found for envia TEL contract :0.',

    'value_not_allowed_for_param' => 'Value :a not allowed for param $level',

];
