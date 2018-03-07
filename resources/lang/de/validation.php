<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| such as the size rules. Feel free to tweak each of these messages.
	|
	*/

	'accepted'             => ':attribute muss akzeptiert werden.',
	'active_url'           => ':attribute ist keine gültige Internet-Adresse.',
	'after'                => ':attribute muss ein Datum nach dem :date sein.',
	'alpha'                => ':attribute darf nur aus Buchstaben bestehen.',
	'alpha_dash'           => ':attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen. Umlaute (ä, ö, ü) und Eszett (ß) sind nicht erlaubt.',
	'alpha_num'            => ':attribute darf nur aus Buchstaben und Zahlen bestehen.',
	'array'                => ':attribute muss ein Array sein.',
	"available" 			=> "Kein Eintrag in Konfigurationsdateien verfügbar - Füllen Sie dieses Feld bitte aus",
	'before'               => ':attribute muss ein Datum vor dem :date sein.',
	'between'              => [
		'numeric' => ':attribute muss zwischen :min & :max liegen.',
		'file'    => ':attribute muss zwischen :min & :max Kilobytes groß sein.',
		'string'  => ':attribute muss zwischen :min & :max Zeichen lang sein.',
		'array'   => ':attribute muss zwischen :min & :max Elemente haben.',
	],
	'boolean'              => ":attribute muss entweder 'true' oder 'false' sein.",
	'confirmed'            => ':attribute stimmt nicht mit der Bestätigung überein.',
	'date'                 => ':attribute muss ein gültiges Datum sein.',
	"dateornull"			=> "Tragen Sie bitte ein gültiges Datum ein oder lassen Sie das Feld frei",
	'date_format'          => ':attribute entspricht nicht dem gültigen Format für :format.',
	'different'            => ':attribute und :other müssen sich unterscheiden.',
	'digits'               => ':attribute muss :digits Stellen haben.',
	'digits_between'       => ':attribute muss zwischen :min und :max Stellen haben.',
	'email'                => ':attribute Format ist ungültig.',
	'exists'               => 'Der gewählte Wert für :attribute ist ungültig.',
	'filled'               => ':attribute muss ausgefüllt sein.',
	'image'                => ':attribute muss ein Bild sein.',
	'in'                   => 'Der gewählte Wert für :attribute ist ungültig.',
	'integer'              => ':attribute muss eine ganze Zahl sein.',
	'ip'                   => ':attribute muss eine gültige IP-Adresse sein.',
	'json'                 => ':attribute muss ein gültiger JSON-String sein.',
	"mac"				   => "Bitte geben Sie eine gültige MAC Adresse in der Form: aa:bb:cc:dd:ee:ff ein",
	'max'                  => [
		'numeric' => ':attribute darf maximal :max sein.',
		'file'    => ':attribute darf maximal :max Kilobytes groß sein.',
		'string'  => ':attribute darf maximal :max Zeichen haben.',
		'array'   => ':attribute darf nicht mehr als :max Elemente haben.',
	],
	'mimes'                => ':attribute muss den Dateityp :values haben.',
	'min'                  => [
		'numeric' => ':attribute muss mindestens :min sein.',
		'file'    => ':attribute muss mindestens :min Kilobytes groß sein.',
		'string'  => ':attribute muss mindestens :min Zeichen lang sein.',
		'array'   => ':attribute muss mindestens :min Elemente haben.',
	],
	'not_in'               => 'Der gewählte Wert für :attribute ist ungültig.',
	'not_null'               => 'Dieses Feld muss gesetzt werden (nicht 0)',
	'null_if'               => 'Muss Null sein',
	'numeric'              => ':attribute muss eine Zahl sein.',
	'period' 				=> ':attribute hat ein ungültiges Format.',
	'regex'                => ':attribute Format ist ungültig.',
	'required'             => ':attribute muss ausgefüllt sein.',
	'required_if'          => ':attribute muss ausgefüllt sein, wenn :other :value ist.',
	'required_unless'      => ':attribute muss ausgefüllt sein, wenn :other nicht :values ist.',
	'required_with'        => ':attribute muss angegeben werden, wenn :values ausgefüllt wurde.',
	'required_with_all'    => ':attribute muss angegeben werden, wenn :values ausgefüllt wurde.',
	'required_without'     => ':attribute muss angegeben werden, wenn :values nicht ausgefüllt wurde.',
	'required_without_all' => ':attribute muss angegeben werden, wenn keines der Felder :values ausgefüllt wurde.',
	'same'                 => ':attribute und :other müssen übereinstimmen.',
	'size'                 => [
		'numeric' => ':attribute muss gleich :size sein.',
		'file'    => ':attribute muss :size Kilobyte groß sein.',
		'string'  => ':attribute muss :size Zeichen lang sein.',
		'array'   => ':attribute muss genau :size Elemente haben.',
	],
	'string'               => ':attribute muss ein String sein.',
	'timezone'             => ':attribute muss eine gültige Zeitzone sein.',
	'unique'               => ':attribute ist schon vergeben.',
	'url'                  => 'Das Format von :attribute ist ungültig.',

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'custom'               => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => [

	"docsis"             		=> ":attribute",
	"ip"                   		=> "Dies ist keine gültige IP-Adresse im Format: 192.168.0.255",
	"ip_in_range"		   		=> "Die angege IP-Adresse ist nicht innerhalb des spezifizierten Bereichs",
	"ip_larger"			   		=> "Die angege IP-Adresse muss aufgrund der Angaben aus anderen Feldern eine höhere Nummer besitzen",
	"mac"				   		=> ":attribute muss eine gültige MAC-Adresse in der Form \'aa:bb:cc:dd:ee:ff\' sein",
	"netmask"               	=> "Die angegebene Netzmaske ist nicht korrekt",
	'not_null'              	=> 'Dieses Feld muss ausgefüllt sein (nicht 0)',
	'null_if'					=> 'Wert muss 0 sein',

		//
	"netmask"               	=> "Die angegebene Netzmaske ist nicht korrekt",
	'not_null'              	=> 'Dieses Feld muss ausgefüllt sein (nicht 0)',
	'null_if'					=> 'Wert muss 0 sein',

		//
	],

];
