<?php

return [

    /*
    |--------------------------------------------------------------------------
    | All other Language Lines - TODO: split descriptions and messages?
    |--------------------------------------------------------------------------
    */
    'Academic degree'           => 'Grado Académico',
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
    'Business'                  => 'Negocio',
    'City'						=> 'Ciudad',
    'Choose KML file'			=> 'Elegir archivo KML',

    'Company'					=> 'Empresa',
    'conninfo' => [
        'error' => 'Error en la creación del PDF: ',
        'missing_company' => 'La cuenta SEPA ":var" no tiene una compañía asignada.',
        'missing_costcenter' => 'El contrato no tiene un centro de costos asignado.',
        'missing_sepaaccount' => 'El centro de costos ":var" no tiene una cuenta SEPA asignada.',
        'missing_template' => 'No hay ninguna plantilla para las informaciones de conexión seleccionadas en la empresa ":var".',
        'missing_template_file' => 'El archivo de la plantilla seleccionada para las informaciones de conexión en la empresa ":var" no existe.',
        'read_failure' => 'Plantilla vacía o error al leerla',
    ],
    'Contract Number'			=> 'Número de contrato',
    'Contract Start'			=> 'Inicio de Contrato',
    'Contract End'				=> 'Fin de Contrato',
    'Contract valid' 			=> 'Contrato válido',
    'Contract'					=> 'Contrato',
    'Contract List'				=> 'Lista de contrato',
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
    'Directory assistance'      => 'Asistencia al directorio',
    'District'					=> 'Distrito',
    'Edit'						=> 'Editar',
    'Edit '						=> 'Editar',
    'Endpoints'					=> 'Punto final:',
    'Endpoints List'			=> 'Lista de Puntos finales',
    'Entry'						=> 'Entrada',
    'alert' 				=> 'Atencion!',
    'ALL' 					=> 'TODOS',
    'E-Mail Address'			=> 'Dirección de correo electrónico',
    'Entry electronic media'    => 'Medios electrónicos de entrada',
    'Entry in print media'      => 'Entrada en medios impresos',
    'Entry type'                => 'Tipo de entrada',
    'First IP'					=> 'Primer IP',
    'Firstname'					=> 'Nombres',
    'Fixed IP'					=> 'IP fija',
    'Force Restart'				=> 'Forzar reinicio',
    'Geocode origin'			=> 'Origen de geocodificación',
    'House number'              => 'Numero de la casa',
    'IBAN'						=> 'IBAN',
    'Internet Access'			=> 'Acceso a Internet',
    'Inventar Number'			=> 'Número de inventario',
    'Call Data Record'		=> 'Registo de Datos de Llamada',
    'IP address'				=> 'Dirección IP',
    'Language'					=> 'Idioma',
    'Lastname'					=> 'Apellidos',
    'Last IP'					=> 'último IP',
    'ccc'					    => 'Centro de Control del Cliente',
    'page_html_header'		    => 'Centro de control de clientes',
    'pdflatex' => [
        'default' => 'Error al ejecutar pdfLaTeX-código de retorno:: var',
        'missing' => 'Comando ilegal-pdfLaTeX no instalado!',
        'syntax'  => 'pdfLaTeX: error de sintaxis en la plantilla Tex (marcador de posición mal escrito?): var',
    ],
    'MAC Address'				=> 'Dirección MAC',
    'Main Menu'					=> 'Menú Principal',
    'Maturity' 					=> 'Vencimiento',
    'cdr' 					=> 'CDR',
    'cdr_discarded_calls' 	=> "CDR: Contrato Nr o ID ':contractnr' no encontradas en la base de datos - :count llamadas del numero telefonico :phonenr con precio de :price :currency son desechados.",
    'cdr_missing_phonenr' 	=> 'Parse CDR.csv: Detectados registros de datos de llamada con phonenr :phonenr que esta faltando en la base de datos. Descartar :count llamadas con cargo de :price :currency.',
    'cdr_missing_reseller_data' => 'Faltan Datos del Revendedor en Archivo de Entorno!',
    'cdr_offset' 			=> 'CDR diferencia de tiempo de Factura en Meses',
    'close' 				=> 'Cerrar',
    'contract' => [
        'concede_credit' => 'Hay artículos cobrados anualmente que ya se han cobrado (a precio completo). ¡Por favor, compruebe si el cliente debería recibir un crédito!',
        'early_cancel' => '¿Realmente desea cancelar este contrato antes de que se alcancen los aranceles finales del término :date?',
        ],
    'iteM' => [
        'concede_credit' => 'Este artículo ya fue cobrado (a precio completo). ¡Por favor, compruebe si el cliente debería recibir un crédito!',
    ],
    'contract_nr_mismatch'  => 'No se pudo encontrar el siguiente número de contrato porque la consulta de la base de datos falló. Esto se debe a que los siguientes contratos tienen un número de contrato que no pertenece a su centro de costo seleccionado: :nrs. Cambie el centro de costos o permita que el sistema asigne un nuevo número de contrato para estos contratos.',
    'contract_numberrange_failure' => 'No se encuentra disponible el numero de contrato para el centro de costos seleccionado!',
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
    'Internet Access'			=> 'Acceso a Internet',
    'no' 						=> 'no',
    'Noble rank'                => 'Rango noble',
    'Nobiliary particle'        => 'Partícula nobiliar',
    'Number'					=> 'Número',
    'Number usage'              => 'Uso del número',
    'Options'					=> 'Opciones',
    'or: Upload KML file'		=> 'o: subir archivo KML',
    'Other name suffix'         => 'Otro sufijo de nombre',
    'Parent Device Type'		=> 'Tipo de dispositivo principal',
    'Parent Object'				=> 'Objeto primario',
    'Period of Notice' 			=> 'Plazo de preaviso',
    'Password'					=> 'Contraseña',
    'Confirm Password'					=> 'Confirme su contraseña',
    'Phone'						=> 'Teléfono',
    'Phone ID next month'		=> 'ID de teléfono del mes siguiente',
    'Phonenumber'				=> 'Número de teléfono',
    'Phonenumbers'				=> 'Numeros telefonicos',
    'Phonenumbers List'			=> 'Lista de números telefonicos',
    'Postcode'					=> 'Código postal',
    'Prefix Number'				=> 'Número de Prefijo ',
    'Price'						=> 'Precio',
    'Public CPE'				=> 'CPE con IP Público',
    'Publish address'           => 'Publicar la dirección',
    'QoS next month'			=> 'Calidad de servicio QoS',
    'Real Time Values'			=> 'Valores en tiempo real',
    'Remember Me'				=> 'Recordar mis datos',
    'Reverse search'            => 'Búsqueda inversa',
    'Salutation'                => 'Tratamiento',
    'Save'                      => 'Guardar',
    'Save All'                  => 'Guardar todo',
    'Save / Restart'            => 'Guardar / Reiniciar',
    'Serial Number'             => 'Número de serie',
    'Sign me in'                => 'Iniciar sesión',
    'snmp' => [
        'errors_walk' => 'Querying the following OIDs failed: :oids.',
        'errors_set' => 'Los siguientes parámetros no se pudieron establecer: :oids.',
        'missing_cmts' => 'Al grupo le falta un CMTS superior como dispositivo padre.',
        'undefined' => 'Para este tipo de elemento de red no hay un controlador definido.',
        'unreachable' => 'El dispositivo no es accesible vía el SNMP.',
    ],
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
    'BillingBase'				=> 'Configuración de Facturacion',
    'Ccc' 						=> 'Configuración de CCC',
    'HfcBase' 					=> 'Configuración de HFC',
    'ProvBase' 					=> 'Configuración de ProvBase',
    'ProvVoip' 					=> 'Configuración de ProvVoip',
    'ProvVoipEnvia' 			=> 'Configuración de ProvVoipEnvia',
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
    'All abilities'				=> 'Habilitar todas',
    'View everything'			=> 'Mostrar todo',
    'Use api'					=> 'Usar api',
    'See income chart'			=> 'Consulte la tabla de ingresos',
    'View analysis pages of modems'	=> 'Visualizar el estado de módems',
    'View analysis pages of cmts' => 'Visualizar el estado del CMTS',
    'Download settlement runs'	=> 'Descargar registros',
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
    'modem_reset_button_title' => 'Solo reinicia el módem. ¡No guarda ninguna información cambiada!',
    'CDR retention period' 		=> 'Período de retención de CDR',
    'Day of Requested Collection Date'	=> 'Fecha de facturación',
    'Tax in %'					=> 'Impuestos en %',
    'Invoice Number Start'		=> 'Inicio de número de facturación',
    'Split Sepa Transfer-Types'	=> 'Dividir los tipos de transferencia Sepa',
    'Mandate Reference'			=> 'Referencia del mandato',
    'e.g.: String - {number}'	=> 'por ejemplo: String - {number}',
    'Item Termination only end of month'=> 'Terminación del artículo sólo final de mes',
    'Language for settlement run' => 'Idioma del contrato',
    'Uncertain start/end dates for tariffs' => 'Fechas de inicio y fin incierto para los aranceles',
    'modem_monitoring_error'=> 'Esto podría deberse a que el módem no estaba en línea hasta ahora. Tenga en cuenta que los diagramas solo están disponibles
desde el punto en que un módem estaba en línea. Si todos los diagramas no se muestran correctamente, entonces debe ser un
 problema más grande y debería haber una mala configuración de cactus. Por favor, considere comunicar al administrador en problemas mayores.',
    'Connection Info Template'	=> 'Plantilla de información de conexión',
    'Upload Template'			=> 'Subir plantilla',
    'SNMP Read Only Community'	=> 'Comunidad SNMP de solo lectura',
    'SNMP Read Write Community'	=> 'Comunidad SNMP de lectura y escritura',
    'Provisioning Server IP'	=> 'IP del Servidor NMSprime',
    'Domain Name for Modems'	=> 'Nombre de dominio para CableModems',
    'Notification Email Address'=> 'Dirección de correo electrónico para notificaciónes',
    'DHCP Default Lease Time'	=> 'Tiempo de Arrendamiento del DHCP ',
    'DHCP Max Lease Time'		=> 'Tiempo máximo de Arrendamiento del DHCP ',
    'Start ID Contracts'		=> 'ID Inicio de contrato',
    'Start ID Modems'			=> 'Inicio de ID del CableModem',
    'Start ID Endpoints'		=> 'ID de Inicio para terminales "Endpoints"',
    'Downstream rate coefficient' => 'Coeficiente de velocidad de descarga',
    'Upstream rate coefficient' => 'Coeficiente de velocidad de subida',
    'Additional modem reset button' => 'Botón adicional de reinicio de módem',
    'modemAnalysis' => [
        'cfOutdated' => 'El módem no se ejecuta con el archivo configfile actual. La última descarga fue antes de la hora de compilación del archivo configfile.',
        'cpeMacMissmatch' => 'El estado del acceso a Internet y la telefonía no se pudo determinar, como mínimo 1 dirección CPE MAC difiere de los MACs de los MTAs asignados.',
        'fullAccess' => 'El acceso a Internet y la telefonía está permitido. (según el configfile)',
        'missingLD' => 'Info: La última descarga del archivo configfile fue hace mucho tiempo como para determinar si el módem ha incurrido en las configuraciones actuales.',
        'noNetworkAccess' => 'El acceso a internet y a la telefonía está bloqueado. (de acuerdo con el configfile)',
        'onlyVoip' => 'El acceso a internet está bloqueado. Sólo se permite la telefonía. (de acuerdo con el configfile)',
    ],
    'modem_no_diag'			=> 'Diagrams no disponibles',
    'Start ID MTA´s'			=> 'Inicio de ID para MTA\'s',
    'modem_lease_error'		=> 'No se encontro Arrendamientos dhcp valido',
    'modem_lease_valid' 	=> 'Modem tiene un Arrendamientos dhcp valido',
    'modem_log_error' 		=> 'Modem no fue registrado en el Servidor - Ningun registro encontrado',
    'modem_configfile_error'=> 'Archivo de configuracion del Modem no hallado',
    'Academic Degree'			=> 'Grado académico',
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
    'Valid from fixed'			=> 'Activo desde la fecha de inicio',
    'Valid to fixed'			=> 'Activo desde la fecha de inicio',
    'modem_statistics'		=> 'Cifra Online / Offline Modems',
    'Configfile'				=> 'Archivo de configuracion',
    'Mta'						=> 'MTA',
    'month' 				=> 'Mes',
    'Configfiles'				=> 'Archivo de configuración',
    'Choose Firmware File'		=> 'Seleccione el archivo de Firmware',
    'Config File Parameters'	=> 'Parámetros del archivo de configuración',
    'or: Upload Certificate File'	=> 'o: subir certificado',
    'or: Upload Firmware File'	=> 'o: subir archivo de Firmware',
    'Parent Configfile'			=> 'Archivo de configuración principal',
    'Public Use'				=> 'IP publica',
    'mta_configfile_error'	=> 'MTA archivo de configuracion no encontrado',
    'IpPool'						=> 'IpPool',
    'SNMP Private Community String'	=> 'Nombre para comunidad SNMP privado',
    'SNMP Public Community String'	=> 'Nombre para comunidad SNMP público',
    'noCC'					=> 'Centro de costes no asignado',
    'IP-Pools'					=> 'IP-Pools',
    'Type of Pool'				=> 'Tipo de Pool',
    'IP network'				=> 'Red IP',
    'IP netmask'				=> 'Máscara de red IP',
    'IP router'					=> 'IP del router',
    'oid_list' 				=> 'Peligro: OIDs que no existen actualmente en la Base de Datods son desechados! Por favor, antes actualice el Archivo MiB!',
    'Phone tariffs'				=> 'Tarifa telefonica',
    'External Identifier'		=> 'Identificador Externo',
    'Usable'					=> 'Disponible',
    'password_change'		=> 'Cambia Contrasenia',
    'password_confirm'		=> 'Confirme Contrasenia',
    'phonenumber_missing'       => 'Número de teléfono :phonenr del contrato :contractnr falta, pero :provider de llamadas cargadas.',
    'phonenumber_mismatch'      => 'Número de teléfono :phonenr no pertenece al contrato :contractnr. El contrato/cliente equivocado podría cobrarse por estas llamadas.',
    'phonenumber_nr_change_hlkomm' => 'Por favor, ten en cuenta que los futuros registros de llamada no puedan ser asignados a este contrato nunca mas, cuando haya cambiado este numero. Esto debido a que HL Komm o Pyur solo manda el numero telefonico con los registros de datos de llamada.',
    'phonenumber_overlap_hlkomm' => 'Este numero existe o existio entre el/los :delay mes/meses. Como HL Komm o Pyur solo envia el numero telefonico dentro de los registros de datos de llamada, no sera posible de asignar poisbles llamadas hechas, al contrato apropiado nunca mas! Esto puede resultar en cobros equivocados. Por favor, solo agregue este numero si es un numero de prueba o si esta seguro de que no habran llamadas a cobrar nunca mas.',
    'show_ags' 				=> 'Muestra Ag Campo Seleccionado en la Pagina de Contrato',
    'snmp_query_failed' 	=> 'Consulta SNMP fallo debido a los siguientes OIDs: ',
    'Billing Cycle'				=> 'Ciclo de Facturación',
    'Bundled with VoIP product?'=> '¿Incluido con el producto de VoIP?',
    'Calculate proportionately' => 'Calcular proporcionalmente',
    'Price (Net)'				=> 'Precio (neto)',
    'Number of Cycles'			=> 'Número de ciclos',
    'Product Entry'				=> 'Producto',
    'Qos (Data Rate)'			=> 'QoS (Data Rate)',
    'with Tax calculation ?'	=> '¿con cálculo de impuestos?',
    'Phone Sales Tariff'		=> 'Tarifa de ventas del teléfono',
    'Phone Purchase Tariff'		=> 'Tarifa de compra del teléfono',
    'sr_repeat' 			=> 'Repita para cuentas SEPA(s):', // Settlementrun repeat
    'Account Holder'			=> 'Poseedor de cuenta',
    'Account Name'				=> 'Nombre de la cuenta',
    'Choose Call Data Record template file'	=> 'Elegir archivo de plantilla de registro de datos llamada',
    'Choose invoice template file'			=> 'Elegir archivo de plantilla de facturación',
    'CostCenter'				=> 'Centro de Costo',
    'Creditor ID'				=> 'ID del acreedor',
    'Institute'					=> 'Institución',
    'Invoice Headline'			=> 'Titular de la factura',
    'Invoice Text for negative Amount with Sepa Mandate'	=> 'Texto de la factura de importe negativo con mandato Sepa',
    'Invoice Text for negative Amount without Sepa Mandate'	=> 'Texto de la factura de importe negativo sin mandato Sepa',
    'Invoice Text for positive Amount with Sepa Mandate'	=> 'Texto de la factura de importe positivo con mandato Sepa',
    'Invoice Text for positive Amount without Sepa Mandate'	=> 'Texto de la factura de importe positivo sin mandato Sepa',
    'SEPA Account'				=> 'Cuenta de SEPA',
    'SepaAccount'				=> 'Cuenta de Sepa', // siehe Companies
    'upload_dependent_mib_err' => "Por favor, antes establezca el ':name' dependiente!! (de otra manera, OIDs no podran ser traducidos)",
    'Upload CDR template'		=> 'Subir plantilla CDR',
    'Upload invoice template'	=> 'Cargar plantilla de facturación',
    'user_settings'			=> 'Configuracion de Usuario',
    'user_glob_settings'	=> 'Configuracion Global de Usuarios',

    'voip_extracharge_default' => 'Cargos Extra de Llamads Voip por defecto en %',
    'voip_extracharge_mobile_national' => 'Cargos Extra de Llamadas mobiles nacionales Voip en %',
    'General'				=> 'General',
    'Verified'				=> 'Verificado',
    'tariff'				=> 'Tarifa',
    'item'					=> 'item',
    'sepa'					=> 'sepa',
    'no_sepa'				=> 'no_sepa',
    'Call_Data_Records'		=> 'Registro_de_llamadas',
    'Y' 					=> 'anio|anios',
    'accounting'			=> 'facturacion',
    'booking'				=> 'Reservación',
    'DD'					=> 'Débitos Directos',
    'DD_FRST'               => 'Primeros débitos directos',
    'DD_RCUR'               => 'Recurrentes de débitos directos',
    'DD_OOFF'               => 'Solo los adeudos directos',
    'DD_FNAL'               => 'Últimos débitos directos',
    'DC'					=> 'Créditos SEPA',
    'salesmen_commission'	=> 'comisión_de_vendedor',
    'Assign Role'				=> 'Asignar Roles',
    'Load Data...' 			=> 'Cargando datos...',
    'Clean up directory...' => 'Limpiar directorio...',
    'Associated SEPA Account'	=> 'Cuenta asociada SEPA',
    'Month to create Bill'		=> 'Mes de creación de la facturación',
    'Choose logo'			=> 'Elegir logotipo',
    'Directorate'			=> 'Dirección',
    'Mail address'			=> 'Dirección de correo electrónico',
    'Management'			=> 'Administración',
    'Registration Court 1'	=> 'Registro corto 1',
    'Registration Court 2'	=> 'Registro corto 2',
    'Registration Court 3'	=> 'Registro corto 3',
    'Sales Tax Id Nr'		=> 'Nº de Id de impuesto sobre las ventas',
    'Tax Nr'				=> 'Impuesto Nº',
    'Transfer Reason for Invoices'	=> 'Razón de transferencia de las facturas',
    'Upload logo'			=> 'Cargar logotipo',
    'Web address'			=> 'Dirección web',
    'Zip'					=> 'Código Postal',
    'Commission in %'		=> 'Comisión en %',
    'Product List'			=> 'Lista de productos',
    'Already recurring ?' 	=> '¿Ya se repite?',
    'Date of Signature' 	=> 'Fecha de la firma',
    'Disable temporary' 	=> 'Desactivar temporal',
    'Reference Number' 		=> 'Número de referencia',
    'Bank Bank Institutee' 		=> 'Institución Bancario',
    'Contractnr'			=> 'Nro. de Contrato',
    'Create Invoices' 		=> 'Crear factura',
    'Invoicenr'				=> 'Nro. de factura',
    'Calling Number'		=> 'Número de llamada',
    'Called Number'			=> 'Número de llamada',
    'Target Month'			=> 'Mes fijado',
    'Date'					=> 'Fecha',
    'Count'					=> 'Cuenta',
    'Tax'					=> 'Impuesto',
    'RCD'					=> 'Fecha de vencimiento',
    'Currency'				=> 'Moneda',
    'Gross'					=> 'Bruto',
    'Net'					=> 'Red',
    'MandateID'				=> 'ID del orden',
    'MandateDate'			=> 'Fecha de orden',
    'Commission in %'		=> 'Comisión en %',
    'Total Fee'				=> 'Tarifa Total',
    'Commission Amount'		=> 'Monto de Comisión',
    'Zip Files' 			=> 'Archivo comprimido .zip',
    'Concatenate invoices'  => 'Concatenar las facturas',
    'primary'				=> 'principal',
    'secondary'				=> 'Secundario',
    'disabled'				=> 'desactivado',
    'Value (deprecated)'          => 'Valor (obsoleto)',
    'Priority (lower runs first)' => 'Prioridad (el más bajo primero)',
    'Priority' 				=> 'Prioridad',
    'Title' 				=> 'Titulo',
    'Created at'			=> 'Fecha de creación',
    'Activation date'       => 'Fecha de activación',
    'Deactivation date'     => 'Fecha de desactivacion',
    'SIP domain'            => 'Dominio SIP',
    'Created at' 			=> 'Fecha de creación',
    'Last status update'	=> 'Última actualización de estado',
    'Last user interaction' => 'Última interacción con el usuario',
    'Method'				=> 'Método',
    'Ordertype ID'			=> 'ID tipo de pedido',
    'Ordertype'				=> 'Tipo de pedido',
    'Orderstatus ID'		=> 'ID del estado de pedido',
    'Orderstatus'			=> 'Estado de pedido',
    'Orderdate'				=> 'Fecha de Pedido',
    'Ordercomment'			=> 'Comentario del pedido',
    'Envia customer reference' => 'Referencia del cliente',
    'Envia contract reference' => 'referencia de contrato',
    'Contract ID'			=> 'ID del contrato',
    'Phonenumber ID'		=> 'ID de número de teléfono',
    'Related order ID'		=> 'ID de pedido relacionado',
    'Related order type'	=> 'Relacionados con tipo de pedido',
    'Related order created' => 'Relacionados con orden creado',
    'Related order last updated' => 'Relacionados con orden actualizada',
    'Related order deleted'	=> 'Orden relacionado eliminada',
    'Envia Order'			=> 'Envia orden',
    'Document type'			=> 'Tipo de documento',
    'Upload document'		=> 'Subir documento',
    'Call Start'			=> 'Inicio de llamada',
    'Call End'				=> 'Fin de llamada',
    'Call Duration/s'		=> 'Duración de llamada',
    'min. MOS'				=> 'min MOS',
    'Packet loss/%'			=> 'Pérdida de paquetes /%',
    'Jitter/ms'				=> 'inestabilidad "Jitter/ms"',
    'avg. Delay/ms'			=> 'promedio demora/ms',
    'Caller (-> Callee)'	=> 'Llamada (-> destinatario)',
    '@Domain'				=> '@dominio',
    'min. MOS 50ms'			=> 'min MOS 50ms',
    'min. MOS 200ms'		=> 'min MOS 200ms',
    'min. MOS adaptive 500ms'	=> 'min. MOS adaptive 500ms',
    'avg. MOS 50ms'			=> 'Promedio MOS 50ms',
    'avg. MOS 200ms'		=> 'Promedio MOS 200ms',
    'avg. MOS adaptive 500ms'	=> 'Promedio MOS adaptive 500ms',
    'Received Packets'		=> 'Paquetes recibidos',
    'Lost Packets'			=> 'Pérdida de paquetes',
    'avg. Delay/ms'			=> 'promedio demora/ms',
    'avg. Jitter/ms'		=> 'promedio variación/ms "jitter/ms"',
    'max. Jitter/ms'		=> 'máxima variacion/ms "jitter/ms"',
    '1 loss in a row'		=> '1 pérdida en una fila',
    '2 losses in a row'		=> '2 pérdidas consecutivas',
    '3 losses in a row'		=> '3 pérdidas consecutivas',
    '4 losses in a row'		=> '4 pérdidas consecutivas',
    '5 losses in a row'		=> '5 pérdidas consecutivas',
    '6 losses in a row'		=> '6 pérdidas consecutivas',
    '7 losses in a row'		=> '7 pérdidas consecutivas',
    '8 losses in a row'		=> '8 pérdidas consecutivas',
    '9 losses in a row'		=> '9 pérdidas consecutivas',
    'PDV 50ms - 70ms'		=> 'PDV 50MS-70MS',
    'PDV 70ms - 90ms'		=> 'PDV 70MS-90MS',
    'PDV 90ms - 120ms'		=> 'PDV 90MS-120MS',
    'PDV 120ms - 150ms'		=> 'PDV 120MS-150MS',
    'PDV 150ms - 200ms'		=> 'PDV 150MS-200MS',
    'PDV 200ms - 300ms'		=> 'PDV 200MS-300MS',
    'PDV >300 ms'			=> 'PDV >300 ms',
    'Callee (-> Caller)'	=> 'Llamada (-> destinatario)',
    'Credit'                    => 'Crédito',
    'Other'                     => 'Otros',
    'Once'                      => 'Una vez',
    'Monthly'                   => 'Mensual',
    'Quarterly'                 => 'Trimestral',
    'Yearly'                    => 'Anual',
    'NET'                       => 'NET',
    'CMTS'                      => 'CMTS',
    'DATA'                      => 'DATOS',
    'CLUSTER'                   => 'CLUSTER',
    'NODE'                      => 'NODO',
    'AMP'                       => 'AMP',
    'None'                      => 'Ninguno',
    'Null'                      => 'Null',
    'generic'                   => 'genérico',
    'network'                   => 'network',
    'vendor'                    => 'fabricante',
    'user'                      => 'usuario',
    'Yes'                       => 'Si',
    'No'                        => 'No',
    'Has telephony'             => 'Tiene telefonía',
    'OID for PreConfiguration Setting' => 'OID para Preconfiguración',
    'PreConfiguration Value' => 'Valor de configuración',
    'PreConfiguration Time Offset' => 'Tiempo de configuración predefinido',
    'Reload Time - Controlling View' => 'Tiempo de recarga - Vista de control',
    'Due Date'                  => 'Fecha de vencimiento',
    'Type'                      => 'Tipo',
    'Assigned users'            => 'Usuarios asignados',
    'active contracts'          => 'Contrato activo',
    'assigned_items'            => 'Este producto ha asignado elementos',
    'Product_Delete_Error'      => 'No se pudo eliminar el producto con ID :id',
    'Product_Successfully_Deleted' => 'Producto eliminado con ID :id con éxito',
    'total'                     => 'Balance',
    'new_items'                 => 'Nuevos objetos',
    'new_customers'             => 'Clientes nuevos',
    'cancellations'             => 'Cancelaciones',
    'support'                   => 'Soporte',
    'Balance'                   => 'Balance',
    'Week'                      => 'Semana',
    'log_msg_descr'             => 'Haga clic para ver descripciones de mensajes de registro',
    'postalInvoices'            => 'Servicios postales',
    'zipCmdProcessing'          => 'Se está creando PDF con facturas postales',
    'last'                      => 'ultimo',
    'of'                        => 'de',
    'parts'                     => 'parte|partes',
    'purchase'                  => 'compra',
    'sale'                      => 'venta',
    'position rectangle'        => 'rectángulo de posición',
    'position polygon'          => 'polígono de posición',
    'nearest amp/node object'   => 'objeto amp/nodo más cercano',
    'assosicated upstream interface' => 'asociado a interfaz upstream',
    'cluster (deprecated)'      => 'cluster (obsoleto)',
    'Cable Modem'               => 'Cable Modem',
    'CPE Private'               => 'CPE Privado',
    'CPE Public'                => 'CPE público',
    'MTA'                       => 'MTA',
    'Minimum Maturity'          => 'Rango mínimo',
    'Concatenate postal invoices...' => 'Facturas postales concertadas...',
    'Enable AGC'                => 'Habilitar AGC',
    'AGC offset'                => 'AGC compensado',
    'spectrum'                  => 'Espectro',
    'levelDb'                   => 'Nivel en dBmV',
    'noSpectrum'                => 'No Spectrum available for this Modem',
    'createSpectrum'            => 'Crear Espectro',
    'configfile_outdated'       => 'Configfile está desactualizado - ¡Error al generar el archivo!',
    'shouldChangePassword'       => 'Por favor, cambie su contraseña!',
    'PasswordExpired'           => 'Su contraseña está desactualizada. Las contraseñas deben ser cambiadas regularmente para mantenerse seguras. ¡Gracias!',
    'newUser'                   => 'Bienvenido a NMS Prime. Por favor, cambia tu Contraseña para asegurar tu cuenta correctamente. ¡Gracias!',
    'Password Reset Interval'   => 'Intervalo de reinicio de contraseña',
    'PasswordClick'             => 'Haz clic en el botón que aparece a continuación para cambiar tu contraseña.',
    'hello'                     => 'Hola',
    'newTicketAssigned'         => 'hay un nuevo Ticket asignado a usted.',
    'ticket'                    => 'Ticket',
    'title'                     => 'Titulo',
    'description'               => 'Descripción 	',
    'ticketUpdated'             => 'Ticket :id actualizado',
    'newTicket'                 => 'Nuevo ticket',
    'deletedTicketUsers'        => 'Eliminado del ticket :id',
    'deletedTicketUsersMessage' => 'Usted ha sido eliminado el ticket Nr. :id.',
    'ticketUpdatedMessage'      => 'este ticket se ha actualizado.',
    'noReplyMail'               => 'Dirección de correo electrónico no responder',
    'noReplyName'               => 'Nombre de autocontestador Email',
    'deleteSettlementRun'       => 'Eliminando settlementrun :time...',
    'created'                   => 'Creado!',
    'Urban district'            => 'Distrito Urbano',
    'Zipcode'                   => 'Código postal',
    'base' => [
        'delete' => [
            'success' => 'Eliminado :model :id',
            'fail' => 'No se pudo eliminar :model :id',
        ],
    ],
    'pleaseWait'                => 'Esto puede tomar unos segundos. Por favor espere hasta que el proceso termine.',
    'import'                    => 'Importar',
    'exportConfigfiles'         => 'Exportar este archivo Configfile y todos sus hijos.',
    'importTree'                => 'Por favor, especifique el archivo de configuración padre relacionado.',
    'exportSuccess'             => ':name exportado!',
    'setManually'               => ':file de :name debe ser configurado manualmente.',
    'invalidJson'               => '¡El archivo seleccionado no tiene el formato correcto o no es un JSON!',
    'proximity'                 => 'Busqueda por proximidad',
    'dashboard'                 => [
        'log' =>[
            'created'       => 'creado',
            'deleted'       => 'borrado',
            'updated'       => 'actualizado',
            'updated N:M'   => 'actualizado',
            ],
    ],
    'Modem'                         => 'Módem',
    'PhonenumberManagement'         => 'Gestión de número telefónico',
    'NetElement'                    => 'Elemento de red',
    'SepaMandate'                   => 'Mandato SEPA',
    'EnviaOrder'                    => 'Orden Envia',
    'Ticket'                        => 'Ticket',
    'CccUser'                       => 'CCC-Usuario',
    'EnviaOrderDocument'            => 'Documento de orden Envia',
    'EnviaContract'                 => 'Contrato Envia',
    'Endpoint'                      => 'Punto final',
    'PhonebookEntry'                => 'Entrada de libreta telefónica',
    'Sla'                           => 'SLA',
];
