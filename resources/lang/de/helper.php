<?php

return [
    /*
  * Authentication and Base
  */
    'translate'                 => 'Sie können dabei helfen NMS Prime zu übersetzen. Besuchen Sie:',
    'assign_role'                   => 'Diesem Nutzer eine oder mehrere Rollen zuweisen. Nutzer ohne Rolle können das NMS nicht verwenden, da sie keine Berechtigungen haben.',
    'assign_users'                  => 'Einen oder mehrere Nutzer zu dieser Rolle zuweisen. Die Veränderung ist im GuiLog des Users nicht sichtbar, sondern nur hier.',
    'assign_rank'                   => 'Der Rang einer Rolle gibt die Fähigkeiten der Rolle an, andere Nutzer zu bearbeiten.<br \\>Es werden werte von 0 bis 100 angenommen. (höher ist besser)<br \\>Hat ein Nutzer mehrere Rollen, gilt der höchste Rang.<br \\> Wenn die Fähigkeit gesetzt ist um Nutzer bearbeiten zu können, wird außerdem der Rang geprüft. Nur wenn der Rang des Bearbeiters höher ist, wird die Anfrage genehmigt. Weiterhin können beim erstellen und bearbeiten von Nutzern nur Rollen mit dem gleichen oder einem niedrigeren Rang vergeben werden.',
    'All abilities'                 => 'Diese Fähigkeit erlaubt alle Autorisierungsanfragen, außer es wurden explizit Fähigkeiten verboten. Diese Fähigkeit ist besonders nützlich, wenn eine Rolle sehr viel mit wenigen Ausnahmen "darf". Das Verbieten der Fähigkeit wurde deaktiviert, da es keine Auswirkungen hat (nur ausgewählte Fähigkeiten sind erlaubt). Wenn diese Fähigkeit nicht gesetzt ist, müssen alle Berechtigungen von Hand gesetzt werden. Das Ändern dieser Fähigkeit, wenn schon viele andere Fähigkeiten gesetzt sind, kann bis zu einer Minute dauern.',
    'countryCode'               => 'ISO 3166 ALPHA-2 (zwei Buchstaben). Wird für die Bestimmung der Koordinaten benötigt, kann aber frei gelassen werden, wenn der global eingetragene Standard-Ländercode genutzt werden soll.',
    'View everything'           => 'Diese Fähigkeit erlaubt es alle Seiten zu besuchen. Das Verbieten der Fähigkeit wurde deaktiviert, da in diesem Fall alle Fähigkeiten verboten werden sollten. Diese Fähigkeit ist hauptsächlich zur Hilfe da, um schnell Rechte für Gäste oder Benutzer mit nur sehr wenigen Privilegien zu setzen.',
    'Use api'                   => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die API Routen mithilfe von "Basic Auth". Als Benutzername muss die E-Mail, welche im Profil hinterlegt ist genutzt werden.',
    'See income chart'          => 'Diese Fähigkeit erlaubt oder verbietet die Anzeige des Einkommensdiagramms im Dashboard.',
    'View analysis pages of modems' => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die Analysisseiten der Modems.',
    'View analysis pages of netgw' => 'Diese Fähigkeit erlaubt oder verbietet den Zugriff auf die Analysisseite der NetGws.',
    'Download settlement runs'  => 'Diese Fähigkeit erlaubt oder verbietet den Download der Abrechnungsläufe. Wenn das Verwalten von Abrechnungsläufen verboten ist, hat diese Einstellung keine Auswirkung.',
    /*
  * Index Page - Datatables
  */
    'SortSearchColumn'              => 'Diese Spalte kann nicht sortiert oder durchsucht werden.',
    'PrintVisibleTable'             => 'Druckt den aktuell sichtbaren Bereich der Tabelle. Um alles zu drucken bitte im Filter \\"Alle\\" auswählen. Das Laden kann einige Sekunden dauern.',
    'ExportVisibleTable'            => 'Exportiert den aktuell sichtbaren Bereich der Tabelle. Um alles zu exportieren bitte im Filter \\"Alle\\" auswählen. Das Laden kann einige Sekunden dauern.',
    'ChangeVisibilityTable'         => 'Mit dieser Option können Spaten ein-/ausgeblendet werden.',

    // GlobalConfig
    'ISO_3166_ALPHA-2'              => 'ISO 3166 ALPHA-2 (zwei Zeichen, z.B. „DE“). Genutzt in Formularen mit Adressdaten um das Land anzugeben.',
    'PasswordReset'           => 'Diese Einstellung bestimmt, in welchem Intervall die Nutzer des Administrationsbereiches zum Ändern ihres Passworts aufgefordert werden. Setzen Sie diesen Wert auf 0, um Passwörter unendlich lang gültig zu halten.',

    //CompanyController
    'Company_Management'            => 'Trennung der Namen durch Komma.',
    'Company_Directorate'           => 'Trennung der Namen durch Komma.',
    'Company_TransferReason'        => 'Vorlage aller Rechnungsklassen als Datenfeld-Schlüssel - Vertrags- und Rechnungsnummer sind standardmäßig ausgewählt.',
    'conn_info_template'            => 'TeX Vorlage für das Anschlussinformationsblatt. (Kann auf der Kundenvertragsseite erstellt werden)',

    //CostCenterController
    'CostCenter_BillingMonth'       => 'Abrechnungsmonat für jährliche Posten. Gilt für den Monat für den die Rechnungen erstellt werden. Standard: 6 (Juni) - wenn nicht festgelegt. Bitte seien Sie vorsichtig beim Ändern innerhalb des Jahres: das Resultat könnten fehlende Zahlungen sein!',

    //ItemController
    'Item_ProductId'                => 'Alle Felder außer dem Abrechnungszyklus müssen vor eine Änderung des Produkts gelöscht werde! Andernfalls können die Produkte in den meisten Fällen nicht gespeichert werden.',
    'Item_ValidFrom'                => 'Für einmalige (Zusatz-)Zahlungen kann das Feld genutzt werden, um die Zahlung zu teilen - nur  Jahr und Monat (jjjj.mm) werden berücksichtigt.',
    'Item_ValidFromFixed'           => 'Dieses Feld ist standardmäßig gesetzt! Deaktivieren Sie diese Checkbox, wenn der Tarif zum gewünschten Startdatum inaktiv bleiben soll (z.B. falls auf einen Portierungstermin gewartet wird). Der Tarif startet damit nicht und wird auch nicht abgerechnet bis Sie die Checkbox aktivieren. Bei Erreichen des Startdatums wird dieses außerdem jeden Tag erneut auf den darauffolgenden Tag gesetzt. Info: Feste Termine werden nicht durch externe Aufträge (z.B. vom Telefonie-Provider) aktualisiert.',
    'Item_validTo'                  => 'Es ist möglich hier die Anzahl der gültigen Monate anzugeben - z.B. \'12M\' für zwölf Monate. Bei monatlich abgerechneten Produkten werden diese 12 Monate zum Startdatum addiert. Bei Start am 2018-05-04 wird das Enddatum der 2019-05-04 sein. Einmalig zu zahlende Produkte, deren Zahlung geteilt wird, werden dann 12 mal abgerechnet - das Enddatum wäre im Beispiel dann 2019-04-31.',
    'Item_ValidToFixed'             => 'Dieses Feld ist standardmäßig gesetzt! Deaktivieren Sie diese Checkbox, wenn das Enddatum noch ungewiss ist. Der Tarif bleibt damit aktiv und wird weiterhin abgerechnet bis Sie die Checkbox aktivieren. Bei Erreichen des Enddatums wird dieses außerdem jeden Tag erneut auf den darauffolgenden Tag gesetzt. Info: Feste Termine werden nicht durch externe Aufträge (z.B. vom Telefonie-Provider) aktualisiert.',
    'Item_CreditAmount'             => 'Nettobetrag, der dem Kunde gutgeschrieben werden soll. Achtung: Ein negativer Betrag wird dem Kunde abgezogen!',

    //ProductController
    'product' => [
        'bundle'                => 'Ist der Tarif mit dem Voip-Tarif gebündelt, wird die Gesamtvertragslaufzeit eines Kunden nur anhand des Internet-Tarifs bestimmt. Anderenfalls bestimmt der Tarif (Voip oder Internet) darüber, der zuletzt begonnen hat.',
        'markon'                => 'Extra Aufschlag auf die Einzelverbindungen. Der prozentuale Aufschlag wird aktuell nur zu Opennumbers EVNs addiert.',
        'maturity_min'          => 'Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'maturity'              => 'Laufzeitverlängerung nach der Mindestlaufzeit. <br> Die Gesamtlaufzeit wird automatisch um diese Zeit verlängert, wenn der Tarif nicht vor der Kündigungsfrist gekündigt wurde. Default: 1 Monat. Wird keine Laufzeit angegeben, wird das Laufzeitende des Tarifs immer auf den letzten Tag des Monats gesetzt. <br><br> Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'Name'                  => 'Für Kredite ist es möglich einen Typ zuzuweisen, indem der Typname dem Namen des Kredits angefügt wird - z.B.: \'Kredit Gerät\'',
        'pod'                   => 'Beispiele: 14D (14 Tage), 3M (Drei Monate), 1Y (Ein Jahr)',
        'proportional'          => 'Setzen Sie diesen Haken, wenn Posten, die innerhalb des aktuellen Abrechnungszyklus beginnen, anteilig berechnet werden sollen. Somit würde bei einem monatlich abzurechnenden Produkt mit Beginn in der Mitte des Monats im aktuellen Abrechnungszyklus nur die Hälfte des vollen Preises abgerechnet werden.',
        'Type'                  => 'Alle Felder außer dem Abrechnungszyklus müssen vor einer Änderung des Produkts gelöscht werden! Andernfalls können die Produkte in den meisten Fällen nicht gespeichert werden.',
        'deprecated'            => 'Setzen Sie diesen Haken, wenn das Produkt veraltet ist. Dadurch kann es nicht mehr beim Erstellen/Ändern von Posten ausgewählt werden.',
    ],
    'Product_Number_of_Cycles'      => 'Achtung! Für alle Produkte, die in einem wiederkehrenden Zyklus bezahlt werden steht der Preis für jede einzelne Zahlung. Für Produkte, die einmalig bezahlt werden wird der Preis durch die Anzahl der Zyklen geteilt.',

    //SalesmanController
    'Salesman_ProductList'          => 'Geben Sie alle Produkttypen an, für die der Verkäufer eine Provision erhalten soll. Möglich sind:',

    // SepaMandate
    'sm_cc'                         => 'Tragen Sie hier eine Kostenstelle ein, um über dieses Konto nur Posten/Produkte abzurechnen, die derselben Kostenstelle zugewiesen sind. Dem Konto eines SEPA-Mandats ohne zugewiesene Kostenstelle werden alle entstandenen Kosten abgebucht, die keinem anderen Mandat zugordnet werden können. Anmerkung: Entstehen Kosten, die keinem SEPA-Mandat zugeordnet werden können, wird angenommen, dass diese in bar beglichen werden.',
    'sm_recur'                      => 'Aktivieren, wenn vor dem Anlegen bereits Transaktionen von diesem Konto vorgenommen worden. Setzt den Status auf Folgelastschrift. Anmerkung: Wird nur bei der ersten Lastschrift beachtet!',

    // SettlementrunController
    'settlement_verification'       => 'Die Rechnungen der Kunden werden nur mit aktivierter Checkbox angezeigt. Der Haken kann nur gesetzt werden, wenn der letzte Rechnungslauf für ALLE SEPA-Konten ausgeführt wurde (damit keine Änderungen missachtet werden). Info: Mit aktivierter Checkbox kann der Abrechnungslauf nicht wiederholt werden.',

    /*
  * MODULE: Dashboard
  */
    'next'                          => 'Nächster Schritt: ',
    'set_isp_name'                  => 'Namen des Internetanbieters setzen',
    'create_netgw'                  => 'Erstes NetGw/CMTS anlegen',
    'create_cm_pool'                => 'Ersten Kabelmodem IP-Bereich anlegen',
    'create_cpepriv_pool'           => 'Ersten privaten CPE IP-Bereich anlegen',
    'create_qos'                    => 'Erstes QoS Profil anlegen',
    'create_product'                => 'Erstes Abrechnungsprodukt anlegen',
    'create_configfile'             => 'Erste Konfigurationsdatei anlegen',
    'create_sepa_account'           => 'Erstes SEPA-Konto anlegen',
    'create_cost_center'            => 'Erste Kostenstelle anlegen',
    'create_contract'               => 'Ersten Vertrag anlegen',
    'create_nominatim'              => 'E-Mail Adresse (OSM_NOMINATIM_EMAIL) in /etc/nmsprime/env/global.env eintragen, um die Geolokalisation für Modems zu ermöglichen',
    'create_nameserver'             => 'Den Nameserver in /etc/resolv.conf auf 127.0.0.1 setzen und sicherstellen, dass dieser nicht via DHCP überschrieben wird (siehe DNS und PEERDNS in /etc/sysconfig/network-scripts/ifcfg-*)',
    'create_modem'                  => 'Erstes Modem anlegen',

    /*
  * MODULE: HfcReq
  */
    'netelementtype_reload'         => 'In Sekunden. 0s zum Deaktivieren des Autoreloads. Nachkommastellen möglich.',
    'netelementtype_time_offset'    => 'In Sekunden. Nachkommastellen möglich.',
    'undeleteables'                 => 'Net & Cluster können weder gelöscht werden, noch kann der Name geändert werden, da die Existenz dieser Typen Vorraussetzung für die Erzeugung des Entitity-Relationship-Diagramms ist.',

    /*
  * MODULE: HfcSnmp
  */
    'mib_filename'                  => 'Der Dateiname setzt sich aus MIB Name und Revision zusammen. Existiert bereits ein MIB-File mit selbem Dateiname und ist identisch, kann dieses nicht erneut angelegt werden.',
    'oid_link'                      => 'Gehe zu OID Einstellungen',
    'oid_table'                     => 'INFO: Dieser Parameter gehört zu einer Tabellen-OID. Durch Hinzufügen von SubOIDs or Indizes werden die SnmpWerte nur für diese abgefragt. Neben einem besseren Überblick auf der Einstellungen-Übersicht des Netzelements kann dies deren Aufrufgeschwindigkeit deutlich beschleunigen.',
    'parameter_3rd_dimension'       => 'Durch Aktivieren der Checkbox wird dieser Parameter zur Einstellungsseite hinter einem Element in der Tabelle hinzugefügt.',
    'parameter_diff'                => 'Bei Aktivierter Checkbox wird nur die Differenz des aktuell zum zuletzt abgefragten Wert angezeigt.',
    'parameter_divide_by'           => 'Durch Angabe von OIDs wird dieser Wert prozentual zur Summe der zu diesen OIDs abgefragten Werte dargestellt. Dies funktioniert vorerst nur in SubOIDs exakt definierter Tabellen. Die hier angegebenen OIDs müssen als Parameter in der SubOID-List eingetragen sein.',
    'parameter_indices'             => 'Durch Angabe einer durch Kommas getrennten Liste der Indizes der Tabellenreihen, werden die SnmpWerte nur für diese Einträge abgefragt.',
    'parameter_html_frame'          => 'Durch Eintragen einer zweistelligen Framenummer wird der Parameter dem Frame auf der Seite zugewiesen. Durch das Eintragen unterschiedlicher Framenummern bei den Parmetern wird die Seite gemäß der Nummer aufgeteilt. Dabei entspricht die erste Zahl der Zeile und die zweite Zahl der Spalte. Auf SubOIDs von Tabellen hat die Framenummer keinen Einfluss (aber auf 3. Dimension-Parameter!).',
    'parameter_html_id'             => 'Durch Eintragen einer ID wird der Parameter in Reihe zu den anderen Parametern gemäß der ID (aufsteigend) angeordnet. In Tabellen kann durch setzen der ID im Sub-Parameter die Spaltenanordnung verändert werden.',

    /*
  * MODULE: ProvBase
  */
    'contract' => [
        'valueDate' => 'Tag im Monat des separaten Buchungsdatums. Überschreibt das Fälligkeitsdatum aus den globalen Konfigurationen für diesen Vertrag in der SEPA XML. Die Bank bucht den Betrag dann an diesem Tag ab.',
    ],
    'rate_coefficient'              => 'MaxRateSustained wird mit diesem Wert multipliziert, um den Nutzer eine höhere (> 1.0) Übertragungsrate als gebucht zu gewähren.',
    'additional_modem_reset'           => 'Zeigt einen zusätzlichen Modem Reset Button an, um das Modem ohne Hilfe des NetGws direkt per SNMP neu zu starten.',
    'auto_factory_reset'            => 'Führt für TR-069 CPEs automatisch einen Werksreset nach Änderung relevanter Einstellungen, die eine Neuprovisionierung erfordern, durch. (Änderung der Telefonnummern, PPPoE Zugangsdaten bzw. der Konfigurationsdatei)',
    'acct_interim_interval'         => 'Die Zeit in Sekunden zwischen vom NAS gesendeten Interim Updates (PPPoE).',
    'openning_new_tab_for_modem' => 'Öffnet die Modem-Edit Seite in einem neuen Fenster (Topographie).',
    'ppp_session_timeout'           => 'In Sekunden. Bei einem Wert von 0 werden die PPP Sessions nicht getrennt.',
    //ModemController
    'Modem_InternetAccess'          => 'Internetzugriff für CPEs. (MTAs werden nicht beachtet und gehen immer online, wenn alle restlich notwendigen Konfigurationen korrekt vorgenommen wurden) - Achtung: Mit Billingmodul wird diese Checkbox während der nächtlichen Prüfung (nur) bei Tarifänderung überschrieben.',
    'Modem_InstallationAddressChangeDate'   => 'Datum der Änderung der Installationsadresse. Wenn nur lesbar existiert bereits ein offener Auftrag.',
    'Modem_GeocodeOrigin'           => 'Quelle der Geodaten. Falls hier „n/a“ steht konnte die Adresse nicht aufgelöst werden. Bei manueller Änderung der Geodaten wird der aktuelle Nutzer eingetragen.',
    'netGwSupportState' => [
        'full-supported' => 'Mehr als 95% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'restricted' => 'Zwischen 80%-95% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'not-supported' => 'Weniger als 80% der integrierten Module wurden als unterstützte Geräte gelistet.',
        'verifying' => 'Weniger als 80% der integrierten Module wurden als unterstützte Geräte gelistet. Das netGw befindet aber noch in der Verifikationszeitspanne von 6 Wochen.',
    ],
    'contract_number'               => 'Achtung - Kundenkennwort wird bei Änderung automatisch geändert!',
    'mac_formats'                   => "Erlaubte Formate (Groß-/Kleinschreibung nicht unterschieden):\n\n1) AA:BB:CC:DD:EE:FF\n2) AABB.CCDD.EEFF\n3) AABBCCDDEEFF",
    'fixed_ip_warning'              => 'Die Nutzung fester IP Adressen ist nicht empfohlen, da hierbei Modems und ihre zugehörigen CPEs nicht mehr zwsichen NetGws verschoben werden können. Anstatt den Endkunden die jeweilige IP Adresse zu nennen, sollte ihnen der Hostname mitgeteilt werden, da sich dieser nicht ändert.',
    'addReverse'                    => 'Zum Setzen eines zusätzlichen Reverse DNS Eintrags, z.B. für E-Mail Server',
    'modem_update_frequency'        => 'Dieses Feld wird einmal täglich aktualisiert.',
    'modemSupportState' => [
        'full-supported' => 'Das Modem wurde als unterstütztes Gerät gelistet.',
        'not-supported' => 'Das Modem wurde nicht als unterstütztes Gerät gelistet',
        'verifying' => 'Das Modem wurde noch nicht als unterstütztes Gerät gelistet, die Verifikationszeitspanne von 6 Wochen ist aber noch nicht abgelaufen.',
    ],
    'enable_agc'                    => 'Aktiviere automatische Verstärkungsregelung in Rückkanalrichtung.',
    'agc_offset'                    => 'Verschiebung des automatischen Verstärkungsregelungwertes in Rückkanalrichtung in dB. (Vorgabewert: 0.0)',
    'configfile_count'              => 'Die Zahl in Klammern zeigt an, wie häufig die jeweilige Konfigurationsdatei bereits verwendet wird.',
    'has_telephony'                 => 'Muss aktiv sein, wenn der Kunde Telefonie haben soll, aber kein Internet hat. Das Flag kann aktuell nicht genutzt werden, um die Telefonie bei Verträgen mit Internet zu deaktivieren. Dazu muss das MTA gelöscht oder die Telefonnummer deaktiviert werden. Info: Die Einstellung hat Einfluss auf NetworkAccess und MaxCPE im Modem Configfile - siehe Modem-Analyse im Tab \'Configfile\'',
    'ssh_auto_prov'                 => 'Periodisches Ausführen eines auf das OLT angepasstes Skript um ONTs automatisch online zu bringen.',
    'modem' => [
        'configfileSelect' => 'Es ist nicht möglich den Typ des Modems über das Configfile zu ändern (z.B. von \'cm\' zu \'tr-69\'). Bitte löschen Sie das Modem dazu und erstellen Sie ein neues!',
    ],

    /*
  * MODULE: ProvVoip
  */
    //PhonenumberManagementController
    'PhonenumberManagement_activation_date' => 'Wird als Wunschtermin der Schaltung zum Provider geschickt und für die Ermittlung des Aktiv-Status der Rufnummer verwendet.',
    'PhonenumberManagement_deactivation_date' => 'Wird als Wunschtermin der Abschaltung zum Provider geschickt und für die Ermittlung des Aktiv-Status der Rufnummer verwendet.',
    'PhonenumberManagement_CarrierIn' => 'Bei eingehender Portierung auf vorherigen Provider setzen.',
    'PhonenumberManagement_CarrierInWithEnvia' => 'Bei eingehender Portierung auf vorherigen Provider setzen. Bei Beantragung einer neuen Rufnummer setzen Sie dieses Feld auf envia TEL.',
    'PhonenumberManagement_EkpIn' => 'Bei eingehender Portierung auf vorherigen Provider setzen.',
    'PhonenumberManagement_EkpInWithEnvia' => 'Bei eingehender Portierung auf vorherigen Provider setzen. Bei Beantragung einer neuen Rufnummer setzen Sie dieses Feld auf envia TEL.',
    'PhonenumberManagement_TRC' => 'Nur zur Info: Sperrklassenänderungen müssen beim aktuellen Provider durchgeführt werden.',
    'PhonenumberManagement_TRCWithEnvia' => 'Sperrklassenänderungen müssen auch bei envia TEL vorgenommen werden (Update VoIP account)!',
    'PhonenumberManagement_ExternalActivationDate' => 'Datum der Aktivierung beim Provider.',
    'PhonenumberManagement_ExternalActivationDateWithEnvia' => 'Datum der Aktivierung bei envia TEL.',
    'PhonenumberManagement_ExternalDeactivationDate' => 'Datum der Deaktivierung beim Provider.',
    'PhonenumberManagement_ExternalDeactivationDateWithEnvia' => 'Datum der Deaktivierung bei envia TEL.',
    'PhonenumberManagement_Autogenerated' => 'Dieses Management wurde automatisch erzeugt. Bitte sämtliche Werte überprüfen und nach evtl. Korrektur den Haken entfernen',
    /*
  * MODULE VoipMon
  */
    'mos_min_mult10'                => 'Minimaler Mean Opionion Score während des Anrufs',
    'caller'                        => 'Betrachtung der Anrufrichtung von Anrufer zu Angerufenem',
    'a_mos_f1_min_mult10'           => 'Minimaler Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 50ms',
    'a_mos_f2_min_mult10'           => 'Minimaler Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 200ms',
    'a_mos_adapt_min_mult10'        => 'Minimaler Mean Opionion Score während des Anrufs mit einem adaptiven Jitter-Buffer von 500ms',
    'a_mos_f1_mult10'               => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 50ms',
    'a_mos_f2_mult10'               => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem festen Jitter-Buffer von 200ms',
    'a_mos_adapt_mult10'            => 'durchschnittl. Mean Opionion Score während des Anrufs mit einem adaptiven Jitter-Buffer von 500ms',
    'a_sl1' => 'Anzahl der Pakete, welche einen aufeinander folgenden Paketverlust während des Anrufs aufweisen',
    'a_sl9' => 'Anzahl der Pakete, welche neun aufeinander folgende Paketverluste während des Anrufs aufweisen',
    'a_d50' => 'Anzahl der Pakete, welche eine Paketverzögerung (Packet Delay Variation - z.B. Jitter) zwischen 50ms and 70ms aufweisen',
    'a_d300' => 'Anzahl der Pakete, welche eine Paketverzögerung (Packet Delay Variation - z.B. Jitter) von über 300ms aufweisen',
    'called' => 'Betrachtung der Anrufrichtung von Angerufenem zum Anrufer',
    /*
 * Module Ticketsystem
 */
    'assign_user' => 'Zuweisen eines Users zu einem Ticket.',
    'mail_env'    => 'Nächster Schritt: Host/Nutzernamen/Passwort in /etc/nmsprime/env/global.env eintragen, um Emails im Bezug auf Tickets zu erhalten.',
    'noReplyMail' => 'Die E-Mail-Adresse, die als Absender angezeigt werden soll, wenn Tickets geändert/erstellt werden. Die Adresse muss nicht existieren. Z.B. example@example.com',
    'noReplyName' => 'Der Name, der als Absender angezeigt werden soll, wenn Tickets geändert/erstellt werden. Z.B: NMS Prime',
    'ticket_settings' => 'Nächster Schritt: Den Namen und die E-Mail-Adresse des Noreply Absenders in der Systemkonfiguration unter dem Reiter Tickets angeben.',
    'carrier_out'      => 'Carriercode des zukünftigen Vertragspartners der Rufnummer. Wenn leer, wird die Rufnummer gelöscht.',
    'ticketDistance' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Distanz eines Technikers zur Störstelle. (Standard: 1)',
    'ticketModemcount' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Anzahl der betroffenen Modems. (Standard: 1)',
    'ticketOpentickets' => 'Multiplikator für das automatische Zuweisen von Tickets. Je höher dieser Wert ist, umso wichtiger ist die Anzahl der bereits zugewiesenen und in Bearbeitung befindlichen Tickets. (Standard: 1)',

    /*
     * Start alphabetical order
     */
    'endpointMac' => 'Kann für alle PPPoE provisionierte Modems frei gelassen werden (dann wird der PPP Nutzername statt der MAC genutzt). Mit DHCP kann das Feld für IPv4 frei gelassen werden. Dann bekommen alle Geräte hinter dem Modem die spezifizierte IP, wobei nur das Gerät, dass sich zuletzt gemeldet hat, eine funktionierende IP-Konnektivität erhält. Für IPv6 ist dies noch nicht implementiert - bitte geben Sie hier immer die MAC des CPE an, das eine öffentliche oder feste IP erhalten soll!',
];
