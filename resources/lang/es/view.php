<?php

return [
//SEARCH
		'Search_EnterKeyword' 		=> 'Ingresar palabra clave',
		'Search_MatchesFor'			=> 'Coincida con',
		'Search_In'					=> 'en',
//jQuery
		//Translations for this at https://datatables.net/plug-ins/i18n/
		'jQuery_sEmptyTable'		=> 'No hay datos disponibles en la tabla',
		'jQuery_sInfo'				=> 'Mostrando _INICIO_ a _FINAL_ de entradas _TOTALES_',
		'jQuery_sInfoEmpty'			=> 'Mostrando 0 a 0 de 0 entradas',
		'jQuery_sInfoFiltered'		=> '(filtrado de _MÁXIMAS_entradas totales)',
		'jQuery_sInfoPostFix'		=> '',
		'jQuery_sInfoThousands'		=> ',',
		'jQuery_sLengthMenu'		=> 'Mostrar las entradas del _MENÚ_',
		'jQuery_sLoadingRecords'	=> 'Cargando...',
		'jQuery_sProcessing'		=> 'Procesando...',
		'jQuery_sSearch'			=> 'Buscar:',
		'jQuery_sZeroRecords'		=> 'Ningún registro encontrado',
		'jQuery_PaginatesFirst'		=> 'Primero',
		'jQuery_PaginatesPrevious'	=> 'Anterior',
		'jQuery_PaginatesNext'		=> 'Próximo',
		'jQuery_PaginatesLast'		=> 'Último',
		'jQuery_sLast'				=> ': activar para ordenar las columnas de forma ascendente',
		'jQuery_sLast'				=> ': activar para ordenar las columnas de forma descendente',
		'jQuery_All'				=> 'Todo',
		'jQuery_Print'				=> 'Imprimir',
		'jQuery_colvis'				=> 'Visibilidad de la columna',
		'jQuery_colvisRestore'		=> 'Restaurar',
		'jQuery_colvisReset'		=> 'Reiniciar',
		'jQuery_ExportTo'			=> 'Exportar a',
                'jQuery_ImportCsv'              => 'importar CSV',
//MENU
	//Main Menu
		'Menu_MainMenu' 			=> 'Menú Principal',
		'Menu_Config Page'			=> 'Configuración global',
		'Menu_Logging'				=> 'Registro',
		'Menu_Product List'			=> 'Lista de productos',
		'Menu_SEPA Accounts'		=> 'Cuenta de SEPA',
		'Menu_Settlement Run'		=> 'Settlement Run',
		'Menu_Cost Center'			=> 'Departamento de costos',
		'Menu_Companies'			=> 'Empresas',
		'Menu_Salesmen'				=> 'Vendedores',
		'Menu_Tree Table'			=> 'Arbol de tabla',
		'Menu_Devices'				=> 'Dispositivos',
		'Menu_DeviceTypes'			=> 'Tipos de dispositivos',
		'Menu_Contracts'			=> 'Contratos',
		'Menu_Modems'				=> 'Módems',
		'Menu_Endpoints'			=> 'Extremos',
		'Menu_Configfiles' 			=> 'Archivo de configuración',
		'Menu_QoS' 					=> 'QoS',
		'Menu_CMTS' 				=> 'CMTS',
		'Menu_Ip-Pools' 			=> 'IP-Pools',
		'Menu_MTAs' 				=> 'MTAs',
		'Menu_Phonenumbers'			=> 'Números de teléfono',
		'Menu_PhoneTariffs'			=> 'Tarifa telefonica',
		'Menu_Envia orders'			=> 'ordenes envia TEL',
		'Menu_Envia contracts'		=> 'envia TEL contratos',

	//User Settings
		'Menu_UserSettings'			=> 'Configuración del Usuario',
		'Menu_UserGlobSettings' 	=> 'Configuración global',
		'Menu_Logout'				=> 'Cerrar sesión',
		'Menu_UserRoleSettings'		=> 'Funciones de usuario',

//HEADER
	//General
		'Header_GlobalSearch'		=> 'Búsqueda global',
		'Header_Overview'			=> 'Información general',
		'Header_Assigned'			=> 'Asignado',
		'Header_Create'				=> 'Crear',
	//Module specific

	//Global
		//Logs
		'Header_Logs'				=> 'Registros',
		'Header_EditLogs'			=> 'Editar registro',
		'Header_Roles'				=> 'Funcion|Funciones',
	//Billing Base
		//Prduct Entry
		'Header_Product Entry'		=> 'Entrada de Producto|Entradas de Productos',
		'Header_EditProduct Entry'	=> 'Editar producto',
		//SEPA Accounts
		'Header_SEPA Account'		=> 'Cuenta SEPA|Cuentas SEPA', //Workaround decide which one to use
		'Header_EditSEPA Account'	=> 'Editar Cuenta SEPA',
		//CostCenter
		'Header_CostCenter'			=> 'Centro de Costos|Centros de Costos', //Workaround decide which one to use
		'Header_Cost Center'		=> 'Centro de Costos|Centros de Costos',
		'Header_EditCost Center'	=> 'Modificar Centro de Costos',
		//Company
		'Header_EditCompany'		=> 'Modificar Empresa',
		//Salesman
		'Header_EditSalesman'		=> 'Modificar Vendedor',
		//Items
		'Header_Item'				=> 'Ítem|Ítems',
		'Header_EditItem'			=> 'Editar ítem', //??
		//Numberrange
		'Header_NumberRange'		=> 'Numberrange|Numberranges',
	//SNMP Modul
		//Device
		'Header_Device'				=> 'Dispositivo|Dispositivos',
		'Header_EditDevice'			=> 'Editar dispositivo',
		//Device Type
		'Header_EditDevice Type'	=> 'Editar dispositivo',
	//Provisioning
		//Contract
		'Header_Contract'			=> 'Contrato|Contratos',
		'Header_EditContract'		=> 'Editar contrato',
		'Header_SepaMandate'		=> 'SEPA-Mandate|SEPA-Mandates',
		//Modems
		'Header_Modems'				=> 'Modem|Modems', //workaround
		'Header_EditModems'			=> 'Edit Modem',
		'Header_Modem'				=> 'Modem|Modems',
		'Header_EditModem'			=> 'Edit Modem',
		//Endpoint
		'Header_Endpoints'			=> 'Endpoint|Endpoints',
		'Header_EditEndpoints'		=> 'Edit Endpoints',
		//Configfiles
		'Header_Configfiles'		=> 'Configfile|Configfiles',
		'Header_EditConfigfiles'	=> 'Edit Configfile',
		//QoS
		'Header_QoS'				=> 'QoS-Rule|QoS-Rules',
		'Header_EditQoS'			=> 'Edit QoS-Rule',
		//CMTS
		'Header_CMTS'				=> 'CMTS|CMTSs',
		'Header_EditCMTS'			=> 'Edit CMTS',
		'Header_Config'				=> 'Config proposal|Config proposals',
		//IpPool
		'Header_IpPool'				=> 'IP-Pool|IP-Pools',
		'Header_EditIpPool'			=> 'Edit IP-Pool',
		'Header_IP-Pools'			=> 'IP-Pool|IP-Pools',
		'Header_EditIP-Pools'		=> 'Edit IP-Pool',
		// Tickets
		'Header_Ticket'				=> 'Ticket|Tickets',
		'Header_EditTicket'			=> 'Edit Ticket',
	//HFC
		//Topography
		'Header_Topography - Modems'=> 'Topography - Modems',
		'navigate'					=> 'Navegar',
		'draw box'					=> 'Dibujar caja',
		'draw polygon'				=> 'Dibujar polígono',
		'modify'					=> 'Modificar elementos',
	//VOIP
		//MTA
		'Header_Mta'				=> '|los MTA MTA',
		'Header_EditMta'			=> 'Editar MTA',
		'Header_MTAs'				=> '|los MTA MTA',
		'Header_EditMTAs'			=> 'Editar MTA',
		//Phonenumber
		'Header_Phonenumber'		=> 'Número de teléfono|Phonenumbers',
		'Header_EditPhonenumber'	=> 'Editar número de teléfono',
		'Header_Phonenumbers'		=> 'Número de teléfono|Phonenumbers',
		'Header_EditPhonenumbers'	=> 'Editar número de teléfono',
		//Phone tariff
		'Header_Phone tariffs'		=> 'Tarifa telefonica|Phonetariffs',
		'Header_EditPhone tariffs'	=> 'Editar tarifa',
	//ProvVoipEnvia
		'Header_EnviaOrders'		=> 'envia TEL orden|envia TEL órdenes',
		'Header_EnviaContracts'		=> 'envia TEL contrato|envia TEL contratos',

	//Header Relation
		// 'Assigned'  				=> 'Zugewiesene',
	//Header Controler index
		// 'SEPA Account' 				=> 'SEPA-Konten',
		// 'Create'					=> 'Erstelle ',
		// 'Edit'						=> 'Ändere ',

//BUTTON
		'Sign me in'				=> 'Usuario',
		'Button_Create'				=> 'Crear',
		'Button_Delete'				=> 'Eliminar elementos marcados',
		'Button_Force Restart'		=> 'Forzar reinicio',
		'Button_Save'				=> 'Guardar',
		'Button_Save / Restart'		=> 'Guardar / reiniciar',
		'Button_manage'				=> 'Gestión permite o prohíbe hacer todo lo posible con cada elemento. Este botón es un acceso directo para administrar todas las entidades de este módulo.',
		'Button_view'				=> 'Acceso directo para la capacidad de ver todas las páginas de este módulo. Visualización es la capacidad básica que se requiere para todas las otras acciones dentro de la GUI.',
		'Button_create'				=> 'Acceso directo para la capacidad de crear todas las páginas de este módulo.',
		'Button_update'				=> 'Acceso directo para la capacidad de crear todas las páginas de este módulo.',
		'Button_delete'				=> 'Acceso directo para la capacidad de crear todas las páginas de este módulo.',
	//BillingBase
		//Product List
		'Button_Create Product Entry'	=> 'Crear entrada de producto',
		//SEPA-Konto
		'Button_Create SEPA Account'	=> 'Crear cuenta SEPA', //Workaround decide which one to use
		'Button_Create SepaAccount'		=> 'Crear cuenta SEPA',

		//CostCenter
		'Button_Create Cost Center' 	=> 'Crear centro de costos', //Workaround decide which one to use
		'Button_Create CostCenter' 		=> 'Crear centro de costos',
		//Company
		'Button_Create Company'			=> 'Crear una empresa',
		//Salesman
		'Button_Create Salesman'		=> 'Crear vendedor',
		//Item
		'Button_Create Item'			=> 'Crear artículo',
		'sr_dl_logs' 					=> 'Descargar archivo de registro de todo',
		//Numberrange
		'Button_Create NumberRange'		=> 'Crear rango de numeros',

	//SNMP Modul
		//Device
		'Button_Create Device'			=> 'Crear dispositivo',
		//Device Type
		'Button_Create Device Type'		=> 'Crear tipo de dispositivo',

	//Provisioning
		//Contract
		// 'Button_Create Contract'		=> 'Neuer Vertrag',
		// 'Button_Create SepaMandate'		=> 'Neues SEPA-Mandat',
		// //Modems
		// 'Button_Create Modem'			=> 'Neues Modem',
		// //Endpoints
		// 'Button_Create Endpoints'		=> 'Neuer Endpunkt',
		// //Configfiles
		// 'Button_Create Configfiles'		=> 'Neue Konfigurationsdatei',
		// //QoS
		// 'Button_Create QoS'				=> 'Neue QoS-Regel',
		// //CMTS
		// 'Button_Create CMTS'			=> 'Neue CMTS',
		// //IpPool
		// 'Button_Create IpPool'			=> 'Neuer IP-Bereich', //workaround
		// 'Button_Create IP-Pools'		=> 'Neuer IP-Bereich',


	//VOIP
		//MTA
		// 'Button_Create Mta'				=> 'Neues MTA',
		// //Phonenumber
		// 'Button_Create Phonenumber'		=> 'Neue Telefonnummer',
		// //Phone tariff
		'Button_Create Phone tariffs'	=> 'Crear Tarifa',

//
// DASHBOARD
//
	'Dashboard_Contracts'			=> 'CONTRATOS ACTIVOS',
	'Dashboard_ContractAnalytics'	=> 'Análisis del contrato (últimos 12 meses)',
	'Dashboard_NoContracts'			=> 'No hay contratos disponibles.',
	'Dashboard_Income'				=> 'INGRESO',
	'Dashboard_IncomeAnalytics'		=> 'Detalle de Ingresos',
	'Dashboard_Date'				=> 'Fecha',
	'Dashboard_LinkDetails'			=> 'Ver Detalles',
	'Dashboard_Other'				=> 'Otros',
	'Dashboard_Tickets' 			=> 'NUEVO TICKET',
	'Dashboard_NoTickets' 			=> 'No hay nuevos Tickets.',
	'Dashboard_Quickstart' 			=> 'INICIO RAPIDO',

//
// Numberrange
//
	//Type
	'Numberrange_Type_contract' => 'Contacto',
	'Numberrange_Type_invoice' => 'Factura',

//
// Contract
//
	'Contract_Numberrange_Failure' => 'No hay contrato libre para el centro de costos seleccionado!'
];
