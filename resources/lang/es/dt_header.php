<?php
return [
	// Index DataTable Header
	// Auth
	'users.login_name' => 'Nombre de usuario',
	'users.first_name' => 'Nombre',
	'users.last_name' => 'Apellido',
	'roles.title' => 'Función',
	'roles.rank' => 'Nivel',
	'roles.description' => 'Descripción 	',
	// GuiLog
	'guilog.created_at' => 'Hora',
	'guilog.username' => 'Usuario',
	'guilog.method' => 'Acción',
	'guilog.model' => 'Modelo',
	'guilog.model_id' => 'Modelo ID',
	// Company
	'company.name' => 'Nombre De La Empresa',
	'company.city' => 'Ciudad',
	'company.phone' => 'Número de teléfono celular',
	'company.mail' => 'Email',
	// Costcenter
	'costcenter.name' => 'Precio',
	'costcenter.number' => 'Importe',
	//Invoices
	'invoice.type' => 'Tipo',
	'invoice.year' => 'Año',
	'invoice.month' => 'Mes',
	//Item //**

	// Product
	'product.type' => 'Tipo',
	'product.name' => 'Nombre de producto',
	'product.price' => 'Precio',
	// Salesman
	'salesman.id' => 'ID',
	'salesman_id' 		=> 'ID del vendedor',
	'salesman_firstname' => 'Nombre',
	'salesman_lastname' => 'Apellido',
	'commission in %' 	=> 'Comisión en %',
	'contract_nr' 		=> 'Contrato',
	'contract_name' 	=> 'Cliente',
	'contract_start' 	=> 'Contrato inicio',
	'contract_end' 		=> 'Contrato fin',
	'product_name' 		=> 'Producto',
	'product_type' 		=> 'Tipo de producto',
	'product_count' 	=> 'Conteo',
	'charge' 			=> 'Precio',
	'salesman_commission' => 'Comisión',
	'sepaaccount_id' 	=> 'Cuenta de SEPA',
	// SepaAccount
	'sepaaccount.name' => "Nombre de cuenta",
	'sepaaccount.institute' => 'Institución',
	'sepaaccount.iban' => 'IBAN',
	// SepaMandate
	'sepamandate.sepa_holder' => 'Titular de la cuenta',
	'sepamandate.sepa_valid_from' => 'Válido desde',
	'sepamandate.sepa_valid_to' => 'Válido hasta',
	'sepamandate.reference' => 'Cuenta de referencia',
	// SettlementRun
	'settlementrun.year' => 'Año',
	'settlementrun.month' => 'Mes',
	'settlementrun.created_at' => 'Creando el',
	'verified' => 'Verificada?',
	// MPR
	'mpr.name' => 'Nombre',
	// NetElement
	'netelement.id' => 'ID',
	'netelement.name' => 'Netelement',
	'netelement.ip' => 'IP Adress',
	'netelement.state' => 'State',
	'netelement.pos' => 'Position',
	// NetElementType
	'netelementtype.name' => 'Netelementtype',
	//HfcSnmp
	'parameter.oid.name' => 'OID Name',
	//Mibfile
	'mibfile.id' => 'ID',
	'mibfile.name' => 'Mibfile',
	'mibfile.version' => 'Version',
	// OID
	'oid.name_gui' => 'GUI Label',
	'oid.name' => 'OID Name',
	'oid.oid' => 'OID',
	'oid.access' => 'Access Type',
	//SnmpValue
	'snmpvalue.oid_index' => 'OID Index',
	'snmpvalue.value' => 'OID Value',
	// MAIL
	'email.localpart' => 'Local Part',
	'email.index' => 'Primary E-Mail?',
	'email.greylisting' => 'Greylisting active?',
	'email.blacklisting' => 'On Blacklist?',
	'email.forwardto' => 'Forward to:',
	// CMTS
	'cmts.id' => 'ID',
	'cmts.hostname' => 'Hostname',
	'cmts.ip' => 'IP',
	'cmts.company' => 'Manufacturer',
	'cmts.type' => 'Type',
	// Contract
	'contract.company' => 'Company',
	'contract.number' => 'Number',
	'contract.firstname' => 'Firstname',
	'contract.lastname' => 'Surname',
	'contract.zip' => 'ZIP',
	'contract.city' => 'City',
	'contract.street' => 'Street',
	'contract.house_number' => 'Housenr',
	'contract.district' => 'District',
	'contract.contract_start' => 'Startdate',
	'contract.contract_end' => 'Enddate',
	// Domain
	'domain.name' => 'Domain',
	'domain.type' => 'Type',
	'domain.alias' => 'Alias',
	// Endpoint
	'endpoint.ip' => 'IP',
	'endpoint.hostname' => 'Hostname',
	'endpoint.mac' => 'MAC',
	'endpoint.description' => 'Description',
	// IpPool
	'ippool.id' => 'ID',
	'ippool.type' => 'Type',
	'ippool.net' => 'Net',
	'ippool.netmask' => 'Netmask',
	'ippool.router_ip' => 'Router IP',
	'ippool.description' => 'Description',
	// Modem
	'modem.house_number' => 'Numero',
	'modem.id' => 'Número de módem',
	'modem.mac' => 'Dirección MAC',
	'modem.model' => 'Modelo',
	'modem.sw_rev' => 'Versión del firmware',
	'modem.name' => 'Nombre de módem',
	'modem.firstname' => 'Nombre',
	'modem.lastname' => 'Apellido',
	'modem.city' => 'Ciudad',
	'modem.street' => 'Calle',
	'modem.district' => 'Distrito',
	'modem.us_pwr' => 'US level',
	'modem.geocode_source' => 'Geolocalización',
	'modem.inventar_num' => 'Nº de serie',
	'contract_valid' => 'Activo?',
	// QoS
	'qos.name' => 'Nombre de Plan',
	'qos.ds_rate_max' => 'Velocidad DS',
	'qos.us_rate_max' => 'Velocidad US',
	// Mta
	'mta.hostname' => 'Nombre de host',
	'mta.mac' => 'Dirección MAC',
	'mta.type' => 'Protocolo VOIP',
	// Configfile
	'configfile.name' => 'Configfile',
	// PhonebookEntry
	'phonebookentry.id' => 'ID',
	// Phonenumber
	'phonenumber.prefix_number' => 'Prefix',
	'phonenumber.number' => 'Number',
	'phonenr_act' => 'Activation date',
	'phonenr_deact' => 'Deactivation date',
	'phonenr_state' => 'Status',
	'modem_city' => 'Modem city',
	// Phonenumbermanagement
	'phonenumbermanagement.id' => 'ID',
	'phonenumbermanagement.activation_date' => 'Activation date',
	'phonenumbermanagement.deactivation_date' => 'Deactivation date',
	// PhoneTariff
	'phonetariff.name' => 'Phone Tariff',
	'phonetariff.type' => 'Type',
	'phonetariff.description' => 'Description',
	'phonetariff.voip_protocol' => 'VOIP Protokoll',
	'phonetariff.usable' => 'Usable?',
	// ENVIA enviaorder
	'enviaorder.ordertype'  => 'Order Type',
	'enviaorder.orderstatus'  => 'Order Status',
	'escalation_level' => 'Statuslevel',
	'enviaorder.created_at'  => 'Created at',
	'enviaorder.updated_at'  => 'Updated at',
	'enviaorder.orderdate'  => 'Order date',
	'enviaorder_current'  => 'Action needed?',
	//ENVIA Contract
	'enviacontract.envia_contract_reference' => 'envia TEL Contract reference',
	'enviacontract.state' => 'Status',
	'enviacontract.start_date' => 'Start Date',
	'enviacontract.end_date' => 'End Date',
	// CDR
	'cdr.calldate' => 'Call Date',
	'cdr.caller' => 'Caller',
	'cdr.called' => 'Called',
	'cdr.mos_min_mult10' => 'Minimum MOS',
	// Numberrange
	'numberrange.id' => 'ID',
	'numberrange.name' => 'Name',
	'numberrange.start' => 'Start',
	'numberrange.end' => 'End',
	'numberrange.prefix' => 'Prefix',
	'numberrange.suffix' => 'Suffix',
	'numberrange.type' => 'Type',
	'numberrange.costcenter.name' => 'Cost center',
	// Ticket
	'ticket.id' => 'ID',
	'ticket.name' => 'Title',
	'ticket.type' => 'Type',
	'ticket.priority' => 'Priority',
	'ticket.state' => 'State',
	'ticket.user_id' => 'Created by',
	'ticket.created_at' => 'Created at',
	'ticket.assigned_users' => 'Assigned Users',
	'assigned_users' => 'Assigned Users',
	'tickettypes.name' => 'Type',
];
