<?php

return [

    'cdr' => [
        'missingEvn' => 'EVN von :provider fehlt.',
        'wrongArgument' => 'Fehlerhaftes Datumsformat. Befehl wird für letzten Monat ausgeführt. (Standard)',
    ],
    'installationDefaultModel' => 'Die eingetragenen Daten erscheinen auf den Rechnungen Ihrer Kunden - diese Daten wurden als Default automatisch bei der Installation hinzugefügt.',
    'modemAddressToInvoice' => 'Es wird nur die Adresse eines Modems des zugehörigen Vertrages auf der Rechnung angezeigt.',
    'settlementrun' => [
        'state' => [
            'clean' => 'Bereinige Verzeichnis...',
            'concatInvoices' => 'Füge Rechnungen zusammen',
            'concatPostalInvoices' => 'Füge postalische Rechnungen zusammen...',
            'createInvoices' => 'Erstelle Rechnungen',
            'finish' => 'Fertig',
            'getData' => 'Hole Daten...',
            'parseCdr' => 'Parse Einzelverbindungsnachweisdatei(en)...',
            'loadData' => 'Daten werden geladen...',
            'zip' => 'Erstelle ZIP-Datei...',
        ],
    ],
    'zip' => [
        'noPostal' => 'Keine Rechnungen für postalischen Versand vorgesehen. Stop.',
    ],

];
