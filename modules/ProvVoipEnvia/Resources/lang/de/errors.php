<?php

return [

    'apiversion_not_float' => 'PROVVOIPENVIA__REST_API_VERSION in .env muß ein Fließkommawert sein (z.B. 1.4).',

	'carrier_invalid' => ':0 ist kein gültiger Carrier-Code.',
    'carrier_no_porting_in' => 'Bei nicht zu portierender Rufnummer muss der Carriercode D057 (envia TEL) verwendet werden.',
    'customernumber_not_existing' => 'Keine Kundennummer eingetragen – mit alter Kundennummer neu versuchen.',

    'deac_date_not_set' => 'Deaktivierungsdatum nicht gesetzt in PhonenumberManagement.',

    'has_to_be_numeric' => ':value muss eine Zahl sein',

    'legacy_customernumber_not_existing' => 'Keine alte Kundennummer eingetragen – mit aktueller Kundennummer neu versuchen.',

    'no_model_given' => 'Kein model übergeben.',
    'no_trc_set' => 'Sperrklasse nicht gesetzt.',
    'no_numbers_for_envia_contract' => 'Keine Rufnummern für den envia-TEL-Vertrag :0 gefunden.',

    'value_not_allowed_for_param' => 'Wert :0 nicht zulässig für Parameter $level',

];
