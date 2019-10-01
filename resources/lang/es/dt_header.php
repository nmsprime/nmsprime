<?php

return [
    // Index DataTable Header
    'amount' => 'Cantidad',
    'city' => 'Ciudad',
    'expansion_degree' => 'Grado de expansión',
    'floor' => 'Piso',
    'group_contract' => 'Contrato de grupo',
    'house_nr' => 'Numero de vivienda',
    'iban' => 'Cuenta bancaria (código IBAN)',
    'id'            => 'ID',
    'name' => 'Nombre',
    'number' => 'Número',
    'prio'          => 'Prioridad',
    'street' => 'Calle',
    'sum' => 'Suma',
    'zip' => 'Código postal',
    'apartment' => [
        'number' => 'Número',
        'connected' => 'Conectado',
        'occupied' => 'Ocupado',
    ],
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
    'debt' => [
        'date' => 'Fecha',
        'due_date' => 'Due date',
        'indicator' => 'Dunning indicator',
        'missing_amount' => 'Missing amount',
        'number' => 'debt number',
        'total_fee' => 'Tarifa',
        'voucher_nr' => 'Voucher nr',
        ],
    //Invoices
    'invoice.type' => 'Tipo',
    'invoice.year' => 'Año',
    'invoice.month' => 'Mes',
    //Item
    'item.valid_from' => 'Artículo válido desde',
    'item.valid_from_fixed' => 'Artículo válido de fijo',
    'item.valid_to' => 'Artículo válido para',
    'item.valid_to_fixed' => 'Artículo válido a fijo',
    'fee' => 'Tarifa',
    'product' => [
        'proportional' => 'Proporcionado',
        'type' => 'Tipo',
        'name' => 'Nombre del producto',
        'price' => 'Precio',
    ],
    // Salesman
    'salesman.id' => 'ID',
    'salesman_id' 		=> 'ID del vendedor',
    'salesman_firstname' => 'Nombre',
    'salesman_lastname' => 'Apellido',
    'commission in %' 	=> 'Comisión en %',
    'contract_nr' 		=> 'Nro. de Contrato',
    'contract_name' 	=> 'Cliente',
    'contract_start' 	=> 'Inicio de Contrato',
    'contract_end' 		=> 'Fin de Contrato',
    'product_name' 		=> 'Producto',
    'product_type' 		=> 'Tipo de Producto',
    'product_count' 	=> 'Contar',
    'charge' 			=> 'Cambiar',
    'salesman.lastname' => 'Apellidos',
    'salesman.firstname' => 'Nombres',
    'salesman_commission' => 'Comisión',
    'sepaaccount_id' 	=> 'ID Cuenta SEPA',
    // SepaAccount
    'sepaaccount.name' => 'Nombre de la cuenta',
    'sepaaccount.institute' => 'Institución',
    'sepaaccount.iban' => 'IBAN',
    // SepaMandate
    'sepamandate.holder' => 'Titular de la cuenta',
    'sepamandate.valid_from' => 'Válido desde',
    'sepamandate.valid_to' => 'Válido hasta',
    'sepamandate.reference' => 'Referencia de cuenta',
    'sepamandate.disable' => 'Desactivado',
    // SettlementRun
    'settlementrun.year' => 'Año',
    'settlementrun.month' => 'Mes',
    'settlementrun.created_at' => 'Creado el',
    'settlementrun.executed_at' => 'Ejecutado en',
    'verified' => 'Verificado?',
    // MPR
    'mpr.name' => 'Nombre',
    'mpr.id'    => 'ID',
    // NetElement
    'netelement.id' => 'ID',
    'netelement.name' => 'Elemento de red',
    'netelement.ip' => 'Direccion IP',
    'netelement.state' => 'Estado',
    'netelement.pos' => 'Posición',
    'netelement.options' => 'Opciones',
    // NetElementType
    'netelementtype.name' => 'Tipo de elemento de red',
    //HfcSnmp
    'parameter.oid.name' => 'Nombre OID',
    //Mibfile
    'mibfile.id' => 'ID',
    'mibfile.name' => 'Archivo MIB',
    'mibfile.version' => 'Versión',
    // OID
    'oid.name_gui' => 'Etiqueta de GUI',
    'oid.name' => 'Nombre OID',
    'oid.oid' => 'OID',
    'oid.access' => 'Tipo de acceso',
    //SnmpValue
    'snmpvalue.oid_index' => 'Indice OID',
    'snmpvalue.value' => 'Valor OID',
    // MAIL
    'email.localpart' => 'Parte local',
    'email.index' => 'E-Mail primario?',
    'email.greylisting' => '¿Activo listas de rechazo transitorio?',
    'email.blacklisting' => 'Lista negra habilitada?',
    'email.forwardto' => 'Reenviar a:',
    // CMTS
    'cmts.id' => 'ID',
    'cmts.hostname' => 'Nombre de host',
    'cmts.ip' => 'IP',
    'cmts.company' => 'Fabricante',
    'cmts.type' => 'Tipo',
    'cmts.formatted_support_state' => 'Support State',
    'cmts.support_state' => 'Support State',
    // Contract
    'contract.city' => 'Ciudad',
    'contract.company' => 'Empresa',
    'contract.contract_end' => 'Fin de Contrato',
    'contract.contract_start' => 'Inicio de Contrato',
    'contract.district' => 'Provincia',
    'contract.firstname' => 'Nombres',
    'contract.house_number' => 'Numero de vivienda',
    'contract.id' => 'Contrato',
    'contract.lastname' => 'Apellidos',
    'contract.number' => 'Numero',
    'contract.street' => 'Calle',
    'contract.zip' => 'Código postal',
    // Domain
    'domain.name' => 'Nombre del dominio',
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
    'ippool.router_ip' => 'Ip de Router',
    'ippool.description' => 'Descripcion',
    // Modem
    'modem.city' => 'Ciudad',
    'modem.district' => 'Distrito',
    'modem.firstname' => 'Nombres',
    'modem.geocode_source' => 'Geolocalización',
    'modem.house_number' => 'Numero de vivienda',
    'modem.id' => 'Modem ID',
    'modem.inventar_num' => 'Serial',
    'modem.lastname' => 'Apellidos',
    'modem.mac' => 'Direccion MAC',
    'modem.model' => 'Modelo',
    'modem.name' => 'Nombre del modem',
    'modem.street' => 'Calle',
    'modem.sw_rev' => 'Version de Firmware',
    'modem.us_pwr' => 'Nivel US',
    'modem.support_state' => 'Suport State',
    'modem.formatted_support_state' => 'Support State',
    'contract_valid' => 'Contrato valido?',
    // Node
    'node' => [
        'name' => 'Nombre',
        'headend' => 'Cabecera',
        'type' => 'Tipo de señal',
    ],
    // QoS
    'qos.name' => 'Nombre de QoS',
    'qos.ds_rate_max' => 'Velocidad maxima de bajada',
    'qos.us_rate_max' => 'Velocidad maxima de subida',
    // Mta
    'mta.hostname' => 'Nombre de Host',
    'mta.mac' => 'Direccion MAC',
    'mta.type' => 'Protocolo VOIP',
    // Configfile
    'configfile.name' => 'Archivo de configuracion',
    // PhonebookEntry
    'phonebookentry.id' => 'ID',
    // Phonenumber
    'phonenumber.prefix_number' => 'Prefijo',
    'phonenr_act' => 'Fecha de activacion',
    'phonenr_deact' => 'Fecha de desactivacion',
    'phonenr_state' => 'Estado',
    'modem_city' => 'Ciudad del modem',
    'sipdomain' => 'Registrar',
    // Phonenumbermanagement
    'phonenumbermanagement.id' => 'ID',
    'phonenumbermanagement.activation_date' => 'Fecha de activacion',
    'phonenumbermanagement.deactivation_date' => 'Fecha de desactivacion',
    // PhoneTariff
    'phonetariff.name' => 'Tarifa telefonica',
    'phonetariff.type' => 'Tipo',
    'phonetariff.description' => 'Descripcion',
    'phonetariff.voip_protocol' => 'Protocolo VOIP',
    'phonetariff.usable' => 'Disponible?',
    // ENVIA enviaorder
    'enviaorder.ordertype'  => 'Tipo de orden',
    'enviaorder.orderstatus'  => 'Estado de orden',
    'escalation_level' => 'Nivel de estado',
    'enviaorder.created_at'  => 'Creado el',
    'enviaorder.updated_at'  => 'Subido el',
    'enviaorder.orderdate'  => 'Fecha de orden',
    'enviaorder_current'  => 'Acciones necesarias?',
    'enviaorder.contract.number' => 'Contrato',
    'phonenumber.number' => 'Numero',
    //ENVIA Contract
    'enviacontract.contract.number' => 'Contrato',
    'enviacontract.end_date' => 'Fecha de desenlace',
    'enviacontract.envia_contract_reference' => 'envia TEL referencia de contrato',
    'enviacontract.start_date' => 'Fecha de inicio',
    'enviacontract.state' => 'Estado',
    // CDR
    'cdr.calldate' => 'Fecha de llamada',
    'cdr.caller' => 'Emisor',
    'cdr.called' => 'Receptor',
    'cdr.mos_min_mult10' => 'MOS minimo',
    // Numberrange
    'numberrange.id' => 'ID',
    'numberrange.name' => 'Nombre',
    'numberrange.start' => 'Inicio',
    'numberrange.end' => 'Fin',
    'numberrange.prefix' => 'Prefijo',
    'numberrange.suffix' => 'Sufijo',
    'numberrange.type' => 'Tipo',
    'numberrange.costcenter.name' => 'Centro de costes',
    'realty' => [
        'name' => 'Nombre',
        'administration' => 'Administración',
        'agreement_from' => 'Válido desde',
        'agreement_to' => 'Válido hasta',
        'concession_agreement' => 'Acuerdo de concesión',
        'last_restoration_on' => 'Última restauración',
    ],
    // Ticket
    'ticket.id' => 'ID',
    'ticket.name' => 'Titulo',
    'ticket.type' => 'Tipo',
    'ticket.priority' => 'Prioridad',
    'ticket.state' => 'Estado',
    'ticket.user_id' => 'Creado por',
    'ticket.created_at' => 'Creando el',
    'ticket.assigned_users' => 'Usuarios asignados',
    'assigned_users' => 'Usuarios asignados',
    'tickettypes.name' => 'Tipo',
];
