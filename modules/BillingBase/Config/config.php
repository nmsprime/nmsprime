<?php

return [
	'name' => 'BillingBase',
	'MenuItems' => [
		'Product List' => [
			'link'	=> 'Product.index',
			'icon'	=> 'fa-th-list'
		],
		'SEPA Accounts' => [
			'link'	=> 'SepaAccount.index',
			'icon'	=> 'fa-credit-card'
		],
		'Settlement Run' => [
			'link'	=> 'SettlementRun.index',
			'icon'	=> 'fa-file-pdf-o'
		],
		'Cost Center' => [
			'link'	=> 'CostCenter.index',
			'icon'	=> 'fa-creative-commons'
		],
		'Companies' => [
			'link'	=> 'Company.index',
			'icon'	=> 'fa-industry'
		],
		'Salesmen' => [
			'link'	=> 'Salesman.index',
			'icon' => 'fa-vcard'
		],
		'Number Range' => [
			'link'	=> 'NumberRange.index',
			'icon' => 'fa-globe'
		]
	]
];
