<?php

return [

    /*
    |--------------------------------------------------------------------------
    | All other Language Lines - TODO: split descriptions and messages?
    |--------------------------------------------------------------------------
    */
    'Active'					=> 'Active',
    'Active?'					=> 'Active?',
    'Additional Options'		=> 'Additional Options',
    'Address Line 1'			=> 'Address Line 1',
    'Address Line 2'			=> 'Address Line 2',
    'Address Line 3'			=> 'Address Line 3',
    'Assigned'					=> 'Assigned',
    'BIC'						=> 'BIC',
    'Bank Account Holder'		=> 'Bank Account Holder',
    'Birthday'					=> 'Fecha nacimiento',
    'City'						=> 'Ciudad',
    'Choose KML file'			=> 'Choose KML file',

    'Company'					=> 'Empresa',
    'Contract Number'			=> 'Número de contrato',
    'Contract Start'			=> 'Contract Start',
    'Contract End'				=> 'Contract End',
    'Contract valid' 			=> 'Contract valid',
    'Contract'					=> 'Contrato',
    'Contract List'				=> 'Lista de contratos',
    'Contracts'					=> 'Contratos',
    'International prefix'		=> 'International prefix',
    'Country code'				=> 'Country code',
    // Descriptions of Form Fields in Edit/Create
    'accCmd_error_noCC' 	=> 'Contrato :contract_nr [ID :contract_id] no tiene asignado Costo. No se creará ninguna factura para el cliente.',
    'accCmd_invoice_creation_deactivated' => 'Los siguientes contratos han desactivado la creación de facturas: :contractnrs',
    'Create'					=> 'Crear',
    'accCmd_processing' 	=> 'El Acuerdo es ejecutado. Por favor espere hasta que este proceso haya finalizado.',
    'Date of installation address change'	=> 'Date of installation address change',
    'Delete'					=> 'Eliminar',
    'Day'						=> 'Día',
    'Description'				=> 'Descripción 	',
    'Device'					=> 'Dispositivo',
    'accCmd_notice_CDR' 	=> 'El Contrato :contract_nr [ID :contract_id] tiene registros de datos de llamadas pero no tiene asignada una tarifa Voip válida',
    'Device List'				=> 'Device List',
    'Device Type'				=> 'Device Type',
    'Device Type List'			=> 'Device Type List',
    'Devices'					=> 'Devices',
    'DeviceTypes'				=> 'DeviceTypes',
    'District'					=> 'District',
    'Edit'						=> 'Edit',
    'Edit '						=> 'Edit ',
    'Endpoints'					=> 'Endpoints',
    'Endpoints List'			=> 'Endpoints List',
    'Entry'						=> 'Entry',
    'alert' 				=> 'Atencion!',
    'ALL' 					=> 'ALL',
    'E-Mail Address'			=> 'E-Mail Address',
    'First IP'					=> 'First IP',
    'Firstname'					=> 'Firstname',
    'Fixed IP'					=> 'Fixed IP',
    'Force Restart'				=> 'Force Restart',
    'Geocode origin'			=> 'Geocode origin',
    'IBAN'						=> 'IBAN',
    'Internet Access'			=> 'Internet Access',
    'Inventar Number'			=> 'Inventar Number',
    'Call Data Record'		=> 'Registo de Datos de Llamada',
    'IP address'				=> 'IP address',
    'Language'					=> 'Language',
    'Lastname'					=> 'Lastname',
    'Last IP'					=> 'Last IP',
    'ccc'					=> 'Centro de Control del Cliente',
    'MAC Address'				=> 'MAC Address',
    'Main Menu'					=> 'Main Menu',
    'Maturity' 					=> 'Maturity',
    'cdr' 					=> 'cdr',
    'cdr_discarded_calls' 	=> "CDR: Contrato Nr o ID ':contractnr' no encontradas en la base de datos - :count llamadas del numero telefonico :phonenr con precio de :price :currency son desechados.",
    'cdr_missing_phonenr' 	=> 'Parse CDR.csv: Detectados registros de datos de llamada con phonenr :phonenr que esta faltando en la base de datos. Descartar :count llamadas con cargo de :price :currency.',
    'cdr_missing_reseller_data' => 'Faltan Datos del Revendedor en Archivo de Entorno!',
    'cdr_offset' 			=> 'CDR diferencia de tiempo de Factura en Meses',
    'close' 				=> 'Cerrar',
    'contract_early_cancel' => 'En serio quiere cancelar este contrato antes de que el fin de termino de las tarifas :date sea alcanzado?',
    'contract_nr_mismatch'  => 'Could not find the next contract number because the database query failed. This is due to the following contracts having a contract number that does not belong to their selected cost center: :nrs',
    'contract_numberrange_failure' => 'No free contract number for selected costcenter available!',
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
    'Month'						=> 'Month',
    'envia_interaction'	 	=> 'Orden Envia requiere Interaccion|Ordenes Envia requieren Interaccion',
    'Net'						=> 'Net',
    'Netmask'					=> 'Netmask',
    'Network Access'			=> 'Network Access',
    'no' 						=> 'no',
    'Number'					=> 'Number',
    'Options'					=> 'Options',
    'or: Upload KML file'		=> 'or: Upload KML file',
    'Parent Device Type'		=> 'Parent Device Type',
    'Parent Object'				=> 'Parent Object',
    'Period of Notice' 			=> 'Period of Notice',
    'Password'					=> 'Password',
    'Confirm Password'					=> 'Confirm Password',
    'Phone'						=> 'Phone',
    'Phone ID next month'		=> 'Phone ID next month',
    'Phonenumber'				=> 'Phonenumber',
    'Phonenumbers'				=> 'Phonenumbers',
    'Phonenumbers List'			=> 'Phonenumbers List',
    'Postcode'					=> 'Postcode',
    'Prefix Number'				=> 'Prefix Number',
    'Price'						=> 'Price',
    'Public CPE'				=> 'Public CPE',
    'QoS next month'			=> 'QoS next month',
    'Real Time Values'			=> 'Real Time Values',
    'Remember Me'				=> 'Remember Me',
    'Salutation'				=> 'Salutation',
    'Save'						=> 'Save',
    'Save All'					=> 'Save All',
    'Save / Restart'			=> 'Save / Restart',
    'Serial Number'				=> 'Serial Number',
    'Sign me in' 				=> 'Sign me in',
    'State'						=> 'State',
    'Street'					=> 'Street',
    'Typee'						=> 'Type',
    'Unexpected exception' 		=> 'Unexpected exception',
    'US level' 					=> 'US level',
    'Username'					=> 'Username',
    'Users'						=> 'Users',
    'Vendor'					=> 'Vendor',
    'Year'						=> 'Year',
    'yes' 						=> 'yes',
    'home' 						=> 'Hogar',
    'indices_unassigned' 		=> 'Algunos de los Indices asignados no pudieron ser asignados al correspondiente parametro! Solo no estan siendo usadas. Usted puede eliminarlas o mantenerlas para despues. Solo compare la lista de parametros del tipo de elemento de red con la lista de indices aqui.',
    'item_credit_amount_negative' => 'Una cantidad de credito negativa se convierte a debito! Esta seguro que el cliente debe ser endeudado?',
    'invoice' 					=> 'Factura',
    'Global Config'				=> 'Global Config',
    'GlobalConfig'				=> 'GlobalConfig',
    'VOIP'						=> 'VOIP',
    'Customer Control Center'	=> 'Customer Control Center',
    'Provisioning'				=> 'Provisioning',
    'BillingBase'				=> 'BillingBase',
    'Ccc' 						=> 'Ccc',
    'HfcBase' 					=> 'HfcBase',
    'ProvBase' 					=> 'ProvBase',
    'ProvVoip' 					=> 'ProvVoip',
    'HFC'						=> 'HFC',
    'Rank'						=> 'Rank',
    'Assign Users'				=> 'Assign Users',
    'Invoices'					=> 'Facturas',
    'Ability'					=> 'Ability',
    'Allow'						=> 'Allow',
    'Allow to'					=> 'Allow to',
    'Forbid'					=> 'Forbid',
    'Forbid to'					=> 'Forbid to',
    'Save Changes'				=> 'Save Changes',
    'Manage'					=> 'Manage',
    'View'						=> 'View',
    'Create'					=> 'Create',
    'Update'					=> 'Update',
    'Delete'					=> 'Delete',
    'Help'						=> 'Help',
    'All abilities'				=> 'All abilities',
    'View everything'			=> 'View everything',
    'Use api'					=> 'Use api',
    'See income chart'			=> 'See income chart',
    'View analysis pages of modems'	=> 'View analysis pages of modems',
    'View analysis pages of cmts' => 'View analysis pages of cmts',
    'Download settlement runs'	=> 'Download settlement runs',
    'Not allowed to acces this user' => 'Not allowed to acces this user',
    'log_out'				=> 'Cierre de sesion',
    'System Log Level'			=> 'System Log Level',
    'Headline 1'				=> 'Headline 1',
    'Headline 2'				=> 'Headline 2',
    'M' 					=> 'mes|meses',
    'Mark solved'			=> 'Marcar como resuelto?',
    'missing_product' 		=> 'Falta Producto!',
    'modem_eventlog_error'	=> 'Modem eventlog no encontrado',
    'modem_force_restart_button_title' => 'Solo reinicia el modem. No guarda algun dato cambiado!',
    'CDR retention period' 		=> 'CDR retention period',
    'Day of Requested Collection Date'	=> 'Day of Requested Collection Date',
    'Tax in %'					=> 'Tax in %',
    'Invoice Number Start'		=> 'Invoice Number Start',
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
    'Upload Template'			=> 'Upload Template',
    'SNMP Read Only Community'	=> 'SNMP Read Only Community',
    'SNMP Read Write Community'	=> 'SNMP Read Write Community',
    'Provisioning Server IP'	=> 'Provisioning Server IP',
    'Domain Name for Modems'	=> 'Domain Name for Modems',
    'Notification Email Address'=> 'Notification Email Address',
    'DHCP Default Lease Time'	=> 'DHCP Default Lease Time',
    'DHCP Max Lease Time'		=> 'DHCP Max Lease Time',
    'Start ID Contracts'		=> 'Start ID Contracts',
    'Start ID Modems'			=> 'Start ID Modems',
    'Start ID Endpoints'		=> 'Start ID Endpoints',
    'Downstream rate coefficient' => 'Downstream rate coefficient',
    'Upstream rate coefficient' => 'Upstream rate coefficient',
    'modem_no_diag'			=> 'Diagrams no disponibles',
    'Start ID MTA´s'			=> 'Start ID MTA´s',
    'modem_lease_error'		=> 'No se encontro Arrendamientos dhcp valido',
    'modem_lease_valid' 	=> 'Modem tiene un Arrendamientos dhcp valido',
    'modem_log_error' 		=> 'Modem no fue registrado en el Servidor - Ningun registro encontrado',
    'modem_configfile_error'=> 'Archivo de configuracion del Modem no hallado',
    'Academic Degree'			=> 'Academic Degree',
    'modem_offline'			=> 'Modem esta Offline',
    'Contract number'			=> 'Contract number',
    'Contract Nr'				=> 'Contract Nr',
    'Contract number legacy'	=> 'Contract number legacy',
    'Cost Center'				=> 'Cost Center',
    'Create Invoice'			=> 'Create Invoice',
    'Customer number'			=> 'Customer number',
    'Customer number legacy'	=> 'Customer number legacy',
    'Department'				=> 'Department',
    'End Date' 					=> 'End Date',
    'House Number'				=> 'House Number',
    'House Nr'					=> 'House Nr',
    'Salesman'					=> 'Salesman',
    'Start Date' 				=> 'Start Date',
    'modem_restart_error' 		=> 'No se pudo reiniciar Modem! (offline?)',
    'Contact Persons' 			=> 'Contact Persons',
    'modem_restart_success_cmts' => 'Modem reiniciado via CMTS',
    'Accounting Text (optional)'=> 'Accounting Text (optional)',
    'Cost Center (optional)'	=> 'Cost Center (optional)',
    'Credit Amount' 			=> 'Credit Amount',
    'modem_restart_success_direct' => 'Modem reiniciado directamente por SNMP',
    'Item'						=> 'Item',
    'Items'						=> 'Items',
    'modem_save_button_title' 	=> 'Guarda datos cambiados. Determina una nueva geoposition cuando los datos de direccion han sido cambiados (y lo asigna a un nuevo MPR si es necesario). Reconstruye el archivo de configuracion y reinicia el modem si por lo menos algo de lo siguiente ha sido cambiado: IP Publica, acceso a la red, archivo de configuracion, QoS, direccion-MAC',
    'Product'					=> 'Product',
    'Start date' 				=> 'Start date',
    'Active from start date' 	=> 'Active from start date',
    'Valid from'				=> 'Valid from',
    'Valid to'					=> 'Valid to',
    'Valid from fixed'			=> 'Valid from fixed',
    'Valid to fixed'			=> 'Valid to fixed',
    'modem_statistics'		=> 'Cifra Online / Offline Modems',
    'Configfile'				=> 'Configfile',
    'Mta'						=> 'Mta',
    'month' 				=> 'Mes',
    'Configfiles'				=> 'Configfiles',
    'Choose Firmware File'		=> 'Choose Firmware File',
    'Config File Parameters'	=> 'Config File Parameters',
    'or: Upload Firmware File'	=> 'or: Upload Firmware File',
    'Parent Configfile'			=> 'Parent Configfile',
    'Public Use'				=> 'Public Use',
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
