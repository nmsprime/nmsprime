<?php

return [

    /*
    |--------------------------------------------------------------------------
    | All other Language Lines - TODO: split descriptions and messages?
    |--------------------------------------------------------------------------
    */
    'Active'					=> 'Aktiv',
    'Active?'					=> 'Aktiv?',
    'Additional Options'		=> 'Zusätzliche Optionen',
    'Address Line 1'			=> 'Adresszeile 1',
    'Address Line 2'			=> 'Adresszeile 2',
    'Address Line 3'			=> 'Adresszeile 3',
    'Assigned'					=> 'Zugewiesen -',
    'BIC'						=> 'BIC',
    'Bank Account Holder'		=> 'Kontoinhaber',
    'Birthday'					=> 'Geburtstag',
    'City'						=> 'Stadt',
    'Choose KML file'			=> 'Wähle KML file',

    'Company'					=> 'Firma',
    'Contract Number'			=> 'Vertragsnummer',
    'Contract Start'			=> 'Vertragsbeginn',
    'Contract End'				=> 'Vertragsende',
    'Contract valid' 			=> 'Vertrag gültig',
    'Contract'					=> 'Vertrag',
    'Contract List'				=> 'Vertragsliste',
    'Contracts'					=> 'Verträge',
    'International prefix'		=> 'Ländervorwahl',
    'Country code'				=> 'Ländercode',
    // Descriptions of Form Fields in Edit/Create
    'accCmd_error_noCC' 	=> 'Dem Vertrag :contract_nr [ID :contract_id] wurde keine Kostenstelle zugewiesen. Für den Kunde wird keine Rechnung erstellt.',
    'accCmd_invoice_creation_deactivated' => 'Bei folgenden Verträgen wurde die Rechnungserstellung deaktiviert: :contractnrs',
    'Create'					=> 'Erstellen',
    'accCmd_processing' 	=> 'Der Abrechnungslauf wird erstellt. Bitte warten Sie bis der Prozess abgeschlossen ist.',
    'Date of installation address change'	=> 'Datum der Änderung der Installationsaddresse',
    'Delete'					=> 'Löschen',
    'Day'						=> 'Tag',
    'Description'				=> 'Beschreibung',
    'Device'					=> 'Gerät',
    'accCmd_notice_CDR' 	=> 'Dem Vertrag :contract_nr [ID :contract_id] werden Einzelverbindungsnachweise abgerechnet, obwohl kein gültiger Telefontarif vorliegt. (Kommt durch verzögerte Abrechnung nach Beenden des Tarifs vor)',
    'Device List'				=> 'Geräteliste',
    'Device Type'				=> 'Gerätetyp',
    'Device Type List'			=> 'Gerätetypenliste',
    'Devices'					=> 'Geräte',
    'DeviceTypes'				=> 'Gerätetypen',
    'District'					=> 'Ortsteil',
    'Edit'						=> 'Ändern -',
    'Edit '						=> 'Ändere ',
    'Endpoints'					=> 'Endpunkte',
    'Endpoints List'			=> 'Endpunktliste',
    'Entry'						=> 'Eintrag',
    'alert' 				=> 'Achtung!',
    'ALL' 					=> 'ALLE',
    'E-Mail Address'			=> 'Email-Adresse',
    'First IP'					=> 'Erste IP',
    'Firstname'					=> 'Vorname',
    'Fixed IP'					=> 'Statische IP',
    'Force Restart'				=> 'Neustart erzwingen',
    'Geocode origin'			=> 'Herkunft der Geodaten',
    'IBAN'						=> 'IBAN',
    'Internet Access'			=> 'Internetzugriff',
    'Inventar Number'			=> 'Inventarnummer',
    'Call Data Record'		=> 'Einzelverbindungsnachweis',
    'IP address'				=> 'IP Adresse',
    'Language'					=> 'Sprache',
    'Lastname'					=> 'Nachname',
    'Last IP'					=> 'Letzte IP',
    'ccc'					=> 'Kundenkontrollzentrum',
    'MAC Address'				=> 'MAC Adresse',
    'Main Menu'					=> 'Hauptmenü',
    'Maturity' 					=> 'Laufzeit',
    'cdr' 					=> 'Evn',
    'cdr_discarded_calls' 	=> "EVN: Vertragsnr oder -ID ':contractnr' in Datenbank nicht gefunden - :count Telefongespräche der Telefonnr :phonenr mit einem Preis von :price :currency können nicht zugeordnet werden.",
    'cdr_missing_phonenr' 	=> 'EVN: Einzelverbindungsnachweise mit Telefonnummer :phonenr gefunden, die nicht in der Datenbank existiert. :count Telefongespräche mit einem Preis von :price :currency können nicht zugeordnet werden.',
    'cdr_missing_reseller_data' => 'EVN konnte nicht geladen werden. Reseller Daten in Environment Datei fehlen!',
    'cdr_offset' 			=> 'Zeitdifferenz EVN zu Rechnung in Monaten',
    'close' 				=> 'Schliessen',
    'contract_early_cancel' => 'Möchten Sie den Vertrag wirklich vor Tariflaufzeitende :date kündigen?',
    'contract_nr_mismatch'  => 'Es konnte keine nächste Vertragsnummer gefunden werden, da die Datenbankabfrage fehl schlug. Die Ursache dafür liegt bei folgenden Verträgen, die eine Vertragsnummer haben, die nicht zur Kostenstelle passt: :nrs. Bitte tragen Sie die korrekte Kostenstelle ein oder lassen Sie eine neue Vertragsnummer für die Verträge vergeben.',
    'contract_numberrange_failure' => 'Keine freie Vertragsnummer für die gewählte Kostenstelle gefunden.',
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
    'Month'						=> 'Monat',
    'envia_interaction'	 	=> 'Envia Vorgang benötigt eine Bearbeitung|Envia Vorgänge benötigen Bearbeitung',
    'Net'						=> 'Netz',
    'Netmask'					=> 'Netzmaske',
    'Network Access'			=> 'Netzwerkzugriff',
    'no' 						=> 'nein',
    'Number'					=> 'Nummer',
    'Options'					=> 'Optionen',
    'or: Upload KML file'		=> 'oder lade KML hoch',
    'Parent Device Type'		=> 'Eltern Gerätetyp',
    'Parent Object'				=> 'Eltern Objekt',
    'Period of Notice' 			=> 'Kündigungsfrist',
    'Password'					=> 'Passwort',
    'Confirm Password'					=> 'Passwort bestätigen',
    'Phone'						=> 'Telefon',
    'Phone ID next month'		=> 'Telefon ID nächsten Monat',
    'Phonenumber'				=> 'Telefonnummer',
    'Phonenumbers'				=> 'Telefonnummern',
    'Phonenumbers List'			=> 'Telefonnummernliste',
    'Postcode'					=> 'Postleitzahl',
    'Prefix Number'				=> 'Vorwahl',
    'Price'						=> 'Preis',
    'Public CPE'				=> 'Öffentliches CPE',
    'QoS next month'			=> 'QoS nächsten Monat',
    'Real Time Values'			=> 'Echtzeitwerte',
    'Remember Me'				=> 'An diesem Gerät eingeloggt bleiben',
    'Salutation'				=> 'Anrede',
    'Save'						=> 'Speichern',
    'Save All'					=> 'Alle Speichern',
    'Save / Restart'			=> 'Speichern / Neustart',
    'Serial Number'				=> 'Seriennummer',
    'Sign me in' 				=> 'Anmelden',
    'State'						=> 'Status',
    'Street'					=> 'Straße',
    'Typee'						=> 'Type',
    'Unexpected exception' 		=> 'Unerwarteter Fehler',
    'US level' 					=> 'US Pegel',
    'Username'					=> 'Nutzername',
    'Users'						=> 'Nutzer',
    'Vendor'					=> 'Hersteller',
    'Year'						=> 'Jahr',
    'yes' 						=> 'ja',
    'home' 						=> 'Startseite',
    'indices_unassigned' 		=> 'Einer/Einige der zugewiesenen Indizes konnten keinem Parameter zugeordnet werden! Sie werden somit aktuell nur nicht genutzt. Sie können gelöscht oder für später behalten werden. Vergleichen Sie dazu die Parameterliste im Netzelement mit der Liste der Indizes!',
    'item_credit_amount_negative' => 'Ein negativer Betrag bei Gutschriften wird zur Lastschrift für den Kunden! Sind Sie sicher, dass der Betrag dem Kunde abgezogen werden soll?',
    'invoice' 					=> 'Rechnung',
    'Global Config'				=> 'Globale Konfiguration',
    'GlobalConfig'				=> 'Globale Konfiguration',
    'VOIP'						=> 'VOIP',
    'Customer Control Center'	=> 'Kundenkontrollzentrum',
    'Provisioning'				=> 'Provisioning',
    'BillingBase'				=> 'Billing Base Konfiguration',
    'Ccc' 						=> 'CCC Konfiguration',
    'HfcBase' 					=> 'HfcBase Konfiguration',
    'ProvBase' 					=> 'ProvBase Konfiguration',
    'ProvVoip' 					=> 'ProvVoip Konfiguration',
    'ProvVoipEnvia' => 'ProvVoipEnvia Konfiguration',
    'HFC'						=> 'HFC',
    'Rank'						=> 'Rang',
    'Assign Users'				=> 'Benutzer zuweisen',
    'Invoices'					=> 'Rechnungen',
    'Ability'					=> 'Fähigkeit',
    'Allow'						=> 'Erlauben',
    'Allow to'					=> 'Erlaube',
    'Forbid'					=> 'Verbieten',
    'Forbid to'					=> 'Verbiete',
    'Save Changes'				=> 'Änderungen speichern',
    'Manage'					=> 'Verwalten',
    'View'						=> 'Ansehen',
    'Create'					=> 'Create',
    'Update'					=> 'Ändern',
    'Delete'					=> 'Delete',
    'Help'						=> 'Hilfe',
    'All abilities'				=> 'Alle Fähigkeiten',
    'View everything'			=> 'Alle Seiten ansehen',
    'Use api'					=> 'API benutzen',
    'See income chart'			=> 'Einkommensdiagramm ansehen',
    'View analysis pages of modems'	=> 'Analyseseite der Modems aufrufen',
    'View analysis pages of cmts' => 'Analyseseite der CMTS aufrufen',
    'Download settlement runs'	=> 'Abrechnungsläufe downloaden',
    'Not allowed to acces this user' => 'Zugriff auf diesen Nutzer ist nicht gestattet',
    'log_out'				=> 'Ausloggen',
    'System Log Level'			=> 'System Logging Stufe',
    'Headline 1'				=> 'Überschrift Kopfzeile',
    'Headline 2'				=> 'Überschrift Navigationsleiste',
    'M' 					=> 'Monat|Monate',
    'Mark solved'			=> 'Als gelöst markeren?',
    'missing_product' 		=> 'Fehlendes Produkt!',
    'modem_eventlog_error'	=> 'Modem Eventlog nicht gefunden',
    'modem_force_restart_button_title' => 'Startet nur das Modem neu. Speichert keine geänderten Daten!',
    'CDR retention period' 		=> 'Aufbewahrungsfrist für Einzelverbindungsnachweise',
    'Day of Requested Collection Date'	=> 'Monatlicher Abrechnungstag',
    'Tax in %'					=> 'Mehrwertsteuer in %',
    'Invoice Number Start'		=> 'Start Nummerierung Rechnungen',
    'Split Sepa Transfer-Types'	=> 'SEPA-Transfertypen aufteilen?',
    'Mandate Reference'			=> 'Mandatsrefferenz',
    'e.g.: String - {number}'	=> 'z.Bsp.: Sring - {Nummer}',
    'Item Termination only end of month'=> 'Posten nur am ende des Monats kündigen?',
    'Language for settlement run' => 'Sprache für Abrechnungslauf',
    'Uncertain start/end dates for tariffs' => 'Ungewisse Tarif-Start-/Enddaten',
    'modem_monitoring_error'=> 'Möglicherweise war das Modem bis jetzt nicht online. Beachten Sie, dass Diagramme erst ab
		dem Zeitpunkt verfügbar sind, von dem an das Modem online ist. Wurden alle Diagramme unsauber angezeigt, könnte es
		sich um ein größeres Problem, wie eine Fehlkonfiguration von Cacti, handeln. Wenden Sie sich dazu an ihren Administrator.',
    'Connection Info Template'	=> 'Vorlage für Verbindungsinformationen',
    'Upload Template'			=> 'Vorlage hochladen',
    'SNMP Read Only Community'	=> 'SNMP Read Only Community',
    'SNMP Read Write Community'	=> 'SNMP Read Write Community',
    'Provisioning Server IP'	=> 'Provisionierungsserver',
    'Domain Name for Modems'	=> 'Modem Domain Name',
    'Notification Email Address'=> 'Benachrichtigungs E-Mail Adresse',
    'DHCP Default Lease Time'	=> 'DHCP Standard Lease Zeit',
    'DHCP Max Lease Time'		=> 'DHCP Maximale Lease Zeit',
    'Start ID Contracts'		=> 'Start Nummerierung Verträge',
    'Start ID Modems'			=> 'Start Nummerierung Modems',
    'Start ID Endpoints'		=> 'Start Nummerierung Endpunkte',
    'Downstream rate coefficient' => 'Übertragungsratenkoeffizient Vorwärtskanal',
    'Upstream rate coefficient' => 'Übertragungsratenkoeffizient Rückwärtskanal',
    'modem_no_diag'			=> 'Keine Diagramme verfügbar',
    'Start ID MTA´s'			=> 'Start Nummerierung MTA\'s',
    'modem_lease_error'		=> 'Kein gültiger Lease gefunden',
    'modem_lease_valid' 	=> 'Modem hat einen gültigen Lease',
    'modem_log_error' 		=> 'Modem ist nicht beim Server registriert - Kein Logeintrag gefunden',
    'modem_configfile_error'=> 'Modem Konfigurationsdatei nicht gefunden',
    'Academic Degree'			=> 'Akademischer Titel',
    'modem_offline'			=> 'Modem ist Offline',
    'Contract number'			=> 'Vertragsnummer',
    'Contract Nr'				=> 'Vertragsnr',
    'Contract number legacy'	=> 'Historische Vertragsnummer',
    'Cost Center'				=> 'Kostenstelle',
    'Create Invoice'			=> 'Rechnung erstellen',
    'Customer number'			=> 'Kundennummer',
    'Customer number legacy'	=> 'Historische Kundennummer',
    'Department'				=> 'Abteilung',
    'End Date' 					=> 'Enddatum',
    'House Number'				=> 'Hausnummer',
    'House Nr'					=> 'Hausnr',
    'Salesman'					=> 'Verkäufer',
    'Start Date' 				=> 'Startdatum',
    'modem_restart_error' 		=> 'Das Modem konnte nicht neugestartet werden! (offline?)',
    'Contact Persons' 			=> 'Antennengemeinschaft/Kontakt',
    'modem_restart_success_cmts' => 'Das Modem wurde erfolgreich über das CMTS neugestartet',
    'Accounting Text (optional)'=> 'Verwendungszweck (optional)',
    'Cost Center (optional)'	=> 'Kostenstelle (optional)',
    'Credit Amount' 			=> 'Gutschrift - Betrag',
    'modem_restart_success_direct' => 'Das Modem wurde erfolgreich direkt über SNMP neugestartet',
    'Item'						=> 'Posten',
    'Items'						=> 'Posten',
    'modem_save_button_title' 	=> 'Speichert geänderte Daten. Berechnet die Geoposition neu, wenn Adressdaten geändert wurden (und weist es ggf. einer neuen MPR hinzu). Baut das Configfile und startet das Modem neu, wenn sich mindestens eines der folgenden Einträge geändert hat: Öffentliche IP, Netzwerkzugriff, Configfile, QoS, MAC-Adresse',
    'Product'					=> 'Produkt',
    'Start date' 				=> 'Startdatum',
    'Active from start date' 	=> 'Ab Startdatum aktiv',
    'Valid from'				=> 'Startdatum',
    'Valid to'					=> 'Enddatum',
    'Valid from fixed'			=> 'Ab Startdatum aktiv',
    'Valid to fixed'			=> 'Festes Enddatum',
    'modem_statistics'		=> 'Anzahl Online / Offline Modems',
    'Configfile'				=> 'Konfigurationsdatei',
    'Mta'						=> 'MTA',
    'month' 				=> 'Monat',
    'Configfiles'				=> 'Konfigurationsdatei',
    'Choose Firmware File'		=> 'Firmware-Datei auswählen',
    'Config File Parameters'	=> 'Parameter für die Konfigurationsdatei',
    'or: Upload Firmware File'	=> 'oder: Firmware-Datei hochladen',
    'Parent Configfile'			=> 'Übergeordnete Konfigurationsdatei',
    'Public Use'				=> 'Öffentliche Nutzung',
    'mta_configfile_error'	=> 'MTA Konfigurationsdatei nicht gefunden',
    'IpPool'						=> 'IP-Bereich',
    'SNMP Private Community String'	=> 'SNMP privater Community Strin',
    'SNMP Public Community String'	=> 'SNMP öffentlicher Community String',
    'noCC'					=> 'Keine Kostenstelle zugewiesen',
    'IP-Pools'					=> 'IP-Bereich',
    'Type of Pool'				=> 'Art des IP-Bereichs',
    'IP network'				=> 'IP Netz',
    'IP netmask'				=> 'IP Netzmaske',
    'IP router'					=> 'IP Router',
    'oid_list' 				=> 'Achtung: OIDs, die nicht bereits in der Datenbank existieren werden nicht beachtet! Bitte laden Sie das zuvor zugehörige MibFile hoch!',
    'Phone tariffs'				=> 'Telefontarife',
    'External Identifier'		=> 'Externer Identifikator',
    'Usable'					=> 'Verfügbar?',
    'password_change'		=> 'Passwort ändern',
    'password_confirm'		=> 'Password bestätigen',
    'phonenumber_nr_change_hlkomm' => 'Beim Ändern dieser Nummer können die angefallen Gespräche der alten Nummer nicht mehr diesem Vertrag angerechnet werden, da HL Komm bzw. Pyur nur die Telefonnummer in den Einzelverbindungsnachweisen mitschickt. Bitte ändern Sie diese Nummer nur, wenn es sich um eine Testnummer handelt oder Sie sicher sind, dass keine Gespräche mehr abgerechnet werden.',
    'phonenumber_overlap_hlkomm' => 'Diese Nummer existiert bereits oder hat im/in den letzten :delay Monat(en) exisiert. Da HL Komm oder Pyur in den Einzelverbindungsnachweisen nur die Telefonnummer mitsendet, wird es nicht möglich sein getätigte Anrufe zum richtigen Vertrag zuzuweisen! Das kann zu falschen Abrechnungen führen. Bitte fügen Sie die Nummer nur hinzu, wenn es sich um eine Testnummer handelt oder Sie sicher sind, dass keine Gespräche mehr abgerechnet werden.',
    'show_ags' 				=> 'Zeige AG Auswahlfeld auf Vertragsseite',
    'snmp_query_failed' 	=> 'SNMP Query failed for following OIDs: ',
    'Billing Cycle'				=> 'Billing Cycle',
    'Bundled with VoIP product?'=> 'Bundled with VoIP product?',
    'Price (Net)'				=> 'Price (Net)',
    'Number of Cycles'			=> 'Number of Cycles',
    'Product Entry'				=> 'Product Entry',
    'Qos (Data Rate)'			=> 'Qos (Data Rate)',
    'with Tax calculation ?'	=> 'with Tax calculation ?',
    'Phone Sales Tariff'		=> 'Phone Sales Tariff',
    'Phone Purchase Tariff'		=> 'Phone Purchase Tariff',
    'sr_repeat' 			=> 'Wiederholen für SEPA-Konto:', // Settlementrun repeat
    'Account Holder'			=> 'Account Holder',
    'Account Name'				=> 'Account Name',
    'Choose Call Data Record template file'	=> 'Choose Call Data Record template file',
    'Choose invoice template file'			=> 'Choose invoice template file',
    'CostCenter'				=> 'CostCenter',
    'Creditor ID'				=> 'Creditor ID',
    'Institute'					=> 'Institute',
    'Invoice Headline'			=> 'Invoice Headline',
    'Invoice Text for negative Amount with Sepa Mandate'	=> 'Invoice Text for negative Amount with Sepa Mandate',
    'Invoice Text for negative Amount without Sepa Mandate'	=> 'Invoice Text for negative Amount without Sepa Mandate',
    'Invoice Text for positive Amount with Sepa Mandate'	=> 'Invoice Text for positive Amount with Sepa Mandate',
    'Invoice Text for positive Amount without Sepa Mandate'	=> 'Invoice Text for positive Amount without Sepa Mandate',
    'SEPA Account'				=> 'SEPA Account',
    'SepaAccount'				=> 'SepaAccount', // siehe Companies
    'upload_dependent_mib_err' => "Bitte Laden Sie zuvor die ':name' hoch! (Die zugehörigen OIDs können sonst nicht geparsed werden)",
    'Upload CDR template'		=> 'Upload CDR template',
    'Upload invoice template'	=> 'Upload invoice template',
    'user_settings'			=> 'User Settings',
    'user_glob_settings'	=> 'Globale Nutzereinstellungen',

    'voip_extracharge_default' => 'Preisaufschlag Telefonie Standard in %',
    'voip_extracharge_mobile_national' => 'Preisaufschlag Telefonie Mobilfunk national in %',
    'General'				=> 'General',
    'Verified'				=> 'Verified',
    'tariff'				=> 'tariff',
    'item'					=> 'item',
    'sepa'					=> 'sepa',
    'no_sepa'				=> 'no_sepa',
    'Call_Data_Records'		=> 'Call_Data_Records',
    'Y' 					=> 'Jahr|Jahre',
    'accounting'			=> 'accounting',
    'booking'				=> 'booking',
    'DD'					=> 'DD',
    'DC'					=> 'DC',
    'salesmen_commission'	=> 'salesmen_commission',
    'Assign Role'				=> 'Rollen zuweisen',
    'Load Data...' 			=> 'Load Data...',
    'Clean up directory...' => 'Clean up directory...',
    'Associated SEPA Account'	=> 'Associated SEPA Account',
    'Month to create Bill'		=> 'Month to create Bill',
    'Choose logo'			=> 'Choose logo',
    'Directorate'			=> 'Directorate',
    'Mail address'			=> 'Mail address',
    'Management'			=> 'Management',
    'Registration Court 1'	=> 'Registration Court 1',
    'Registration Court 2'	=> 'Registration Court 2',
    'Registration Court 3'	=> 'Registration Court 3',
    'Sales Tax Id Nr'		=> 'Sales Tax Id Nr',
    'Tax Nr'				=> 'Tax Nr',
    'Transfer Reason for Invoices'	=> 'Transfer Reason for Invoices',
    'Upload logo'			=> 'Upload logo',
    'Web address'			=> 'Web address',
    'Zip'					=> 'Zip',
    'Commission in %'		=> 'Commission in %',
    'Product List'			=> 'Product List',
    'Already recurring ?' 	=> 'Already recurring ?',
    'Date of Signature' 	=> 'Date of Signature',
    'Disable temporary' 	=> 'Disable temporary',
    'Reference Number' 		=> 'Reference Number',
    'Bank Bank Institutee' 		=> 'Bank Institute',
    'Contractnr'			=> 'Contractnr',
    'Create Invoices' 		=> 'Create Invoices',
    'Invoicenr'				=> 'Invoicenr',
    'Calling Number'		=> 'Calling Number',
    'Called Number'			=> 'Called Number',
    'Target Month'			=> 'Target Month',
    'Date'					=> 'Date',
    'Count'					=> 'Count',
    'Tax'					=> 'Tax',
    'RCD'					=> 'RCD',
    'Currency'				=> 'Currency',
    'Gross'					=> 'Gross',
    'Net'					=> 'Net',
    'MandateID'				=> 'MandateID',
    'MandateDate'			=> 'MandateDate',
    'Commission in %'		=> 'Commission in %',
    'Total Fee'				=> 'Total Fee',
    'Commission Amount'		=> 'Commission Amount',
    'Zip Files' 			=> 'Zip Files',
    'Concatenate invoices'  => 'Concatenate invoices',
    'primary'				=> 'primary',
    'secondary'				=> 'secondary',
    'disabled'				=> 'disabled',
    'Value (deprecated)'          => 'Value (deprecated)',
    'Priority (lower runs first)' => 'Priority (lower runs first)',
    'Priority' 				=> 'Priority',
    'Title' 				=> 'Title',
    'Created at'			=> 'Created at',
    'Activation date'       => 'Activation date',
    'Deactivation date'     => 'Deactivation date',
    'SIP domain'            => 'SIP domain',
    'Created at' 			=> 'Created at',
    'Last status update'	=> 'Last status update',
    'Last user interaction' => 'Last user interaction',
    'Method'				=> 'Method',
    'Ordertype ID'			=> 'Ordertype ID',
    'Ordertype'				=> 'Ordertype',
    'Orderstatus ID'		=> 'Orderstatus ID',
    'Orderstatus'			=> 'Orderstatus',
    'Orderdate'				=> 'Orderdate',
    'Ordercomment'			=> 'Ordercomment',
    'Envia customer reference' => 'Envia customer reference',
    'Envia contract reference' => 'Envia contract reference',
    'Contract ID'			=> 'Contract ID',
    'Phonenumber ID'		=> 'Phonenumber ID',
    'Related order ID'		=> 'Related order ID',
    'Related order type'	=> 'Related order type',
    'Related order created' => 'Related order created',
    'Related order last updated' => 'Related order last updated',
    'Related order deleted'	=> 'Related order deleted',
    'Envia Order'			=> 'Envia Order',
    'Document type'			=> 'Document type',
    'Upload document'		=> 'Upload document',
    'Call Start'			=> 'Call Start',
    'Call End'				=> 'Call End',
    'Call Duration/s'		=> 'Call Duration/s',
    'min. MOS'				=> 'min. MOS',
    'Packet loss/%'			=> 'Packet loss/%',
    'Jitter/ms'				=> 'Jitter/ms',
    'avg. Delay/ms'			=> 'avg. Delay/ms',
    'Caller (-> Callee)'	=> 'Caller (-> Callee)',
    '@Domain'				=> '@Domain',
    'min. MOS 50ms'			=> 'min. MOS 50ms',
    'min. MOS 200ms'		=> 'min. MOS 200ms',
    'min. MOS adaptive 500ms'	=> 'min. MOS adaptive 500ms',
    'avg. MOS 50ms'			=> 'avg. MOS 50ms',
    'avg. MOS 200ms'		=> 'avg. MOS 200ms',
    'avg. MOS adaptive 500ms'	=> 'avg. MOS adaptive 500ms',
    'Received Packets'		=> 'Received Packets',
    'Lost Packets'			=> 'Lost Packets',
    'avg. Delay/ms'			=> 'avg. Delay/ms',
    'avg. Jitter/ms'		=> 'avg. Jitter/ms',
    'max. Jitter/ms'		=> 'max. Jitter/ms',
    '1 loss in a row'		=> '1 loss in a row',
    '2 losses in a row'		=> '2 losses in a row',
    '3 losses in a row'		=> '3 losses in a row',
    '4 losses in a row'		=> '4 losses in a row',
    '5 losses in a row'		=> '5 losses in a row',
    '6 losses in a row'		=> '6 losses in a row',
    '7 losses in a row'		=> '7 losses in a row',
    '8 losses in a row'		=> '8 losses in a row',
    '9 losses in a row'		=> '9 losses in a row',
    'PDV 50ms - 70ms'		=> 'PDV 50ms - 70ms',
    'PDV 70ms - 90ms'		=> 'PDV 70ms - 90ms',
    'PDV 90ms - 120ms'		=> 'PDV 90ms - 120ms',
    'PDV 120ms - 150ms'		=> 'PDV 120ms - 150ms',
    'PDV 150ms - 200ms'		=> 'PDV 150ms - 200ms',
    'PDV 200ms - 300ms'		=> 'PDV 200ms - 300ms',
    'PDV >300 ms'			=> 'PDV >300 ms',
    'Callee (-> Caller)'	=> 'Callee (-> Caller)',
    'Credit'                    => 'Credit',
    'Other'                     => 'Other',
    'Once'                      => 'Once',
    'Monthly'                   => 'Monatlich',
    'Quarterly'                 => 'Vierteljährlich',
    'Yearly'                    => 'Yearly',
    'NET'                       => 'NET',
    'CMTS'                      => 'CMTS',
    'DATA'                      => 'DATA',
    'CLUSTER'                   => 'CLUSTER',
    'NODE'                      => 'NODE',
    'AMP'                       => 'AMP',
    'None'                      => 'None',
    'Null'                      => 'Null',
    'generic'                   => 'generic',
    'network'                   => 'network',
    'vendor'                    => 'vendor',
    'user'                      => 'user',
    'Yes'                       => 'Yes',
    'No'                        => 'No',
    'telephony_only'            => 'Muss aktiv sein, wenn der Kunde nur Telefonie und keinen Internetzugriff haben soll. Dies hat Einfluss auf NetworkAccess und MaxCPE im Modem Configfile.',
];
