<?php

return [

	/*
	|--------------------------------------------------------------------------
	| All other Language Lines - TODO: split descriptions and messages?
	|--------------------------------------------------------------------------
	*/

	// Descriptions of Form Fields in Edit/Create
	'accCmd_error_noCC' 	=> "Contrato :contract_nr [ID :contract_id] no tiene asignado Costo. No se creará ninguna factura para el cliente.",
	'accCmd_invoice_creation_deactivated' => "Los siguientes contratos han desactivado la creación de facturas: :contractnrs",
	'accCmd_processing' 	=> 'El Acuerdo es ejecutado. Por favor espere hasta que este proceso haya finalizado.',
	'accCmd_notice_CDR' 	=> "El Contrato :contract_nr [ID :contract_id] tiene registros de datos de llamadas pero no tiene asignada una tarifa Voip válida",
	'alert' 				=> 'Atencion!',
	'ALL' 					=> 'ALL',
	'Call Data Record'		=> 'Registo de Datos de Llamada',
	'ccc'					=> 'Centro de Control del Cliente',
	'cdr' 					=> 'cdr',
	'cdr_discarded_calls' 	=> "CDR: Contrato Nr o ID ':contractnr' no encontradas en la base de datos - :count llamadas del numero telefonico :phonenr con precio de :price :currency son desechados.",
	'cdr_missing_phonenr' 	=> "Parse CDR.csv: Detectados registros de datos de llamada con phonenr :phonenr que esta faltando en la base de datos. Descartar :count llamadas con cargo de :price :currency.",
	'cdr_missing_reseller_data' => 'Faltan Datos del Revendedor en Archivo de Entorno!',
	'cdr_offset' 			=> 'CDR diferencia de tiempo de Factura en Meses',
	'close' 				=> 'Cerrar',
	'contract_early_cancel' => 'En serio quiere cancelar este contrato antes de que el fin de termino de las tarifas :date sea alcanzado?',
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
	'envia_interaction'	 	=> 'Orden Envia requiere Interaccion|Ordenes Envia requieren Interaccion',
	'home' 					=> 'Hogar',
	'indices_unassigned' 	=> 'Algunos de los Indices asignados no pudieron ser asignados al correspondiente parametro! Solo no estan siendo usadas. Usted puede eliminarlas o mantenerlas para despues. Solo compare la lista de parametros del tipo de elemento de red con la lista de indices aqui.',
	'item_credit_amount_negative' => 'Una cantidad de credito negativa se convierte a debito! Esta seguro que el cliente debe ser endeudado?',
	'invoice' 				=> 'Factura',
	'Invoices'				=> 'Facturas',
	'log_out'				=> 'Cierre de sesion',
	'M' 					=> 'mes|meses',
	'Mark solved'			=> 'Marcar como resuelto?',
	'missing_product' 		=> 'Falta Producto!',
	'modem_eventlog_error'	=> 'Modem eventlog no encontrado',
	'modem_force_restart_button_title' => 'Solo reinicia el modem. No guarda algun dato cambiado!',
	'modem_monitoring_error'=> 'Esto podría deberse a que el módem no estaba en línea hasta ahora. Tenga en cuenta que los diagramas solo están disponibles
desde el punto en que un módem estaba en línea. Si todos los diagramas no se muestran correctamente, entonces debe ser un
 problema más grande y debería haber una mala configuración de cactus. Por favor, considere comunicar al administrador en problemas mayores.',
	'modem_no_diag'			=> 'Diagrams no disponibles',
	'modem_lease_error'		=> 'No se encontro Arrendamientos dhcp valido',
	'modem_lease_valid' 	=> 'Modem tiene un Arrendamientos dhcp valido',
	'modem_log_error' 		=> 'Modem no fue registrado en el Servidor - Ningun registro encontrado',
	'modem_configfile_error'=> 'Archivo de configuracion del Modem no hallado',
	'modem_offline'			=> 'Modem esta Offline',
	'modem_restart_error' 		=> 'No se pudo reiniciar Modem! (offline?)',
	'modem_restart_success_cmts' => "Modem reiniciado via CMTS",
	'modem_restart_success_direct' => "Modem reiniciado directamente por SNMP",
	'modem_save_button_title' 	=> 'Guarda datos cambiados. Determina una nueva geoposition cuando los datos de direccion han sido cambiados (y lo asigna a un nuevo MPR si es necesario). Reconstruye el archivo de configuracion y reinicia el modem si por lo menos algo de lo siguiente ha sido cambiado: IP Publica, acceso a la red, archivo de configuracion, QoS, direccion-MAC',
	'modem_statistics'		=> 'Cifra Online / Offline Modems',
	'month' 				=> 'Mes',
	'mta_configfile_error'	=> 'MTA archivo de configuracion no encontrado',
	'noCC'					=> 'Centro de costes no asignado',
	'oid_list' 				=> 'Peligro: OIDs que no existen actualmente en la Base de Datods son desechados! Por favor, antes actualice el Archivo MiB!',
	'password_change'		=> 'Cambia Contrasenia',
	'password_confirm'		=> 'Confirme Contrasenia',
	'phonenumber_nr_change_hlkomm' => 'Por favor, ten en cuenta que los futuros registros de llamada no puedan ser asignados a este contrato nunca mas, cuando haya cambiado este numero. Esto debido a que HL Komm o Pyur solo manda el numero telefonico con los registros de datos de llamada.',
	'phonenumber_overlap_hlkomm' => 'Este numero existe o existio entre el/los :delay mes/meses. Como HL Komm o Pyur solo envia el numero telefonico dentro de los registros de datos de llamada, no sera posible de asignar poisbles llamadas hechas, al contrato apropiado nunca mas! Esto puede resultar en cobros equivocados. Por favor, solo agregue este numero si es un numero de prueba o si esta seguro de que no habran llamadas a cobrar nunca mas.',
	'show_ags' 				=> 'Muestra Ag Campo Seleccionado en la Pagina de Contrato',
	'snmp_query_failed' 	=> 'Consulta SNMP fallo debido a los siguientes OIDs: ',
	'sr_repeat' 			=> 'Repita para cuentas SEPA(s):', // Settlementrun repeat
	'upload_dependent_mib_err' => "Por favor, antes establezca el ':name' dependiente!! (de otra manera, OIDs no podran ser traducidos)",
	'user_settings'			=> 'Configuracion de Usuario',
	'user_glob_settings'	=> 'Configuracion Global de Usuarios',

	'voip_extracharge_default' => 'Cargos Extra de Llamads Voip por defecto en %',
	'voip_extracharge_mobile_national' => 'Cargos Extra de Llamadas mobiles nacionales Voip en %',
	'Y' 					=> 'anio|anios',
	'Assign Role'				=> 'Asignar Roles',
];
