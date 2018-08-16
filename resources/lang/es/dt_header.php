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
	'netelement.name' => 'Elementos de red',
	'netelement.ip' => 'Dirección IP',
	'netelement.state' => 'Estado',
	'netelement.pos' => 'Posición',
	// NetElementType
	'netelementtype.name' => 'Tipo de elemento',
	//HfcSnmp
	'parameter.oid.name' => 'Nombre de la OID',
	//Mibfile
	'mibfile.id' => 'ID',
	'mibfile.name' => 'Mibfile',
	'mibfile.version' => 'Versión',
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
	'modem.us_pwr' => 'Nivel US',
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
	'configfile.name' => 'Archivo de configuración',
	// PhonebookEntry
	'phonebookentry.id' => 'ID',
	// Phonenumber
	'phonenumber.prefix_number' => 'Prefijo',
	'phonenumber.number' => 'Número',
	'phonenr_act' => 'Fecha de activación',
	'phonenr_deact' => 'Fecha de desactivación',
	'phonenr_state' => 'Estado',
	'modem_city' => 'Ciudad de módem',
	// Phonenumbermanagement
	'phonenumbermanagement.id' => 'ID',
	'phonenumbermanagement.activation_date' => 'Fecha de activación',
	'phonenumbermanagement.deactivation_date' => 'Fecha de desactivación',
	// PhoneTariff
	'phonetariff.name' => 'Tarifa de teléfono',
	'phonetariff.type' => 'Tipo',
	'phonetariff.description' => 'Descripción 	',
	'phonetariff.voip_protocol' => 'Protocolo VOIP',
	'phonetariff.usable' => 'Disponible?',
	// ENVIA enviaorder
	'enviaorder.ordertype'  => 'Tipo De Orden',
	'enviaorder.orderstatus'  => 'Estado de la Orden',
	'escalation_level' => 'Statuslevel',
	'enviaorder.created_at'  => 'Creando el',
	'enviaorder.updated_at'  => 'Actualizada en',
	'enviaorder.orderdate'  => 'Fecha del pedido',
	'enviaorder_current'  => '¿Acción necesaria?',
	//ENVIA Contract
	'enviacontract.envia_contract_reference' => 'referencia contrato TEL',
	'enviacontract.state' => 'Estado',
	'enviacontract.start_date' => 'Fecha Inicio',
	'enviacontract.end_date' => 'Fecha fin',
	// CDR
	'cdr.calldate' => 'Fecha de la llamada',
	'cdr.caller' => 'Persona que llama',
	'cdr.called' => 'Llamada',
	'cdr.mos_min_mult10' => 'MOS mínimos',
	// Numberrange
	'numberrange.id' => 'ID',
	'numberrange.name' => 'Nombre',
	'numberrange.start' => 'Inicio',
	'numberrange.end' => 'Fin',
	'numberrange.prefix' => 'Prefijo',
	'numberrange.suffix' => 'Sufijo',
	'numberrange.type' => 'Tipo',
	'numberrange.costcenter.name' => 'Centro de costo',
	// Ticket
	'ticket.id' => 'ID',
	'ticket.name' => 'Título',
	'ticket.type' => 'Tipo',
	'ticket.priority' => 'Prioridad',
	'ticket.state' => 'Estado',
	'ticket.user_id' => 'Creado por',
	'ticket.created_at' => 'Creando el',
	'ticket.assigned_users' => 'Usuarios asignados',
	'assigned_users' => 'Usuarios asignados',
	'tickettypes.name' => 'Tipo',
];
