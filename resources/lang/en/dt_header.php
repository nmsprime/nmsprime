<?php
return [
	// Index DataTable Header
	// Auth
	'authusers.login_name' => 'Login Name',
	'authusers.first_name' => 'Given Name',
	'authusers.last_name' => 'Family Name',
	'authrole.name' => 'Name',
	// GuiLog
	'guilog.created_at' => 'Time',
	'guilog.username' => 'User',
	'guilog.method' => 'Action',
	'guilog.model' => 'Model',
	'guilog.model_id' => 'Model ID',
	// Company
	'company.name' => 'Company Name',
	'company.city' => 'City',
	'company.phone' => 'Mobile Number',
	'company.mail' => 'E-Mail',
	// Costcenter
	'costcenter.name' => 'CostCenter',
	'costcenter.number' => 'Number',
	//Invoices
	'invoice.type' => 'Type',
	'invoice.year' => 'Year',
	'invoice.month' => 'Month',
	//Item //**

	// Product
	'product.type' => 'Type',
	'product.name' => 'Product Name',
	'product.price' => 'Price',
	// Salesman
	'salesman.id' => 'ID',
	'salesman.lastname' => 'Lastname',
	'salesman.firstname' => 'Firstname',
	// SepaAccount
	'sepaaccount.name' => "Account Name",
	'sepaaccount.institute' => 'Institute',
	'sepaaccount.iban' => 'IBAN',
	// SepaMandate
	'sepamandate.sepa_holder' => 'Account Holder',
	'sepamandate.sepa_valid_from' => 'Valid from',
	'sepamandate.sepa_valid_to' => 'Valid to',
	'sepamandate.reference' => 'Account reference',
	// SettlementRun
	'settlementrun.year' => 'Year',
	'settlementrun.month' => 'Month',
	'settlementrun.created_at' => 'Created at',
	'verified' => 'Verified?',
	// MPR
	'mpr.name' => 'Name',
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
	'contract.number' => 'Contractnumber',
	'contract.firstname' => 'Firstname',
	'contract.lastname' => 'Lastname',
	'contract.zip' => 'ZIP',
	'contract.city' => 'City',
	'contract.street' => 'Street',
	'contract.house_number' => 'Housenumber',
	'contract.district' => 'District',
	'contract.contract_start' => 'Contract Startdate',
	'contract.contract_end' => 'Contract Enddate',
	// Domain
	'domain.name' => 'Domain',
	'domain.type' => 'Type',
	'domain.alias' => 'Alias',
	// Endpoint
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
	'modem.id' => 'Modem Number',
	'modem.mac' => 'MAC Addresse',
	'modem.name' => 'Modem Name',
	'modem.lastname' => 'Lastname',
	'modem.city' => 'City',
	'modem.street' => 'Street',
	'modem.district' => 'District',
	'modem.us_pwr' => 'US level',
	'contract_valid' => 'Contract valid?',
	// QoS
	'qos.name' => 'QoS Name',
	'qos.ds_rate_max' => 'Maximum DS Speed',
	'qos.us_rate_max' => 'Maximum US Speed',
	// Mta
	'mta.hostname' => 'Hostname',
	'mta.mac' => 'MAC-Adress',
	'mta.type' => 'VOIP Protocol',
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
];
