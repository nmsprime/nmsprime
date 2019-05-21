<?php

/*
|--------------------------------------------------------------------------
| Language lines for module ProvVoipEnvia
|--------------------------------------------------------------------------
|
| The following language lines are used by the module ProvVoipEnvia
| As far as we know this module is in use in Germany, only. So no translation
| for other languages is needed at the moment.
|
*/

return [
	'carrier_invalid' => ':0 ist kein gültiger Carrier-Code.',
    'apiversion_not_float' => 'PROVVOIPENVIA__REST_API_VERSION in .env muß ein Fließkommawert sein (z.B. 1.4).',
    'carrier_no_porting_in' => 'Bei nicht zu portierender Rufnummer muss der Carriercode D057 (envia TEL) verwendet werden.',
    'customernumber_not_existing' => 'Keine Kundennummer eingetragen – mit alter Kundennummer neu versuchen.',
    'deac_date_not_set' => 'Deaktivierungsdatum nicht gesetzt in PhonenumberManagement.',
    'document_already_uploaded' => 'Das übergebene Dokument wurde bereits hochgeladen.',
    'enviacontract_id_missing' => 'Keine envia-TEL-Vertrags-ID (contract_external_id) vorhanden.',
    'error_creating_xml' => 'Beim Erzeugen des XML für den Versand an envia TEL ist ein Fehler aufgetreten',
    'has_to_be_numeric' => ':value muss eine Zahl sein',
    'legacy_customernumber_not_existing' => 'Keine alte Kundennummer eingetragen – mit aktueller Kundennummer neu versuchen.',
    'multiple_envia_contracts_at_modem' => 'Dem Modem sind mehrere envia-TEL-Verträge zugeordnet (:0). Dies kann nicht verarbeitet werden – bitte nutzen Sie das envia-TEL-Resellerportal.',
    'needs_to_be_string_or_array' => ':0 muß entweder ein String oder ein Array sein, übergeben wurde :1.',
    'no_model_given' => 'Kein model übergeben.',
    'no_numbers_for_envia_contract' => 'Keine Rufnummern für den envia-TEL-Vertrag :0 gefunden.',
    'no_trc_set' => 'Sperrklasse nicht gesetzt.',
    'order_not_existing' => 'FEHLER: envia-TEL-Auftrag :0 existiert nicht in unserer Datenbank.',
    'orderid_orderdocument_mismatch' => 'Übergebene envia-TEL-Auftrags-ID (:0) passt nicht zum Dokument.',
    'relocate_date_missing' => 'Datum der Änderung der Installationsadresse muss gesetzt sein.',
    'value_not_allowed_for_param' => 'Wert :0 nicht zulässig für Parameter $level',
];
