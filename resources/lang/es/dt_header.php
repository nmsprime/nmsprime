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
	'product.name' => 'Nombre del producto',
	'product.price' => 'Precio',
	// Salesman
	'salesman.id' => 'ID',
	'salesman.lastname' => 'Apellidos',
	'salesman.firstname' => 'Nombres',
	'commission in %' 	=> 'Comision en %',
	'contract_nr' 		=> 'Nro. Contrato',
	'contract_name' 	=> 'Cliente',
	'contract_start' 	=> 'Inicio de Contrato',
	'contract_end' 		=> 'Fin de Contrato',
	'product_name' 		=> 'Producto',
	'product_type' 		=> 'Tipo de Producto',
	'product_count' 	=> 'Contar',
	'charge' 			=> 'Cambiar',
	'salesman_commission' => 'Comision',
	'sepaaccount_id' 	=> 'ID Cuenta SEPA',
	// SepaAccount
	'sepaaccount.name' => "Nombre de cuenta SEPA",
	'sepaaccount.institute' => 'Asociacion',
	'sepaaccount.iban' => 'IBAN',
	// SepaMandate
	'sepamandate.sepa_holder' => 'Poseedor de cuenta',
	'sepamandate.sepa_valid_from' => 'Valida desde',
	'sepamandate.sepa_valid_to' => 'Valida hasta',
	'sepamandate.reference' => 'Referencia de cuenta',
	// SettlementRun
	'settlementrun.year' => 'A&ntilde;o',
	'settlementrun.month' => 'Mes',
	'settlementrun.created_at' => 'Creado el',
	'verified' => 'Verificado?',
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
	'oid.name_gui' => 'Etiqueta de GUI',
	'oid.name' => 'Nombre de la OID',
	'oid.oid' => 'OID',
	'oid.access' => 'Tipo de acceso',
	//SnmpValue
	'snmpvalue.oid_index' => 'Índice OID',
	'snmpvalue.value' => 'Valor del OID',
	// MAIL
	'email.localpart' => 'Parte local',
	'email.index' => 'Correo electrónico principal?',
	'email.greylisting' => '¿Activo listas de rechazo transitorio?',
	'email.blacklisting' => '¿Está en la lista negra?',
	'email.forwardto' => 'Reenviar a:',
	// CMTS
	'cmts.id' => 'ID',
	'cmts.hostname' => 'Nombre de host',
	'cmts.ip' => 'IP',
	'cmts.company' => 'Fabricante',
	'cmts.type' => 'Tipo',
	// Contract
	'contract.company' => 'Empresa',
	'contract.number' => 'Número',
	'contract.firstname' => 'Nombre',
	'contract.lastname' => 'Apellido',
	'contract.zip' => 'Código postal',
	'contract.city' => 'Ciudad',
	'contract.street' => 'Calle',
	'contract.house_number' => 'Numero de vivienda',
	'contract.district' => 'Provincia',
	'contract.contract_start' => 'Fecha de inicio',
	'contract.contract_end' => 'Fecha de finalización',
	// Domain
	'domain.name' => 'Dominio',
	'domain.type' => 'Tipo',
	'domain.alias' => 'Alias',
	// Endpoint
	'endpoint.ip' => 'IP',
	'endpoint.hostname' => 'Nombre de host',
	'endpoint.mac' => 'MAC',
	'endpoint.description' => 'Descripción 	',
	// IpPool
	'ippool.id' => 'ID',
	'ippool.type' => 'Tipo',
	'ippool.net' => 'Red',
	'ippool.netmask' => 'Máscara de red',
	'ippool.router_ip' => 'Router IP',
	'ippool.description' => 'Descripción 	',
	// Modem
    'modem.house_number' => 'Numero de vivienda',
	'modem.id' => 'Modem ID',
	'modem.mac' => 'Direccion MAC',
	'modem.model' => 'Modelo',
	'modem.sw_rev' => 'Version de Firmware',
	'modem.name' => 'Nombre del modem',
	'modem.firstname' => 'Nombres',
	'modem.lastname' => 'Apellidos',
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
