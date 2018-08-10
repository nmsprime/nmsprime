<?php

return [

	/*
	|--------------------------------------------------------------------------
	| All other Language Lines - TODO: split descriptions and messages?
	|--------------------------------------------------------------------------
	*/

	// Descriptions of Form Fields in Edit/Create
	'accCmd_error_noCC' 	=> "Dem Vertrag :contract_nr [ID :contract_id] wurde keine Kostenstelle zugewiesen. Für den Kunde wird keine Rechnung erstellt.",
	'accCmd_invoice_creation_deactivated' => "Bei folgenden Verträgen wurde die Rechnungserstellung deaktiviert: :contractnrs",
	'accCmd_processing' 	=> 'Der Abrechnungslauf wird erstellt. Bitte warten Sie bis der Prozess abgeschlossen ist.',
	'accCmd_notice_CDR' 	=> "Dem Vertrag :contract_nr [ID :contract_id] werden Einzelverbindungsnachweise abgerechnet, obwohl kein gültiger Telefontarif vorliegt. (Kommt durch verzögerte Abrechnung nach Beenden des Tarifs vor)",
	'alert' 				=> 'Achtung!',
	'ALL' 					=> 'ALLE',
	'Call Data Record'		=> 'Einzelverbindungsnachweis',
	'ccc'					=> 'Kundenkontrollzentrum',
	'cdr' 					=> 'Evn',
	'cdr_discarded_calls' 	=> "EVN: Vertragsnr oder -ID ':contractnr' in Datenbank nicht gefunden - :count Telefongespräche der Telefonnr :phonenr mit einem Preis von :price :currency können nicht zugeordnet werden.",
	'cdr_missing_phonenr' 	=> "EVN: Einzelverbindungsnachweise mit Telefonnummer :phonenr gefunden, die nicht in der Datenbank existiert. :count Telefongespräche mit einem Preis von :price :currency können nicht zugeordnet werden.",
	'cdr_missing_reseller_data' => 'EVN konnte nicht geladen werden. Reseller Daten in Environment Datei fehlen!',
	'cdr_offset' 			=> 'Zeitdifferenz EVN zu Rechnung in Monaten',
	'close' 				=> 'Schliessen',
	'contract_early_cancel' => 'Möchten Sie den Vertrag wirklich vor Tariflaufzeitende :date kündigen?',
	'conn_info_err_create' 	=> 'Fehler beim Erstellen des PDF - Siehe LogFile!',
	'conn_info_err_template' => 'Das Template konnte nicht gelesen werden - Bitte überprüfen Sie ob es unter Unternehmen gesetzt ist!',
	'cpe_log_error' 		=> 'ist nicht beim Server registriert - Kein Logeintrag gefunden',
	'cpe_not_reachable' 	=> 'aber via PING nicht erreichbar (ICMP kann herstellerabhängig vom Router geblockt werden)',
	'cpe_fake_lease'		=> 'Der DHCP Server hat kein Lease für den Endpunkt angelegt, weil dessen IP Adresse statisch vergeben ist und der Server diesen somit nicht verfolgen muss. Das folgende Lease wurde lediglich als Referenz manuell generiert:',
	'D' 					=> 'Tag|Tage',
	'dashbrd_ticket' 		=> 'Neue mir zugewiesene Tickets',
	'device_probably_online' =>	':type ist wahrscheinlich online',
	'eom' 					=> 'zum Monatsende',
	'envia_no_interaction' 	=> 'Keine Envia Vorgänge, die eine Bearbeitung benötigen',
	'envia_interaction'	 	=> 'Envia Vorgang benötigt eine Bearbeitung|Envia Vorgänge benötigen Bearbeitung',
	'home' 					=> 'Startseite',
	'indices_unassigned' 	=> 'Einer/Einige der zugewiesenen Indizes konnten keinem Parameter zugeordnet werden! Sie werden somit aktuell nur nicht genutzt. Sie können gelöscht oder für später behalten werden. Vergleichen Sie dazu die Parameterliste im Netzelement mit der Liste der Indizes!',
	'item_credit_amount_negative' => 'Ein negativer Betrag bei Gutschriften wird zur Lastschrift für den Kunden! Sind Sie sicher, dass der Betrag dem Kunde abgezogen werden soll?',
	'invoice' 				=> 'Rechnung',
	'Invoices'				=> 'Rechnungen',
	'log_out'				=> 'Ausloggen',
	'M' 					=> 'Monat|Monate',
	'Mark solved'			=> 'Als gelöst markeren?',
	'missing_product' 		=> 'Fehlendes Produkt!',
	'modem_eventlog_error'	=> 'Modem Eventlog nicht gefunden',
	'modem_force_restart_button_title' => 'Startet nur das Modem neu. Speichert keine geänderten Daten!',
	'modem_monitoring_error'=> 'Möglicherweise war das Modem bis jetzt nicht online. Beachten Sie, dass Diagramme erst ab
		dem Zeitpunkt verfügbar sind, von dem an das Modem online ist. Wurden alle Diagramme unsauber angezeigt, könnte es
		sich um ein größeres Problem, wie eine Fehlkonfiguration von Cacti, handeln. Wenden Sie sich dazu an ihren Administrator.',
	'modem_no_diag'			=> 'Keine Diagramme verfügbar',
	'modem_lease_error'		=> 'Kein gültiger Lease gefunden',
	'modem_lease_valid' 	=> 'Modem hat einen gültigen Lease',
	'modem_log_error' 		=> 'Modem ist nicht beim Server registriert - Kein Logeintrag gefunden',
	'modem_configfile_error'=> 'Modem Konfigurationsdatei nicht gefunden',
	'modem_offline'			=> 'Modem ist Offline',
	'modem_restart_error' 		=> 'Das Modem konnte nicht neugestartet werden! (offline?)',
	'modem_restart_success_cmts' => "Das Modem wurde erfolgreich über das CMTS neugestartet",
	'modem_restart_success_direct' => "Das Modem wurde erfolgreich direkt über SNMP neugestartet",
	'modem_save_button_title' 	=> 'Speichert geänderte Daten. Berechnet die Geoposition neu, wenn Adressdaten geändert wurden (und weist es ggf. einer neuen MPR hinzu). Baut das Configfile und startet das Modem neu, wenn sich mindestens eines der folgenden Einträge geändert hat: Öffentliche IP, Netzwerkzugriff, Configfile, QoS, MAC-Adresse',
	'modem_statistics'		=> 'Anzahl Online / Offline Modems',
	'month' 				=> 'Monat',
	'mta_configfile_error'	=> 'MTA Konfigurationsdatei nicht gefunden',
	'noCC'					=> 'Keine Kostenstelle zugewiesen',
	'oid_list' 				=> 'Achtung: OIDs, die nicht bereits in der Datenbank existieren werden nicht beachtet! Bitte laden Sie das zuvor zugehörige MibFile hoch!',
	'password_change'		=> 'Passwort ändern',
	'password_confirm'		=> 'Password bestätigen',
	'phonenumber_nr_change_hlkomm' => 'Beim Ändern dieser Nummer können die angefallen Gespräche der alten Nummer nicht mehr diesem Vertrag angerechnet werden, da HL Komm bzw. Pyur nur die Telefonnummer in den Einzelverbindungsnachweisen mitschickt. Bitte ändern Sie diese Nummer nur, wenn es sich um eine Testnummer handelt oder Sie sicher sind, dass keine Gespräche mehr abgerechnet werden.',
	'phonenumber_overlap_hlkomm' => 'Diese Nummer existiert bereits oder hat im/in den letzten :delay Monat(en) exisiert. Da HL Komm oder Pyur in den Einzelverbindungsnachweisen nur die Telefonnummer mitsendet, wird es nicht möglich sein getätigte Anrufe zum richtigen Vertrag zuzuweisen! Das kann zu falschen Abrechnungen führen. Bitte fügen Sie die Nummer nur hinzu, wenn es sich um eine Testnummer handelt oder Sie sicher sind, dass keine Gespräche mehr abgerechnet werden.',
	'show_ags' 				=> 'Zeige AG Auswahlfeld auf Vertragsseite',
	'snmp_query_failed' 	=> 'SNMP Query failed for following OIDs: ',
	'sr_repeat' 			=> 'Wiederholen für SEPA-Konto:', // Settlementrun repeat
	'upload_dependent_mib_err' => "Bitte Laden Sie zuvor die ':name' hoch! (Die zugehörigen OIDs können sonst nicht geparsed werden)",
	'user_settings'			=> 'User Settings',
	'user_glob_settings'	=> 'Globale Nutzereinstellungen',

	'voip_extracharge_default' => 'Preisaufschlag Telefonie Standard in %',
	'voip_extracharge_mobile_national' => 'Preisaufschlag Telefonie Mobilfunk national in %',
	'Y' 					=> 'Jahr|Jahre',
	'Assign Role'				=> 'Rollen zuweisen',
];
