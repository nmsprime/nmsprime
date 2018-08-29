<?php

return [

    /*
    |--------------------------------------------------------------------------
    | All other Language Lines - TODO: split descriptions and messages?
    |--------------------------------------------------------------------------
    */
    'Active'					=> 'Activo',
    'Active?'					=> 'Activo?',
    'Additional Options'		=> 'Opciones adicionales.',
    'Address Line 1'			=> 'Dirección (línea 1)',
    'Address Line 2'			=> 'Dirección (línea 2)',
    'Address Line 3'			=> 'Dirección (línea 3)',
    'Assigned'					=> 'Asignado',
    'BIC'						=> 'BIC',
    'Bank Account Holder'		=> 'Nombre de titular de cuenta bancaria',
    'Birthday'					=> 'Fecha nacimiento',
    'City'						=> 'Ciudad',
    'Choose KML file'			=> 'Elegir archivo KML',

    'Company'					=> 'Empresa',
    'Contract Number'			=> 'Número de contrato',
    'Contract Start'			=> 'Inicio de Contrato',
    'Contract End'				=> 'Fin de Contrato',
    'Contract valid' 			=> 'Contrato válido',
    'Contract'					=> 'Contrato',
    'Contract List'				=> 'Lista de contratos',
    'Contracts'					=> 'Contratos',
    'International prefix'		=> 'Prefijo internacional',
    'Country code'				=> 'Código de país',
    // Descriptions of Form Fields in Edit/Create
    'accCmd_error_noCC' 	=> 'Contrato :contract_nr [ID :contract_id] no tiene asignado Costo. No se creará ninguna factura para el cliente.',
    'accCmd_invoice_creation_deactivated' => 'Los siguientes contratos han desactivado la creación de facturas: :contractnrs',
    'Create'					=> 'Crear',
    'accCmd_processing' 	=> 'El Acuerdo es ejecutado. Por favor espere hasta que este proceso haya finalizado.',
    'Date of installation address change'	=> 'Fecha de cambio de domicilio de instalación',
    'Delete'					=> 'Eliminar',
    'Day'						=> 'Día',
    'Description'				=> 'Descripción',
    'Device'					=> 'Dispositivo',
    'accCmd_notice_CDR' 	=> 'El Contrato :contract_nr [ID :contract_id] tiene registros de datos de llamadas pero no tiene asignada una tarifa Voip válida',
    'Device List'				=> 'Lista de dispositivos',
    'Device Type'				=> 'Tipo de dispositivo',
    'Device Type List'			=> 'Lista  de tipos de dispositivos',
    'Devices'					=> 'Dispositivos',
    'DeviceTypes'				=> 'Tipos de dispositivos',
    'District'					=> 'Distrito',
    'Edit'						=> 'Editar',
    'Edit '						=> 'Editar',
    'Endpoints'					=> 'Punto final:',
    'Endpoints List'			=> 'Endpoints List',
    'Entry'						=> 'Entrada',
    'alert' 				=> 'Atencion!',
    'ALL' 					=> 'TODOS',
    'E-Mail Address'			=> 'Dirección de correo electrónico',
    'First IP'					=> 'Primer IP',
    'Firstname'					=> 'Nombres',
    'Fixed IP'					=> 'IP fija',
    'Force Restart'				=> 'Forzar reinicio',
    'Geocode origin'			=> 'Origen de geocodificación',
    'IBAN'						=> 'IBAN',
    'Internet Access'			=> 'Acceso a Internet',
    'Inventar Number'			=> 'Inventar Number',
    'Call Data Record'		=> 'Registo de Datos de Llamada',
    'IP address'				=> 'Dirección IP',
    'Language'					=> 'Idioma',
    'Lastname'					=> 'Apellidos',
    'Last IP'					=> 'último IP',
    'ccc'					=> 'Centro de Control del Cliente',
    'MAC Address'				=> 'Dirección MAC',
    'Main Menu'					=> 'Menú Principal',
    'Maturity' 					=> 'Vencimiento',
    'cdr' 					=> 'CDR',
    'cdr_discarded_calls' 	=> "CDR: Contrato Nr o ID ':contractnr' no encontradas en la base de datos - :count llamadas del numero telefonico :phonenr con precio de :price :currency son desechados.",
    'cdr_missing_phonenr' 	=> 'Parse CDR.csv: Detectados registros de datos de llamada con phonenr :phonenr que esta faltando en la base de datos. Descartar :count llamadas con cargo de :price :currency.',
    'cdr_missing_reseller_data' => 'Faltan Datos del Revendedor en Archivo de Entorno!',
    'cdr_offset' 			=> 'CDR diferencia de tiempo de Factura en Meses',
    'close' 				=> 'Cerrar',
    'contract_early_cancel' => 'En serio quiere cancelar este contrato antes de que el fin de termino de las tarifas :date sea alcanzado?',
    'contract_nr_mismatch'  => 'No se pudo encontrar el siguiente número de contrato porque la consulta al base de datos ha fallado. Esto es debido a que ha selecionado los siguientes contratos con un número de contrato que no pertenecen a su centro de costo: :nrs',
    'contract_numberrange_failure' => 'No se encuentra disponible el numero de contrato para el centro de costos seleccionado!',
    'conn_info_err_create' 	=> 'Error Creando PDF - Revise los Registros o pregunte a un Administrador!',
    'conn_info_err_template' => 'No se pudo Leer la Plantilla - Mire Configuracion Globar o Compania si es establecido!',
    'cpe_log_error' 		=> 'No se estuvo registrando en el Servidor - No se encontro registro',
    'cpe_not_reachable' 	=> 'pero no alcanzable desde el lado-WAN debido a razones de fabricacion (puede ser posible de habilitar la respuesta ICMP a travez de el archivo de configuracion de modem)',
    'cpe_fake_lease'		=> 'El servidor DHCP no ha generado un lease para su endpoint, ya que la direccion IP ha sido estaticamente asignada y el servidor no necesita mantener rastro de el. El siguiente lease ha sido manualmente generado solo por referencia:',
    'D' 					=> 'dia|dias',
    'dashbrd_ticket' 		=> 'Mis Nuevos Tickets',
    'device_probably_online' =>	':type es probablemente online',
    'eom' 					=> 'al final del mes',
    'envia_no_interaction' 	=> 'No Ordenes Envia requieren Interaccion',
    'Month'						=> 'Mes',
    'envia_interaction'	 	=> 'Orden Envia requiere Interaccion|Ordenes Envia requieren Interaccion',
    'Net'						=> 'Red',
    'Netmask'					=> 'Máscara de red',
    'Network Access'			=> 'Acceso a la red',
    'no' 						=> 'no',
    'Number'					=> 'Número',
    'Options'					=> 'Opciones',
    'or: Upload KML file'		=> 'o: subir archivo KML',
    'Parent Device Type'		=> 'Tipo de dispositivo principal',
    'Parent Object'				=> 'Objeto primario',
    'Period of Notice' 			=> 'Plazo de preaviso',
    'Password'					=> 'Contraseña',
    'Confirm Password'					=> 'Confirme su contraseña',
    'Phone'						=> 'Teléfono',
    'Phone ID next month'		=> 'Phone ID next month',
    'Phonenumber'				=> 'Número de teléfono',
    'Phonenumbers'				=> 'Numeros telefonicos',
    'Phonenumbers List'			=> 'Lista de números telefonicos',
    'Postcode'					=> 'Código postal',
    'Prefix Number'				=> 'Prefix Number',
    'Price'						=> 'Precio',
    'Public CPE'				=> 'IP público CPE',
    'QoS next month'			=> 'QoS next month',
    'Real Time Values'			=> 'Valores en tiempo real',
    'Remember Me'				=> 'Recordar mis datos',
    'Salutation'				=> 'Tratamiento',
    'Save'						=> 'Guardar',
    'Save All'					=> 'Guardar todo',
    'Save / Restart'			=> 'Guardar / Reiniciar',
    'Serial Number'				=> 'Número de serie',
    'Sign me in' 				=> 'Iniciar sesión',
    'State'						=> 'Estado',
    'Street'					=> 'Calle',
    'Typee'						=> 'Tipo',
    'Unexpected exception' 		=> 'Excepción inesperada',
    'US level' 					=> 'Nivel US',
    'Username'					=> 'Usuario',
    'Users'						=> 'Usuarios',
    'Vendor'					=> 'Proveedor',
    'Year'						=> 'Año',
    'yes' 						=> 'Sí',
    'home' 						=> 'Hogar',
    'indices_unassigned' 		=> 'Algunos de los Indices asignados no pudieron ser asignados al correspondiente parametro! Solo no estan siendo usadas. Usted puede eliminarlas o mantenerlas para despues. Solo compare la lista de parametros del tipo de elemento de red con la lista de indices aqui.',
    'item_credit_amount_negative' => 'Una cantidad de credito negativa se convierte a debito! Esta seguro que el cliente debe ser endeudado?',
    'invoice' 					=> 'Factura',
    'Global Config'				=> 'Configuración global',
    'GlobalConfig'				=> 'Configuración global',
    'VOIP'						=> 'VoIP',
    'Customer Control Center'	=> 'Centro de control de clientes',
    'Provisioning'				=> 'Aprovisionamiento',
    'BillingBase'				=> 'Módulo de facturación',
    'Ccc' 						=> 'CCC',
    'HfcBase' 					=> 'HfcBase',
    'ProvBase' 					=> 'ProvBase',
    'ProvVoip' 					=> 'ProvVoip',
    'HFC'						=> 'HFC',
    'Rank'						=> 'Ranking:',
    'Assign Users'				=> 'Asignar usuarios',
    'Invoices'					=> 'Facturas',
    'Ability'					=> 'Capacidad',
    'Allow'						=> 'Permitir',
    'Allow to'					=> 'Permitir a',
    'Forbid'					=> 'Prohibir',
    'Forbid to'					=> 'Prohibir a',
    'Save Changes'				=> 'Guardar cambios',
    'Manage'					=> 'Administrar',
    'View'						=> 'Ver',
    'Create'					=> 'Crear',
    'Update'					=> 'Actualizar',
    'Delete'					=> 'Eliminar',
    'Help'						=> 'Ayuda',
    'All abilities'				=> 'All abilities',
    'View everything'			=> 'Mostrar todo',
    'Use api'					=> 'Usar api',
    'See income chart'			=> 'See income chart',
    'View analysis pages of modems'	=> 'View analysis pages of modems',
    'View analysis pages of cmts' => 'View analysis pages of cmts',
    'Download settlement runs'	=> 'Download settlement runs',
    'Not allowed to acces this user' => 'Prohibido el acceso a este usuario',
    'log_out'				=> 'Cierre de sesion',
    'System Log Level'			=> 'Nivel de registro del sistema',
    'Headline 1'				=> 'Título 1',
    'Headline 2'				=> 'Título 2',
    'M' 					=> 'mes|meses',
    'Mark solved'			=> 'Marcar como resuelto?',
    'missing_product' 		=> 'Falta Producto!',
    'modem_eventlog_error'	=> 'Modem eventlog no encontrado',
    'modem_force_restart_button_title' => 'Solo reinicia el modem. No guarda algun dato cambiado!',
    'CDR retention period' 		=> 'CDR retention period',
    'Day of Requested Collection Date'	=> 'Day of Requested Collection Date',
    'Tax in %'					=> 'Impuestos en %',
    'Invoice Number Start'		=> 'Inicio de número de facturación',
    'Split Sepa Transfer-Types'	=> 'Split Sepa Transfer-Types',
    'Mandate Reference'			=> 'Mandate Reference',
    'e.g.: String - {number}'	=> 'e.g.: String - {number}',
    'Item Termination only end of month'=> 'Item Termination only end of month',
    'Language for settlement run' => 'Language for settlement run',
    'Uncertain start/end dates for tariffs' => 'Uncertain start/end dates for tariffs',
    'modem_monitoring_error'=> 'Esto podría deberse a que el módem no estaba en línea hasta ahora. Tenga en cuenta que los diagramas solo están disponibles
desde el punto en que un módem estaba en línea. Si todos los diagramas no se muestran correctamente, entonces debe ser un
 problema más grande y debería haber una mala configuración de cactus. Por favor, considere comunicar al administrador en problemas mayores.',
    'Connection Info Template'	=> 'Connection Info Template',
    'Upload Template'			=> 'Subir plantilla',
    'SNMP Read Only Community'	=> 'SNMP de solo lectura',
    'SNMP Read Write Community'	=> 'SNMP de lectura y escritura',
    'Provisioning Server IP'	=> 'IP del Servidor NMSprime',
    'Domain Name for Modems'	=> 'Nombre de dominio para CableModems',
    'Notification Email Address'=> 'Dirección de correo electrónico para notificaciónes',
    'DHCP Default Lease Time'	=> 'Tiempo de Arrendamiento del DHCP ',
    'DHCP Max Lease Time'		=> 'Tiempo máximo de Arrendamiento del DHCP ',
    'Start ID Contracts'		=> 'Inicio de ID del contrato',
    'Start ID Modems'			=> 'Inicio de ID del CableModem',
    'Start ID Endpoints'		=> 'Inicio de ID para terminales',
    'Downstream rate coefficient' => 'Coeficiente de velocidad de descarga',
    'Upstream rate coefficient' => 'Coeficiente de velocidad de subida',
    'modem_no_diag'			=> 'Diagrams no disponibles',
    'Start ID MTA´s'			=> 'Inicio de ID para MTA\'s',
    'modem_lease_error'		=> 'No se encontro Arrendamientos dhcp valido',
    'modem_lease_valid' 	=> 'Modem tiene un Arrendamientos dhcp valido',
    'modem_log_error' 		=> 'Modem no fue registrado en el Servidor - Ningun registro encontrado',
    'modem_configfile_error'=> 'Archivo de configuracion del Modem no hallado',
    'Academic Degree'			=> 'Academic Degree',
    'modem_offline'			=> 'Modem esta Offline',
    'Contract number'			=> 'Número de contrato',
    'Contract Nr'				=> 'Número de contrato',
    'Contract number legacy'	=> 'Número de contrato heredado',
    'Cost Center'				=> 'Centro de costos',
    'Create Invoice'			=> 'Crear factura',
    'Customer number'			=> 'Número de cliente',
    'Customer number legacy'	=> 'Número de cliente heredado',
    'Department'				=> 'Departamento',
    'End Date' 					=> 'Fecha de finalización',
    'House Number'				=> 'Número del domicilio',
    'House Nr'					=> 'Número del domicilio',
    'Salesman'					=> 'Vendedor',
    'Start Date' 				=> 'Fecha de inicio',
    'modem_restart_error' 		=> 'No se pudo reiniciar Modem! (offline?)',
    'Contact Persons' 			=> 'Personas de contacto',
    'modem_restart_success_cmts' => 'Modem reiniciado via CMTS',
    'Accounting Text (optional)'=> 'Informacion adicional (opcional)',
    'Cost Center (optional)'	=> 'Centro de costo (opcional)',
    'Credit Amount' 			=> 'Importe de crédito',
    'modem_restart_success_direct' => 'Modem reiniciado directamente por SNMP',
    'Item'						=> 'Artículo',
    'Items'						=> 'Artículos',
    'modem_save_button_title' 	=> 'Guarda datos cambiados. Determina una nueva geoposition cuando los datos de direccion han sido cambiados (y lo asigna a un nuevo MPR si es necesario). Reconstruye el archivo de configuracion y reinicia el modem si por lo menos algo de lo siguiente ha sido cambiado: IP Publica, acceso a la red, archivo de configuracion, QoS, direccion-MAC',
    'Product'					=> 'Producto',
    'Start date' 				=> 'Fecha de inicio',
    'Active from start date' 	=> 'Activo a partir de la fecha de inicio',
    'Valid from'				=> 'Válido desde',
    'Valid to'					=> 'Válido hasta',
    'Valid from fixed'			=> 'Valid from fixed',
    'Valid to fixed'			=> 'Valid to fixed',
    'modem_statistics'		=> 'Cifra Online / Offline Modems',
    'Configfile'				=> 'Archivo de configuracion',
    'Mta'						=> 'MTA',
    'month' 				=> 'Mes',
    'Configfiles'				=> 'Archivo de configuración',
    'Choose Firmware File'		=> 'Seleccione el archivo de Firmware',
    'Config File Parameters'	=> 'Parámetros del archivo de configuración',
    'or: Upload Firmware File'	=> 'o: subir archivo de Firmware',
    'Parent Configfile'			=> 'Archivo de configuración principal',
    'Public Use'				=> 'IP publica',
    'mta_configfile_error'	=> 'MTA archivo de configuracion no encontrado',
    'IpPool'						=> 'IpPool',
    'SNMP Private Community String'	=> 'SNMP Private Community String',
    'SNMP Public Community String'	=> 'SNMP Public Community String',
    'noCC'					=> 'Centro de costes no asignado',
    'IP-Pools'					=> 'IP-Pools',
    'Type of Pool'				=> 'Type of Pool',
    'IP network'				=> 'IP network',
    'IP netmask'				=> 'IP netmask',
    'IP router'					=> 'IP router',
    'oid_list' 				=> 'Peligro: OIDs que no existen actualmente en la Base de Datods son desechados! Por favor, antes actualice el Archivo MiB!',
    'Phone tariffs'				=> 'Phone tariffs',
    'External Identifier'		=> 'External Identifier',
    'Usable'					=> 'Usable',
    'password_change'		=> 'Cambia Contrasenia',
    'password_confirm'		=> 'Confirme Contrasenia',
    'phonenumber_nr_change_hlkomm' => 'Por favor, ten en cuenta que los futuros registros de llamada no puedan ser asignados a este contrato nunca mas, cuando haya cambiado este numero. Esto debido a que HL Komm o Pyur solo manda el numero telefonico con los registros de datos de llamada.',
    'phonenumber_overlap_hlkomm' => 'Este numero existe o existio entre el/los :delay mes/meses. Como HL Komm o Pyur solo envia el numero telefonico dentro de los registros de datos de llamada, no sera posible de asignar poisbles llamadas hechas, al contrato apropiado nunca mas! Esto puede resultar en cobros equivocados. Por favor, solo agregue este numero si es un numero de prueba o si esta seguro de que no habran llamadas a cobrar nunca mas.',
    'show_ags' 				=> 'Muestra Ag Campo Seleccionado en la Pagina de Contrato',
    'snmp_query_failed' 	=> 'Consulta SNMP fallo debido a los siguientes OIDs: ',
    'Billing Cycle'				=> 'Billing Cycle',
    'Bundled with VoIP product?'=> 'Bundled with VoIP product?',
    'Price (Net)'				=> 'Price (Net)',
    'Number of Cycles'			=> 'Number of Cycles',
    'Product Entry'				=> 'Product Entry',
    'Qos (Data Rate)'			=> 'Qos (Data Rate)',
    'with Tax calculation ?'	=> 'with Tax calculation ?',
    'Phone Sales Tariff'		=> 'Phone Sales Tariff',
    'Phone Purchase Tariff'		=> 'Phone Purchase Tariff',
    'sr_repeat' 			=> 'Repita para cuentas SEPA(s):', // Settlementrun repeat
    'Account Holder'			=> 'Account Holder',
    'Account Name'				=> 'Account Name',
    'Choose Call Data Record template file'	=> 'Choose Call Data Record template file',
    'Choose invoice template file'			=> 'Choose invoice template file',
    'CostCenter'				=> 'CostCenter',
    'Creditor ID'				=> 'Creditor ID',
    'Institute'					=> 'Institute',
    'Invoice Headline'			=> 'Invoice Headline',
    'Invoice Text for negative Amount with Sepa Mandate'	=> 'Invoice Text for negative Amount with Sepa Mandate',
    'Invoice Text for negative Amount without Sepa Mandate'	=> 'Invoice Text for negative Amount without Sepa Mandate',
    'Invoice Text for positive Amount with Sepa Mandate'	=> 'Invoice Text for positive Amount with Sepa Mandate',
    'Invoice Text for positive Amount without Sepa Mandate'	=> 'Invoice Text for positive Amount without Sepa Mandate',
    'SEPA Account'				=> 'SEPA Account',
    'SepaAccount'				=> 'SepaAccount', // siehe Companies
    'upload_dependent_mib_err' => "Por favor, antes establezca el ':name' dependiente!! (de otra manera, OIDs no podran ser traducidos)",
    'Upload CDR template'		=> 'Upload CDR template',
    'Upload invoice template'	=> 'Upload invoice template',
    'user_settings'			=> 'Configuracion de Usuario',
    'user_glob_settings'	=> 'Configuracion Global de Usuarios',

    'voip_extracharge_default' => 'Cargos Extra de Llamads Voip por defecto en %',
    'voip_extracharge_mobile_national' => 'Cargos Extra de Llamadas mobiles nacionales Voip en %',
    'General'				=> 'General',
    'Verified'				=> 'Verified',
    'tariff'				=> 'tariff',
    'item'					=> 'item',
    'sepa'					=> 'sepa',
    'no_sepa'				=> 'no_sepa',
    'Call_Data_Records'		=> 'Call_Data_Records',
    'Y' 					=> 'anio|anios',
    'accounting'			=> 'accounting',
    'booking'				=> 'booking',
    'DD'					=> 'DD',
    'DC'					=> 'DC',
    'salesmen_commission'	=> 'salesmen_commission',
    'Assign Role'				=> 'Asignar Roles',
    'Load Data...' 			=> 'Load Data...',
    'Clean up directory...' => 'Clean up directory...',
    'Associated SEPA Account'	=> 'Associated SEPA Account',
    'Month to create Bill'		=> 'Month to create Bill',
    'Choose logo'			=> 'Choose logo',
    'Directorate'			=> 'Directorate',
    'Mail address'			=> 'Mail address',
    'Management'			=> 'Management',
    'Registration Court 1'	=> 'Registration Court 1',
    'Registration Court 2'	=> 'Registration Court 2',
    'Registration Court 3'	=> 'Registration Court 3',
    'Sales Tax Id Nr'		=> 'Sales Tax Id Nr',
    'Tax Nr'				=> 'Tax Nr',
    'Transfer Reason for Invoices'	=> 'Transfer Reason for Invoices',
    'Upload logo'			=> 'Upload logo',
    'Web address'			=> 'Web address',
    'Zip'					=> 'Zip',
    'Commission in %'		=> 'Commission in %',
    'Product List'			=> 'Product List',
    'Already recurring ?' 	=> 'Already recurring ?',
    'Date of Signature' 	=> 'Date of Signature',
    'Disable temporary' 	=> 'Disable temporary',
    'Reference Number' 		=> 'Reference Number',
    'Bank Bank Institutee' 		=> 'Bank Institute',
    'Contractnr'			=> 'Contractnr',
    'Create Invoices' 		=> 'Create Invoices',
    'Invoicenr'				=> 'Invoicenr',
    'Calling Number'		=> 'Calling Number',
    'Called Number'			=> 'Called Number',
    'Target Month'			=> 'Target Month',
    'Date'					=> 'Date',
    'Count'					=> 'Count',
    'Tax'					=> 'Tax',
    'RCD'					=> 'RCD',
    'Currency'				=> 'Currency',
    'Gross'					=> 'Gross',
    'Net'					=> 'Net',
    'MandateID'				=> 'MandateID',
    'MandateDate'			=> 'MandateDate',
    'Commission in %'		=> 'Commission in %',
    'Total Fee'				=> 'Total Fee',
    'Commission Amount'		=> 'Commission Amount',
    'Zip Files' 			=> 'Zip Files',
    'Concatenate invoices'  => 'Concatenate invoices',
    'primary'				=> 'primary',
    'secondary'				=> 'secondary',
    'disabled'				=> 'disabled',
    'Value (deprecated)'          => 'Value (deprecated)',
    'Priority (lower runs first)' => 'Priority (lower runs first)',
    'Priority' 				=> 'Priority',
    'Title' 				=> 'Title',
    'Created at'			=> 'Created at',
    'Activation date'       => 'Activation date',
    'Deactivation date'     => 'Deactivation date',
    'SIP domain'            => 'SIP domain',
    'Created at' 			=> 'Created at',
    'Last status update'	=> 'Last status update',
    'Last user interaction' => 'Last user interaction',
    'Method'				=> 'Method',
    'Ordertype ID'			=> 'Ordertype ID',
    'Ordertype'				=> 'Ordertype',
    'Orderstatus ID'		=> 'Orderstatus ID',
    'Orderstatus'			=> 'Orderstatus',
    'Orderdate'				=> 'Orderdate',
    'Ordercomment'			=> 'Ordercomment',
    'Envia customer reference' => 'Envia customer reference',
    'Envia contract reference' => 'Envia contract reference',
    'Contract ID'			=> 'Contract ID',
    'Phonenumber ID'		=> 'Phonenumber ID',
    'Related order ID'		=> 'Related order ID',
    'Related order type'	=> 'Related order type',
    'Related order created' => 'Related order created',
    'Related order last updated' => 'Related order last updated',
    'Related order deleted'	=> 'Related order deleted',
    'Envia Order'			=> 'Envia Order',
    'Document type'			=> 'Document type',
    'Upload document'		=> 'Upload document',
    'Call Start'			=> 'Call Start',
    'Call End'				=> 'Call End',
    'Call Duration/s'		=> 'Call Duration/s',
    'min. MOS'				=> 'min. MOS',
    'Packet loss/%'			=> 'Packet loss/%',
    'Jitter/ms'				=> 'Jitter/ms',
    'avg. Delay/ms'			=> 'avg. Delay/ms',
    'Caller (-> Callee)'	=> 'Caller (-> Callee)',
    '@Domain'				=> '@Domain',
    'min. MOS 50ms'			=> 'min. MOS 50ms',
    'min. MOS 200ms'		=> 'min. MOS 200ms',
    'min. MOS adaptive 500ms'	=> 'min. MOS adaptive 500ms',
    'avg. MOS 50ms'			=> 'avg. MOS 50ms',
    'avg. MOS 200ms'		=> 'avg. MOS 200ms',
    'avg. MOS adaptive 500ms'	=> 'avg. MOS adaptive 500ms',
    'Received Packets'		=> 'Received Packets',
    'Lost Packets'			=> 'Lost Packets',
    'avg. Delay/ms'			=> 'avg. Delay/ms',
    'avg. Jitter/ms'		=> 'avg. Jitter/ms',
    'max. Jitter/ms'		=> 'max. Jitter/ms',
    '1 loss in a row'		=> '1 loss in a row',
    '2 losses in a row'		=> '2 losses in a row',
    '3 losses in a row'		=> '3 losses in a row',
    '4 losses in a row'		=> '4 losses in a row',
    '5 losses in a row'		=> '5 losses in a row',
    '6 losses in a row'		=> '6 losses in a row',
    '7 losses in a row'		=> '7 losses in a row',
    '8 losses in a row'		=> '8 losses in a row',
    '9 losses in a row'		=> '9 losses in a row',
    'PDV 50ms - 70ms'		=> 'PDV 50ms - 70ms',
    'PDV 70ms - 90ms'		=> 'PDV 70ms - 90ms',
    'PDV 90ms - 120ms'		=> 'PDV 90ms - 120ms',
    'PDV 120ms - 150ms'		=> 'PDV 120ms - 150ms',
    'PDV 150ms - 200ms'		=> 'PDV 150ms - 200ms',
    'PDV 200ms - 300ms'		=> 'PDV 200ms - 300ms',
    'PDV >300 ms'			=> 'PDV >300 ms',
    'Callee (-> Caller)'	=> 'Callee (-> Caller)',
    'Credit'                    => 'Credit',
    'Other'                     => 'Other',
    'Once'                      => 'Once',
    'Monthly'                   => 'Monthly',
    'Quarterly'                 => 'Quarterly',
    'Yearly'                    => 'Yearly',
    'NET'                       => 'NET',
    'CMTS'                      => 'CMTS',
    'DATA'                      => 'DATA',
    'CLUSTER'                   => 'CLUSTER',
    'NODE'                      => 'NODE',
    'AMP'                       => 'AMP',
    'None'                      => 'None',
    'Null'                      => 'Null',
    'generic'                   => 'generic',
    'network'                   => 'network',
    'vendor'                    => 'vendor',
    'user'                      => 'user',
    'Yes'                       => 'Yes',
    'No'                        => 'No',
];
