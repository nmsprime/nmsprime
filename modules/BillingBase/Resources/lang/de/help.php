<?php

return [
    'BillingBase' => [
        'adaptItemStart'    => 'Wenn aktiviert wird bei Änderung des Beginns eines Vertrags der Beginn aller zugehörigen Posten automatisch auf das Datum des Vertragsbeginns gesetzt.',
        'cdr_offset'        => "ACHTUNG: Eine Erhöhung der Differenz führt bei bereits vorhandenen Daten im nächsten Abrechnungslauf zu überschriebenen EVNs - Stellen Sie sicher, dass diese gesichert/umbenannt wurden!\n\n1 - wenn die Einzelverbindungsnachweise vom Juni zu den Rechnungen vom Juli gehören; 0 - wenn beide für den selben Monat abgerechnet werden; 2 - wenn die Einzelverbindungsnachweise vom Januar zu den Rechnungen vom März gehören.",
        'cdr_retention'     => 'Anzahl der Monate, die Einzelverbindungsnachweise gespeichert werden dürfen/müssen.',
        'extra_charge'      => 'Aufschlag auf Einkaufspreis in %. Aktuell nur in Kombination mit Provider HlKomm nutzbar!',
        'fluid_dates'       => 'Aktivieren Sie diese Checkbox wenn Sie Tarife mit ungewissem Start- und/oder Enddatum eintragen möchten. In dem Fall werden 2 weitere Checkboxen (Gültig ab fest, Gültig bis fest) auf der Posten-Seite angezeigt. Weitere Erklärungen finden Sie neben diesen Feldern!',
        'invoiceNrStart'    => 'Rechnungsnummer startet jedes neue Jahr mit dieser Nummer.',
        'ItemTermination'   => 'Erlaubt es Kunden gebuchte Produkte nur bis zum letzten Tag des Monats zu kündigen.',
        'MandateRef'        => "Eine Vorlage kann mit SQL-Spalten des Auftrags oder mit der Mandat-Tabelle erstellt werden - mögliche Felder: \n",
        'rcd'               => 'Globales Fälligkeits- und Buchungsdatum. Dieses kann auf Vertragsebene auch spezifisch für den Vertrag gesetzt werden.',
        'showAGs'           => 'Fügt eine Auswahlliste mit Ansprechpartnern von Antennengemeinschaften zur Vertragsseite hinzu. Die Liste muss als Textdatei im Storage hinterlegt werden. Siehe Quellcode!',
        'SplitSEPA'         => 'Bitte aktivieren, wenn die SEPA.xml in unterschiedliche XML-Dateien aufgeteilt werden soll, die dann jeweils nur einen SEPA-Mandatsstatus enthalten (FRST | RCUR | ...).',
    ],
    'product' => [
        'recordMonthly' => 'Bei Aktivieren der Checkbox wird der Posten monatlich auf der Rechnung aufgeführt. Der Einzelpreis wird automatisch berechnet. Dies ist nur für jährlich abzurechnende Produkte relevant.',
    ],
    'sepaAccount' => [
        'invoiceHeadline'   => 'Ersetzt die Überschrift der Rechnung, die für diese Kostenstelle erstellt wird.',
        'invoiceText'       => 'Der Text der vier verschiedenen \'Rechnungstext\'-Felder wird automatisch in Abhängigkeit von Gesamtkosten und SEPA-Mandat gewählt und wird in der entsprechenden Rechnung für den Kunden festgelegt. Es ist möglich, alle Datenfeld-Schlüssel der Rechnungsklasse als Platzhalter in Form von {Feldname} zu verwenden, um eine Art von Vorlage zu erstellen. Diese werden durch den Ist-Wert der Rechnung ersetzt.',
    ],
    'sepaMandate' => [
        'holder' => 'Der Name des Kontoinhabers darf kein Semikolon enthalten.',
    ],
    'texTemplate'           => 'TeX Vorlage',
];
