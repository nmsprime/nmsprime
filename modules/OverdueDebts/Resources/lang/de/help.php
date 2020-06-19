<?php

return [
    'debt' => [
        'total_fee' => 'Inklusive Mahngebühr.',
    ],
    'fee' => 'Gebühr des Providers für Rücklastschriften.',
    'import' => [
        'amount' => 'Ab diesem summierten, ausstehenden Betrag wird der Internetzugriff des Kunden beim Import der offenen Posten gesperrt.',
        'debts' => 'Erreicht die Anzahl offener Posten des Kunden die hier eingetragene Zahl, wird der Internetzugriff des Kunden beim OP-Import (aus der Finanzbuchhaltungssoftware) automatisch gesperrt.',
        'indicator' => 'Wird beim OP-Import ein offener Posten mit diesem Mahnkennzeichen importiert, wird der Internetzugriff des Kunden automatisch gesperrt.',
    ],
    'testCsvUpload' => 'Der Internetzugriff der Kunden wird nicht gesperrt. Es werden nur die OPs hinzugefügt und angezeigt welche Kunden gesperrt werden würden.',
    'total' => 'Wenn aktiviert, gilt die oben eingetragene Gebühr als absoluter Betrag - entspricht also immer der Gesamtgebühr für die Rücklastschrift. Der Betrag wird somit nicht extra zu der von den Banken erhobenen Gebühr addiert.',
];
