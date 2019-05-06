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
	"clear_envia_contract_reference" => "Clear envia TEL contract reference (local)",
	"clear_envia_contract_reference_help" => "Does not change any data at envia TEL.",
	"contract" => "Telephone connection (= envia TEL contract)",
	"contract_changetariff" => "Change tariff",
	"contract_changetariff_help" => "Changes the VoIP sales tariff for this modem (=envia TEL contract).\n\nATTENTION: Has also to be changed for all other modems related to this customer!",
	"contract_changevariation" => "Change purchase tariff",
	"contract_changevariation_help" => "Changes the VoIP purchase tariff for this envia TEL contract.\n\nATTENTION: Has also to be changed for all other envia TEL contracts related to this customer!",
	"contract_create" => "Create envia TEL contract",
	"contract_create_help" => "This method performs an order for a product.",
	"contract_getreference" => "Get envia TEL contract reference",
	"contract_getreference_help" => "This method determines the unique product reference assigned by envia TEL based on any phone number of the product.",
	"contract_getvoicedata" => "Get voice data",
	"contract_getvoicedata_help" => "Get SIP and TRC data for all phonenumbers on this envia TEL contract.",
	"contract_relocate" => "Relocate contract",
	"contract_relocate_help" => "Changes (physical) installation address of this modem.\n\nATTENTION: Changes of customer address have to be sent separately (using “Update customer”)!",
	"customer" => "Customer",
	"customer_getcontracts" => "Get envia TEL contracts",
	"customer_getcontracts_help" => "This method determines all associated contracts/connections on the basis of the unique customer reference assigned by envia TEL.",
	"customer_getreference" => "Get envia TEL customer reference",
	"customer_getreference_help" => "This method determines the unique customer reference assigned by envia TEL on the basis of the end customer number assigned by the reseller himself.",
	"customer_getreferencelegacy" => "Get envia TEL customer reference (using legacy customer number)",
	"customer_getreferencelegacy_help" => "This method determines the unique customer reference assigned by envia TEL on the basis of the legacy end customer number (e.g. from previous NMS) assigned by the reseller himself.",
	"customer_update" => "Update customer at envia TEL",
	"customer_update_help" => "Pushes changes on customer data to envia TEL.\n\nATTENTION: Changes of modem installation address have to be sent separately (using “Relocate contract”)!",
	"customer_update_number" => "Update customer number at envia TEL",
	"customer_update_number_help" => "Sends a changed customer number to envia TEL.",
	"misc" => "Miscellaneous",
	"misc_getfreenumbers" => "Get free phonenumbers",
	"misc_getfreenumbers_help" => "This method returns a list of available phone numbers from a reseller's phone number pool.",
	"misc_getkeys" => "Get key values for use in other methods",
	"misc_getkeys_help" => "This method gets e.g. EKP codes, carrier codes, phonebook entry related data, …",
	"misc_getusagecsv" => "Get user statistics of all users",
	"misc_getusagecsv_help" => "This method returns a CSV file with the user statistics of all users of a reseller.",
	"misc_ping" => "Test envia TEL API (ping)",
	"misc_ping_help" => "This method is only used to test the availability of the API.",
	"order" => "Orders",
	"order_getstatus_help" => "Gets the current state of this order from envia TEL.",
	"phonebookentry" => "Phonebook entry",
	"phonebookentry_create" => "Create phonebook entry",
	"phonebookentry_create_help" => "Creates a new or updates an existing phonebook entry for this phonenumber (EXPERIMENTAL).",
	"phonebookentry_delete" => "Delete phonebook entry",
	"phonebookentry_delete_help" => "Creates a new or updates an existing phonebook entry for this phonenumber (EXPERIMENTAL).",
	"phonebookentry_get" => "Get phonebook entry",
	"phonebookentry_get_help" => "Gets the current phonebook entry for this phonenumber (EXPERIMENTAL).",
	"voipaccount" => "Phonenumber",
	"voipaccount_create" => "Create VoIP account",
	"voipaccount_create_help" => "Creates the phonenumber at envia TEL.",
	"voipaccount_terminate" => "Terminate VoIP account",
	"voipaccount_terminate_help" => "Terminates the phonenumber at envia TEL.",
	"voipaccount_update" => "Update VoIP account",
	"voipaccount_update_help" => "Updates the phonenumber at envia TEL.",
];
