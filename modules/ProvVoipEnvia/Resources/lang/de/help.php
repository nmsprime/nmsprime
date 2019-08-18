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
        'clearEnviaContractReference' => 'Dies ändert keine Daten bei envia TEL.',
        'contractChangeTariff' => "Diese Methode führt eine Änderung des End-Sprachtarifs durch.\n\nACHTUNG: Muss für alle Modems dieses Kunden separat durchgeführt werden!",
        'contractChangeVariation' => "Diese Methode führt eine Änderung der Produktvariante (bspw. Änderung von minutenbasierender Abrechnung zu Festnetzflatrate im Einkauftarif) durch.\n\nACHTUNG: Muss für alle Modems dieses Kunden separat durchgeführt werden!",
        'contractCreate' => 'Diese Methode führt eine Bestellung eines Produktes durch.',
        'contractGetReference' => 'Diese Methode ermittelt die durch envia TEL vergebene eindeutige Produktreferenz anhand einer beliebigen Rufnummer des Produktes.',
        'contractGetTariff' => 'Diese Methode fragt den aktuellen End-Sprachtarif für den Anschluss ab. Sollte sich aktuell eine Änderung des Sprachtarifs in Bearbeitung befinden, gibt die Methode noch den alten Tarif zurück.',
        'contractGetVariation' => 'Diese Methode ermittelt die Produktvariante eines Anschlusses. Sollte sich aktuell ein Variantenwechsel in Bearbeitung befinden, gibt die Methode noch die alte Variante zurück.',
        'contractGetVoiceData' => 'Diese Methode ermittelt die durch envia TEL konfigurierten SIP-/MGCP-Benutzerdaten sowie die Sperrklasse aller Nummern eines Produktes.',
        'contractRelocate' => "Diese Methode beauftragt einen Umzug eines Produktes, ändert also die Installationsadresse eines Modems.\n\nACHTUNG: Änderungen der Kundendaten müssen separat an envia TEL gesendet werden (Methode „Ändere Kundendaten bei envia TEL“)!",
        'customerGetContracts' => 'Diese Methode ermittelt anhand der durch envia TEL vergebenen eindeutigen Kundenreferenz alle zugehörigen Verträge/Anschlüsse.',
        'customerGetReference' => 'Diese Methode ermittelt die durch envia TEL vergebene eindeutige Kundenreferenz anhand der durch den Reseller selbst vergebenen Endkundennummer.',
        'customerGetReferenceLegacy' => 'Diese Methode ermittelt die durch envia TEL vergebene eindeutige Kundenreferenz anhand der durch den Reseller selbst vergebenen alten Endkundennummer (z.B. aus altem NMS).',
        'customerUpdate' => "diese Methode führt eine Aktualisierung der Endkundendaten durch.\n\nACHTUNG: Änderungen am Installationsort des Modems müssen separat an envia TEL übermittelt werden (Methode „Installationsadresse bei envia TEL ändern“)!",
        'customerUpdateNumber' => 'Diese Methode ändert die bei envia TEL hinterlegte Kundennummer.',
        'miscGetFreeNumbers' => 'Diese Methode liefert eine Liste mit verfügbaren Rufnummern aus dem Rufnummernpool eines Resellers.',
        'miscGetKeys' => 'Hiermit können z.B. EKP- und Carrier-Codes, Daten für das Anlegen von Telefonbucheinträgen usw. geholt werden.',
        'miscGetUsageCsv' => 'Diese Methode liefert eine CSV-Datei mit den Nutzerstatistiken aller Nutzer eines Resellers.',
        'miscPing' => 'Diese Methode dient ausschließlich dem Test der Erreichbarkeit der Schnittstelle.',
        'orderGetStatus' => 'Hole aktuellen Auftragsstatus von envia TEL.',
        'phonebookEntryCreate' => "Die Methode fügt für die angegebene Telefonnummer einen Telefonbucheintrag hinzu oder ändert einen bestehenden Eintrag (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",
        'phonebookEntryDelete' => "Die Methode löscht für die angegebene Telefonnummer einen Telefonbucheintrag (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",
        'phonebookEntryGet' => "Die Methode fragt für die angegebene Telefonnummer einen Telefonbucheintrag ab (EXPERIMENTELL).\nDie Eingaben werden direkt an die Telefonbuchschnittstelle der Deutschen Telekom AG weitergegeben, etwaige Fehlermeldungen werden an den Reseller durchgereicht.",
        'voipAccountCreate' => 'Diese Methode dient der Neuschaltung einer Rufnummer unter einem bestehenden Anschluss.',
        'voipAccountTerminate' => 'Diese Methode dient der Kündigung einer Rufnummer unter einem bestehenden Anschluss. Wenn die letzte Rufnummer des Anschlusses gekündigt wird, wird das Produkt ebenso automatisch gekündigt.',
        'voipAccountUpdate' => 'Diese Methode dient der Aktualisierung einer Rufnummer unter einem bestehenden Anschluss. Es können sowohl TRC-Klasse als auch die SIP-Daten zusammen oder einzeln geändert werden.',
    ],
];
