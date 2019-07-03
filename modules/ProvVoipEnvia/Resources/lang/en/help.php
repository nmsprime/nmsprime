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
    'api' => [
        'clearEnviaContractReference' => 'Does not change any data at envia TEL.',
        'contractChangeTariff' => "Changes the VoIP sales tariff for this modem (=envia TEL contract).\n\nATTENTION: Has also to be changed for all other modems related to this customer!",
        'contractChangeVariation' => "Changes the VoIP purchase tariff for this envia TEL contract.\n\nATTENTION: Has also to be changed for all other envia TEL contracts related to this customer!",
        'contractCreate' => 'This method performs an order for a product.',
        'contractGetReference' => 'This method determines the unique product reference assigned by envia TEL based on any phone number of the product.',
        'contractGetTariff' => 'Gets the current tariff for this contract. If a tariff change is in progress the old tariff will be shown.',
        'contractGetVariation' => 'Gets the current variation for this contract. If a variation change is in progress the old variation will be shown.',
        'contractGetVoiceData' => 'Get SIP and TRC data for all phonenumbers on this envia TEL contract.',
        'contractRelocate' => "Changes (physical) installation address of this modem.\n\nATTENTION: Changes of customer address have to be sent separately (using “Update customer”)!",
        'customerGetContracts' => 'This method determines all associated contracts/connections on the basis of the unique customer reference assigned by envia TEL.',
        'customerGetReference' => 'This method determines the unique customer reference assigned by envia TEL on the basis of the end customer number assigned by the reseller himself.',
        'customerGetReferenceLegacy' => 'This method determines the unique customer reference assigned by envia TEL on the basis of the legacy end customer number (e.g. from previous NMS) assigned by the reseller himself.',
        'customerUpdate' => "pushes changes on customer data to envia TEL.\n\nATTENTION: Changes of modem installation address have to be sent separately (using “Relocate contract”)!",
        'customerUpdateNumber' => 'Sends a changed customer number to envia TEL.',
        'miscGetFreeNumbers' => "This method returns a list of available phone numbers from a reseller's phone number pool.",
        'miscGetKeys' => 'This method gets e.g. EKP codes, carrier codes, phonebook entry related data, …',
        'miscGetUsageCsv' => 'This method returns a CSV file with the user statistics of all users of a reseller.',
        'miscPing' => 'This method is only used to test the availability of the API.',
        'orderGetStatus' => 'Gets the current state of this order from envia TEL.',
        'phonebookEntryCreate' => 'Creates a new or updates an existing phonebook entry for this phonenumber (EXPERIMENTAL).',
        'phonebookEntryDelete' => 'Creates a new or updates an existing phonebook entry for this phonenumber (EXPERIMENTAL).',
        'phonebookEntryGet' => 'Gets the current phonebook entry for this phonenumber (EXPERIMENTAL).',
        'voipAccountCreate' => 'Creates the phonenumber at envia TEL.',
        'voipAccountTerminate' => 'Terminates the phonenumber at envia TEL.',
        'voipAccountUpdate' => 'Updates the phonenumber at envia TEL.',
    ],
];
