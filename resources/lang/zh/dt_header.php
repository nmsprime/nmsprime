<?php

return [
    // Index DataTable Header
    'active' => 'Active',
    'amount' => 'Amount',
    'city' => 'City',
    'connected' => 'Connected',
    'connection_type' => 'Connection type',
    'deprecated' => 'Deprecated',
    'district' => 'District',
    'email' => 'Email',
    'expansion_degree' => 'Expansion degree',
    'floor' => 'Floor',
    'group_contract' => 'Group contract',
    'house_nr' => 'Housenr',
    'iban' => 'IBAN',
    'id'            => 'ID',
    'name' => 'Name',
    'number' => 'Number',
    'occupied' => 'Occupied',
    'prio'          => 'Priority',
    'street' => 'Street',
    'sum' => 'Sum',
    'type' => 'Type',
    'zip' => 'ZIP',
    'version' => 'Version',
    'apartment' => [
        'number' => 'Number',
        'connected' => 'Connected',
        'occupied' => 'Occupied',
    ],
    'contact' => [
        'administration' => 'Administration',
    ],
    'contact_id' => 'Group contract',
    'contract' => [
        'additional' => 'Additional info',
        'city' => 'City',
        'company' => 'Company',
        'contact' => 'Contact',
        'contract_end' => 'Contract End',
        'contract_start' => 'Contract Start',
        'district' => 'District',
        'firstname' => 'Firstname',
        'house_number' => 'Housenr',
        'id' => 'Contract',
        'lastname' => 'Surname',
        'number' => 'Contract Number',
        'street' => 'Street',
        'zip' => 'ZIP',
        'ground_for_dismissal' => 'Ground for dismissal',
    ],
    // Auth
    'users' => [
        'login_name' => 'Login Name',
        'first_name' => 'Given Name',
        'last_name' => 'Family Name',
        'geopos_updated_at' => 'Last geopos update',
    ],
    'roles.title' => 'Name',
    'roles.rank' => 'Rank',
    'roles.description' => 'Description',
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
    'costcenter' => [
        'name' => 'CostCenter',
        'number' => 'Number',
        'billing_month' => 'Billing month',
    ],
    'debt' => [
        'date' => 'Date',
        'due_date' => 'Due date',
        'indicator' => 'Dunning indicator',
        'missing_amount' => 'Missing amount',
        'number' => 'debt number',
        'total_fee' => 'Fee',
        'voucher_nr' => 'Voucher nr',
    ],
    //Invoices
    'invoice.type' => 'Type',
    'invoice.year' => 'Year',
    'invoice.month' => 'Month',
    //Item
    'item.valid_from' => 'Item Valid from',
    'item.valid_from_fixed' => 'Item Valid from fixed',
    'item.valid_to' => 'Item Valid to',
    'item.valid_to_fixed' => 'Item Valid to fixed',
    'fee' => 'Fee',
    'product' => [
        'proportional' => 'Proportionate',
        'type' => 'Type',
        'name' => 'Product Name',
        'price' => 'Price',
    ],
    // Salesman
    'salesman.id' => 'ID',
    'salesman_id' 		=> 'Salesman-ID',
    'salesman_firstname' => 'Firstname',
    'salesman_lastname' => 'Lastname',
    'commission in %' 	=> 'Commission in %',
    'contract_nr' 		=> 'Contractnr',
    'contract_name' 	=> 'Customer',
    'contract_start' 	=> 'Contract start',
    'contract_end' 		=> 'Contract end',
    'product_name' 		=> 'Product',
    'product_type' 		=> 'Product type',
    'product_count' 	=> 'Count',
    'charge' 			=> 'Charge',
    'salesman.lastname' => 'Lastname',
    'salesman.firstname' => 'Firstname',
    'salesman_commission' => 'Commission',
    'sepaaccount_id' 	=> 'SEPA-account',
    'sepaaccount' => [
        'iban' => 'IBAN',
        'institute' => 'Institute',
        'name' => 'Account Name',
        'template_invoice' => 'Invoice template',
    ],
    // SepaMandate
    'sepamandate.holder' => 'Account Holder',
    'sepamandate.valid_from' => 'Valid from',
    'sepamandate.valid_to' => 'Valid to',
    'sepamandate.reference' => 'Account reference',
    'sepamandate.disable' => 'Disabled',
    // SettlementRun
    'settlementrun.year' => 'Year',
    'settlementrun.month' => 'Month',
    'settlementrun.created_at' => 'Created at',
    'settlementrun.executed_at' => 'Executed at',
    'verified' => 'Verified?',
    // MPR
    'mpr.name' => 'Name',
    'mpr.id'    => 'ID',
    // NetElement
    'netelement.id' => 'ID',
    'netelement.name' => 'Netelement',
    'netelement.ip' => 'IP Adress',
    'netelement.state' => 'State',
    'netelement.pos' => 'Position',
    'netelement.options' => 'Options',
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
    'contact.firstname1' => 'Firstname 1',
    'lastname1' => 'Lastname 1',
    'firstname2' => 'Firstname 2',
    'lastname2' => 'Lastname 2',
    'tel' => 'Phonenumber',
    'tel_private' => 'Phonenumber private',
    'email1' => 'E-Mail 1',
    'email2' => 'E-Mail 2',
    // NetGw
    'netgw.id' => 'ID',
    'netgw.hostname' => 'Hostname',
    'netgw.ip' => 'IP',
    'netgw.company' => 'Manufacturer',
    'netgw.series' => 'Series',
    'netgw.formatted_support_state' => 'Support State',
    'netgw.support_state' => 'Support State',
    // Contract
    'company' => 'Company',
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
    'modem.city' => 'City',
    'modem.district' => 'District',
    'modem.firstname' => 'First name',
    'modem.geocode_source' => 'Geocode origin',
    'modem.house_number' => 'Housenr',
    'modem.id' => 'Modem Number',
    'modem.inventar_num' => 'Serial Nr',
    'modem.lastname' => 'Surname',
    'modem.mac' => 'MAC Address',
    'modem.model' => 'Model',
    'modem.name' => 'Modem Name',
    'modem.street' => 'Street',
    'modem.sw_rev' => 'Firmware Version',
    'modem.ppp_username' => 'PPP Username',
    'modem.us_pwr' => 'US level / dBmV',
    'modem.us_snr' => 'US SNR / dB',
    'modem.ds_pwr' => 'DS level / dBmV',
    'modem.ds_snr' => 'DS SNR / dB',
    'modem.support_state' => 'Suport State',
    'modem.formatted_support_state' => 'Support State',
    'contract_valid' => 'Contract valid?',
    // Node
    'node' => [
        'name' => 'Name',
        'headend' => 'Headend',
        'type' => 'Type of signal',
    ],
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
    'phonenr_act' => 'Activation date',
    'phonenr_deact' => 'Deactivation date',
    'phonenr_state' => 'Status',
    'modem_city' => 'Modem city',
    'sipdomain' => 'Registrar',
    // Phonenumbermanagement
    'phonenumbermanagement.id' => 'ID',
    'phonenumbermanagement.activation_date' => 'Activation date',
    'phonenumbermanagement.deactivation_date' => 'Deactivation date',
    // PhoneTariff
    'phonetariff.name' => 'Phone Tariff',
    'phonetariff.type' => 'Type',
    'phonetariff.description' => 'Description',
    'phonetariff.voip_protocol' => 'VOIP Protocol',
    'phonetariff.usable' => 'Usable',
    // ENVIA enviaorder
    'enviaorder.ordertype'  => 'Order Type',
    'enviaorder.orderstatus'  => 'Order Status',
    'escalation_level' => 'Escalation Level',
    'enviaorder.created_at'  => 'Created at',
    'enviaorder.updated_at'  => 'Updated at',
    'enviaorder.orderdate'  => 'Order date',
    'enviaorder_current'  => 'Action needed?',
    'enviaorder.contract.number' => 'Contract',
    'phonenumber.number' => 'Number',
    //ENVIA Contract
    'enviacontract.contract.number' => 'Contract',
    'enviacontract.end_date' => 'End Date',
    'enviacontract.envia_contract_reference' => 'envia TEL Contract reference',
    'enviacontract.start_date' => 'Start Date',
    'enviacontract.state' => 'Status',
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
    'realty' => [
        'agreement_from' => 'Valid from',
        'agreement_to' => 'Valid to',
        'apartmentCount' => 'Total apartments',
        'apartmentCountConnected' => 'Connected apartments',
        'city' => 'City',
        'concession_agreement' => 'Concession agreement',
        'contact_id' => 'Administration',
        'contact_local_id' => 'Local contact',
        'district' => 'District',
        'house_nr' => 'House nr',
        'last_restoration_on' => 'Last restoration',
        'name' => 'Name',
        'street' => 'Street',
        'zip' => 'ZIP',
    ],
    // NAS
    'nas' => [
        'nasname' => 'Name',
    ],
    // Ticket
    'ticket' => [
        'id' => 'ID',
        'name' => 'Title',
        'type' => 'Type',
        'priority' => 'Priority',
        'state' => 'State',
        'user_id' => 'Created by',
        'created_at' => 'Created at',
        'assigned_users' => 'Assigned Users',
        'ticketable_id' => 'Id',
        'ticketable_type' => 'Type',
    ],
    'assigned_users' => 'Assigned Users',
    'tickettypes.name' => 'Type',
];
