<?php

return [

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

	"contract" => "Anschluß (= envia-TEL-Vertrag)",
	"contract_changetariff" => "Ändere Endkundentarif bei envia TEL",
	"contract_changetariff_help" => "Diese Methode führt eine Änderung des End-Sprachtarifs durch.\n\nACHTUNG: Muss für alle Modems dieses Kunden separat durchgeführt werden!",
	"contract_changevariation" => "Ändere Einkaufstarif bei envia TEL",
	"contract_changevariation_help" => "Diese Methode führt eine Änderung der Produktvariante (bspw. Änderung von minutenbasierender Abrechnung zu Festnetzflatrate im Einkauftarif) durch.\n\nACHTUNG: Muss für alle Modems dieses Kunden separat durchgeführt werden!",
	"contract_create" => "Vertrag bei envia TEL anlegen",
	"contract_create_help" => "Diese Methode führt eine Bestellung eines Produktes durch.",
	"contract_getreference" => "Hole envia TEL Vertragsreferenz",
	"contract_getreference_help" => "Diese Methode ermittelt die durch envia TEL vergebene eindeutige Produktreferenz anhand einer beliebigen Rufnummer des Produktes.",
	"contract_getvoicedata" => "Hole SIP-/MGCP-Benutzerdaten dieses Anschlusses",
	"contract_getvoicedata_help" => "Diese Methode ermittelt die durch envia TEL konfigurierten SIP-/MGCP-Benutzerdaten sowie die Sperrklasse aller Nummern eines Produktes.",
	"contract_relocate" => "Ändere Installationsadresse bei envia TEL",
	"contract_relocate_help" => "Diese Methode beauftragt einen Umzug eines Produktes, ändert also die Installationsadresse eines Modems.\n\nACHTUNG: Änderungen der Kundendaten müssen separat an envia TEL gesendet werden (Methode „Ändere Kundendaten bei envia TEL“)!",

	"customer" => "Kunde",
	"customer_getcontracts" => "Hole envia TEL Verträge",
	"customer_getcontracts_help" => "Diese Methode ermittelt anhand der durch envia TEL vergebenen eindeutigen Kundenreferenz alle zugehörigen Verträge/Anschlüsse.",
	"customer_getreference" => "Hole envia TEL Kundenreferenz",
	"customer_getreference_help" => "Diese Methode ermittelt die durch envia TEL vergebene eindeutige Kundenreferenz anhand der durch den Reseller selbst vergebenen Endkundennummer.",
	"customer_getreferencelegacy" => "Hole envia TEL Kundenreferenz (mit alter Kundennummer)",
	"customer_getreferencelegacy_help" => "Diese Methode ermittelt die durch envia TEL vergebene eindeutige Kundenreferenz anhand der durch den Reseller selbst vergebenen alten Endkundennummer (z.B. aus altem NMS).",
	"customer_update" => "Ändere Kundendaten bei envia TEL",
	"customer_update_number" => "Ändere Kundennummer bei envia TEL",
	"customer_update_help" => "Diese Methode führt eine Aktualisierung der Endkundendaten durch.\n\nACHTUNG: Änderungen am Installationsort des Modems müssen separat an envia TEL übermittelt werden (Methode „Installationsadresse bei envia TEL ändern“)!",
	"customer_update_number_help" => "Diese Methode ändert die bei envia TEL hinterlegte Kundennummer.",

	"misc" => "Verschiedenes",
	"misc_getfreenumbers" => "Hole freie Rufnummern",
	"misc_getfreenumbers_help" => "Diese Methode liefert eine Liste mit verfügbaren Rufnummern aus dem Rufnummernpool eines Resellers.",
	"misc_getkeys" => "Hole Schlüsselwerte zur Verwendung in anderen Methoden",
	"misc_getkeys_help" => "Hiermit können z.B. EKP- und Carrier-Codes, Daten für das Anlegen von Telefonbucheinträgen usw. geholt werden.",
	"misc_getusagecsv" => "Hole Nutzerstatistiken aller Nutzer",
	"misc_getusagecsv_help" => "Diese Methode liefert eine CSV-Datei mit den Nutzerstatistiken aller Nutzer eines Resellers.",
	"misc_ping" => "Funktionstest envia TEL API",
	"misc_ping_help" => "Diese Methode dient ausschließlich dem Test der Erreichbarkeit der Schnittstelle.",

	"order" => "Aufträge",
	"order_getstatus_help" => "Hole aktuellen Auftragsstatus von envia TEL.",

	"phonebookentry" => "Telefonbucheintrag",
	"phonebookentry_create" => "Lege Telefonbucheintrag an",
	"phonebookentry_create_help" => "Die Methode fügt für die angegebene Telefonnummer einen Telefonbucheintrag hinzu oder ändert einen bestehenden Eintrag (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",
	"phonebookentry_delete" => "Lösche Telefonbucheintrag",
	"phonebookentry_delete_help" => "Die Methode löscht für die angegebene Telefonnummer einen Telefonbucheintrag (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",
	"phonebookentry_get" => "Hole Telefonbucheintrag",
	"phonebookentry_get_help" => "Die Methode fragt für die angegebene Telefonnummer einen Telefonbucheintrag ab (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",

    "voipaccount" => "Telefonnummer",
	"voipaccount_create" => "Lege VoIP-Account bei envia TEL an",
	"voipaccount_create_help" => "Diese Methode dient der Neuschaltung einer Rufnummer unter einem bestehenden Anschluss.",
	"voipaccount_terminate" => "Kündige VoIP-Account bei envia TEL",
	"voipaccount_terminate_help" => "Diese Methode dient der Kündigung einer Rufnummer unter einem bestehenden Anschluss. Wenn die letzte Rufnummer des Anschlusses gekündigt wird, wird das Produkt ebenso automatisch gekündigt.",
	"voipaccount_update" => "Ändere VoIP-Account bei envia TEL",
	"voipaccount_update_help" => "Diese Methode dient der Aktualisierung einer Rufnummer unter einem bestehenden Anschluss. Es können sowohl TRC-Klasse als auch die SIP-Daten zusammen oder einzeln geändert werden.",

	"clear_envia_contract_reference" => "Entferne envia-TEL-Vertragsreferenz (lokal)",
	"clear_envia_contract_reference_help" => "Dies ändert keine Daten bei envia TEL.",
];
