<?php

return [

    /*
    |--------------------------------------------------------------------------
    | All other Language Lines - TODO: split descriptions and messages?
    |--------------------------------------------------------------------------
    */
    'Academic degree'           => 'Academic degree',
    'Active'					=> 'Active',
    'Active?'					=> 'Active?',
    'Additional Options'		=> 'Additional Options',
    'Address Line 1'			=> 'Address Line 1',
    'Address Line 2'			=> 'Address Line 2',
    'Address Line 3'			=> 'Address Line 3',
    'Amount'                    => 'Amount',
    'Assigned'					=> 'Assigned',
    'BIC'						=> 'BIC',
    'Bank Account Holder'		=> 'Bank Account Holder',
    'Birthday'					=> 'Birthday',
    'Business'                  => 'Business',
    'City'						=> 'City',
    'Choose KML file'			=> 'Choose KML file',

    'Company'					=> 'Company',
    'conninfo' => [
        'error' => 'Error on PDF creation: ',
        'missing_company' => 'The SEPA account ":var" has no company assigned.',
        'missing_costcenter' => 'The contract has no cost center assigned.',
        'missing_sepaaccount' => 'The cost center ":var" has no SEPA account assigned.',
        'missing_template' => 'There\'s no template for the connection informations selected in company ":var".',
        'missing_template_file' => 'The file of the selected template for the connection informations in the company ":var" does not exist.',
        'read_failure' => 'Empty template or failure on reading it',
    ],
    'Contract Number'			=> 'Contract Number',
    'Contract Start'			=> 'Contract Start',
    'Contract End'				=> 'Contract End',
    'Contract valid' 			=> 'Contract valid',
    'Contract'					=> 'Contract',
    'Contract List'				=> 'Contract List',
    'Contracts'					=> 'Contracts',
    'International prefix'		=> 'International prefix',
    'Country code'				=> 'Country code',
    // Descriptions of Form Fields in Edit/Create
    'accCmd_error_noCC' 	=> 'Contract :contract_nr [ID :contract_id] has no CostCenter assigned. No invoice will be created for the customer.',
    'accCmd_invoice_creation_deactivated' => 'Following contracts have deactivated invoice creation: :contractnrs',
    'Create'					=> 'Create',
    'accCmd_processing' 	=> 'The SettlementRun is executed. Please wait until this process has finished.',
    'Date of installation address change'	=> 'Date of installation address change',
    'Date of value'             => 'Date of value',
    'Delete'					=> 'Delete',
    'Day'						=> 'Day',
    'Description'				=> 'Description',
    'Device'					=> 'Device',
    'accCmd_notice_CDR' 	=> 'Contract :contract_nr [ID :contract_id] has Call Data Records but no valid Voip Tariff assigned',
    'Device List'				=> 'Device List',
    'Device Type'				=> 'Device Type',
    'Device Type List'			=> 'Device Type List',
    'Devices'					=> 'Devices',
    'DeviceTypes'				=> 'DeviceTypes',
    'Directory assistance'      => 'Directory assistance',
    'District'					=> 'District',
    'Dunning'                   => 'Dunning',
    'Edit'						=> 'Edit',
    'Edit '						=> 'Edit ',
    'Endpoints'					=> 'Endpoints',
    'Endpoints List'			=> 'Endpoints List',
    'Entry'						=> 'Entry',
    'alert' 				=> 'Attention!',
    'ALL' 					=> 'ALL',
    'E-Mail Address'			=> 'E-Mail Address',
    'Entry electronic media'    => 'Entry electronic media',
    'Entry in print media'      => 'Entry in print media',
    'Entry type'                => 'Entry type',
    'Fee'                       => 'Fee',
    'Fee for return debit notes' => 'Fee for return debit notes',
    'First IP'					=> 'First IP',
    'Firstname'					=> 'Firstname',
    'Fixed IP'					=> 'Fixed IP',
    'Force Restart'				=> 'Force Restart',
    'Geocode origin'			=> 'Geocode origin',
    'House number'              => 'House number',
    'IBAN'						=> 'IBAN',
    'Internet Access'			=> 'Internet Access',
    'Inventar Number'			=> 'Inventar Number',
    'Call Data Record'		=> 'Call Data Record',
    'IP address'				=> 'IP address',
    'Language'					=> 'Language',
    'Lastname'					=> 'Lastname',
    'Last IP'					=> 'Last IP',
    'ccc'					    => 'Customer Control Center',
    'page_html_header'		    => 'Customer Control Center',
    'pdflatex' => [
        'default' => 'Error executing pdflatex - Return Code: :var',
        'missing' => 'Illegal Command - pdflatex not installed!',
        'syntax'  => 'pdflatex: Syntax error in tex template (misspelled placeholder?) :var',
    ],
    'MAC Address'				=> 'MAC Address',
    'Main Menu'					=> 'Main Menu',
    'Maturity' 					=> 'Maturity',
    'cdr' 					=> 'cdr',
    'cdr_discarded_calls' 	=> "CDR: Contract Nr or ID ':contractnr' not found in database - :count calls of phonenumber :phonenr with price of :price :currency are discarded.",
    'cdr_missing_phonenr' 	=> 'Parse CDR.csv: Detected call data records with phonenr :phonenr that is missing in database. Discard :count phonecalls with charge of :price :currency.',
    'cdr_missing_reseller_data' => 'Missing Reseller Data in Environment File!',
    'cdr_offset' 			=> 'CDR to Invoice time difference in Months',
    'close' 				=> 'Close',
    'contract' => [
        'concede_credit' => 'There are yearly charged items that were already charged (by full price). Please check if the customer shall get a credit!',
        'early_cancel' => 'Do you really want to cancel this contract before tariffs end of term :date is reached?',
        ],
    'iteM' => [
        'concede_credit' => 'This item was already charged (by full price). Please check if the customer shall get a credit!',
    ],
    'contract_nr_mismatch'  => 'Could not find the next contract number because the database query failed. This is due to the following contracts having a contract number that does not belong to their selected cost center: :nrs. Please change the cost center or let the system assign a new contract number for these contracts.',
    'contract_numberrange_failure' => 'No free contract number for selected costcenter available!',
    'cpe_log_error' 		=> 'was not registering on Server - No log entry found',
    'cpe_not_reachable' 	=> 'but not reachable from WAN-side due to manufacturing reasons (it can be possible to enable ICMP response via modem configfile)',
    'cpe_fake_lease'		=> 'The DHCP server has not generated a lease for this endpoint, because the IP address is statically assigned and the server does not need to keep track of it. The following lease has been manually generated for reference only:',
    'D' 					=> 'day|days',
    'dashbrd_ticket' 		=> 'My New Tickets',
    'device_probably_online' =>	':type is probably online',
    'eom' 					=> 'to end of month',
    'envia_no_interaction' 	=> 'No Envia Orders require Interaction',
    'Month'						=> 'Month',
    'envia_interaction'	 	=> 'Envia Order requires Interaction|Envia Orders require Interaction',
    'Net'						=> 'Net',
    'Netmask'					=> 'Netmask',
    'Internet Access'			=> 'Internet Access',
    'no' 						=> 'no',
    'Noble rank'                => 'Noble rank',
    'Nobiliary particle'        => 'Nobiliary particle',
    'Number'					=> 'Number',
    'Number usage'              => 'Number usage',
    'Options'					=> 'Options',
    'or: Upload KML file'		=> 'or: Upload KML file',
    'Other name suffix'         => 'Other name suffix',
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
    'Publish address'           => 'Publish address',
    'QoS next month'			=> 'QoS next month',
    'Real Time Values'			=> 'Real Time Values',
    'Remember Me'				=> 'Remember Me',
    'Reverse search'            => 'Reverse search',
    'Salutation'                => 'Salutation',
    'Save'                      => 'Save',
    'Save All'                  => 'Save All',
    'Save / Restart'            => 'Save / Restart',
    'Serial Number'             => 'Serial Number',
    'Sign me in'                => 'Sign me in',
    'snmp' => [
        'errors_walk' => 'Querying the following OIDs failed: :oids.',
        'errors_set' => 'The following Parameters could not be Set: :oids.',
        'missing_cmts' => 'The cluster misses a superior CMTS as parent device.',
        'undefined' => 'For this netelementtype is no controlling defined.',
        'unreachable' => 'The device is not reachable via SNMP.',
    ],
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
    'home' 						=> 'Home',
    'indices_unassigned' 		=> 'Some of the assigned Indices could not be assigned to a corresponding parameter! They are actually just not used. You can delete them or keep them for later. Just compare the list of parameters of the netelementtype with the list of indices here.',
    'item_credit_amount_negative' => 'A negative credit amount becomes a debit! Are you sure that the customer shall be charged?',
    'invoice' 					=> 'Invoice',
    'Global Config'				=> 'Global Config',
    'GlobalConfig'				=> 'GlobalConfig',
    'VOIP'						=> 'VOIP',
    'Customer Control Center'	=> 'Customer Control Center',
    'Provisioning'				=> 'Provisioning',
    'BillingBase'				=> 'BillingBase Config',
    'Ccc' 						=> 'Ccc Config',
    'HfcBase' 					=> 'HfcBase Config',
    'ProvBase' 					=> 'ProvBase Config',
    'ProvVoip' 					=> 'ProvVoip Config',
    'ProvVoipEnvia' 			=> 'ProvVoipEnvia Config',
    'HFC'						=> 'HFC',
    'Rank'						=> 'Rank',
    'Assign Users'				=> 'Assign Users',
    'Invoices'					=> 'Invoices',
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
    'log_out'				=> 'Log Out',
    'System Log Level'			=> 'System Log Level',
    'Headline 1'				=> 'Headline 1',
    'Headline 2'				=> 'Headline 2',
    'M' 					=> 'month|months',
    'Mark solved'			=> 'Mark as solved?',
    'missing_product' 		=> 'Missing Product!',
    'modem_eventlog_error'	=> 'Modem eventlog not found',
    'modem_force_restart_button_title' => 'Only restarts the modem. Doesn\'t save any changed data!',
    'modem_reset_button_title' => 'Only resets the modem. Doesn\'t save any changed data!',
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
    'modem_monitoring_error'=> 'This could be because the Modem was not online until now. Please note that Diagrams are only available
		from the point that a modem was online. If all diagrams did not show properly then it should be a
		bigger problem and there should be a cacti misconfiguration. Please consider the administrator on bigger problems.',
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
    'Additional modem reset button' => 'Additional modem reset button',
    'modemAnalysis' => [
        'cfOutdated' => 'The modem doesn\'t run with the actual configfile. The last download was before the built time of the configfile.',
        'cpeMacMissmatch' => 'The state of internet access and telephony could not be determined as minimum 1 CPE MAC address differs from the MACs of the assigned MTAs.',
        'fullAccess' => 'Internet access and telephony is allowed. (according to configfile)',
        'missingLD' => 'Info: The last configfile download was too long ago to determine if the modem has incured the actual configurations.',
        'noNetworkAccess' => 'Internet access and telephony is blocked. (according to configfile)',
        'onlyVoip' => 'Internet access is blocked. Only telephony is allowed. (according to configfile)',
    ],
    'modem_no_diag'			=> 'No Diagrams available',
    'Start ID MTA´s'			=> 'Start ID MTA´s',
    'modem_lease_error'		=> 'No valid Lease found',
    'modem_lease_valid' 	=> 'Modem has a valid lease',
    'modem_log_error' 		=> 'Modem was not registering on Server - No log entry found',
    'modem_configfile_error'=> 'Modem configfile not found',
    'Academic Degree'			=> 'Academic Degree',
    'modem_offline'			=> 'Modem is Offline',
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
    'modem_restart_error' 		=> 'Could not restart Modem! (offline?)',
    'Contact Persons' 			=> 'Contact Persons',
    'modem_restart_success_cmts' => 'Restarted modem via CMTS',
    'Accounting Text (optional)'=> 'Accounting Text (optional)',
    'Cost Center (optional)'	=> 'Cost Center (optional)',
    'Credit Amount' 			=> 'Credit Amount',
    'modem_restart_success_direct' => 'Restarted Modem directly via SNMP',
    'Item'						=> 'Item',
    'Items'						=> 'Items',
    'modem_save_button_title' 	=> 'Saves changed data. Determines new geoposition when address data was changed (and assigns it to a new MPR if necessary). Rebuilds the configfile and restarts the modem if at least one of the following has changed: Public IP, network access, configfile, QoS, MAC-address',
    'Product'					=> 'Product',
    'Start date' 				=> 'Start date',
    'Active from start date' 	=> 'Active from start date',
    'Valid from'				=> 'Valid from',
    'Valid to'					=> 'Valid to',
    'Valid from fixed'			=> 'Valid from fixed',
    'Valid to fixed'			=> 'Valid to fixed',
    'modem_statistics'		=> 'Number Online / Offline Modems',
    'Configfile'				=> 'Configfile',
    'Mta'						=> 'Mta',
    'month' 				=> 'Month',
    'Configfiles'				=> 'Configfiles',
    'Choose Firmware File'		=> 'Choose Firmware File',
    'Config File Parameters'	=> 'Config File Parameters',
    'or: Upload Certificate File'	=> 'or: Upload Certificate File',
    'or: Upload Firmware File'	=> 'or: Upload Firmware File',
    'Parent Configfile'			=> 'Parent Configfile',
    'Public Use'				=> 'Public Use',
    'mta_configfile_error'	=> 'MTA configfile not found',
    'IpPool'						=> 'IpPool',
    'SNMP Private Community String'	=> 'SNMP Private Community String',
    'SNMP Public Community String'	=> 'SNMP Public Community String',
    'noCC'					=> 'no Costcenter assigned',
    'IP-Pools'					=> 'IP-Pools',
    'Type of Pool'				=> 'Type of Pool',
    'IP network'				=> 'IP network',
    'IP netmask'				=> 'IP netmask',
    'IP router'					=> 'IP router',
    'oid_list' 				=> 'Warning: OIDs that not already exist in Database are discarded! Please upload MibFile before!',
    'Phone tariffs'				=> 'Phone tariffs',
    'External Identifier'		=> 'External Identifier',
    'Usable'					=> 'Usable',
    'password_change'		=> 'Change Password',
    'password_confirm'		=> 'Confirm Password',
    'phonenumber_missing'       => 'Phonenumber :phonenr of contract :contractnr is missing but :provider charged calls.',
    'phonenumber_mismatch'      => 'Phonenumber :phonenr does not belong to contract :contractnr. The wrong contract/customer could be charged for these calls.',
    'phonenumber_nr_change_hlkomm' => 'Please be aware that future call data records can not be assigned to this contract anymore when you change this number. This is because HL Komm or Pyur only sends the phonenumber with the call data records.',
    'phonenumber_overlap_hlkomm' => 'This number exists or existed within the last :delay month(s). As HL Komm or Pyur only sends the phonenumber with the call data records, it won\'t be possible to assign possible made calls to the appropriate contract anymore! This can result in wrong charges. Please only add this number if it\'s a test number or you are sure that there will be no calls to be charged anymore.',
    'show_ags' 				=> 'Show AG Select Field on Contract Page',
    'snmp_query_failed' 	=> 'SNMP Query failed for following OIDs: ',
    'Billing Cycle'				=> 'Billing Cycle',
    'Bundled with VoIP product?'=> 'Bundled with VoIP product?',
    'Calculate proportionately' => 'Calculate proportionately',
    'Price (Net)'				=> 'Price (Net)',
    'Number of Cycles'			=> 'Number of Cycles',
    'Product Entry'				=> 'Product Entry',
    'Qos (Data Rate)'			=> 'Qos (Data Rate)',
    'with Tax calculation ?'	=> 'with Tax calculation ?',
    'Phone Sales Tariff'		=> 'Phone Sales Tariff',
    'Phone Purchase Tariff'		=> 'Phone Purchase Tariff',
    'sr_repeat' 			=> 'Repeat for SEPA-account(s):', // Settlementrun repeat
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
    'upload_dependent_mib_err' => "Please Upload dependent ':name' before!! (OIDs cant be translated otherwise)",
    'Upload CDR template'		=> 'Upload CDR template',
    'Upload invoice template'	=> 'Upload invoice template',
    'user_settings'			=> 'User Settings',
    'user_glob_settings'	=> 'Global User Settings',

    'voip_extracharge_default' => 'Extra Charge Voip Calls default in %',
    'voip_extracharge_mobile_national' => 'Extra Charge Voip Calls mobile national in %',
    'General'				=> 'General',
    'Verified'				=> 'Verified',
    'tariff'				=> 'tariff',
    'item'					=> 'item',
    'sepa'					=> 'sepa',
    'no_sepa'				=> 'no_sepa',
    'Call_Data_Records'		=> 'Call_Data_Records',
    'Y' 					=> 'year|years',
    'accounting'			=> 'accounting',
    'booking'				=> 'booking',
    'DD'					=> 'Direct Debits',
    'DD_FRST'               => 'First Direct Debits',
    'DD_RCUR'               => 'Recurring Direct Debits',
    'DD_OOFF'               => 'Single Direct Debits',
    'DD_FNAL'               => 'Final Direct Debits',
    'DC'					=> 'DC',
    'salesmen_commission'	=> 'salesmen_commission',
    'Assign Role'				=> 'Assign Roles',
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
    'Has telephony'             => 'Has telephony',
    'OID for PreConfiguration Setting' => 'OID for PreConfiguration Setting',
    'PreConfiguration Value' => 'PreConfiguration Value',
    'PreConfiguration Time Offset' => 'PreConfiguration Time Offset',
    'Reload Time - Controlling View' => 'Reload Time - Controlling View',
    'Due Date'                  => 'Due Date',
    'Type'                      => 'Type',
    'Assigned users'            => 'Assigned users',
    'active contracts'          => 'Active contracts',
    'assigned_items'            => 'This product has assigned items',
    'Product_Delete_Error'      => 'Could not delete product with ID :id',
    'Product_Successfully_Deleted' => 'Successfully deleted product with ID :id',
    'total'                     => 'Balance',
    'new_items'                 => 'New items',
    'new_customers'             => 'New customers',
    'cancellations'             => 'Cancellations',
    'support'                   => 'Support',
    'Balance'                   => 'Balance',
    'Week'                      => 'Week',
    'log_msg_descr'             => 'Click to see log message descriptions',
    'postalInvoices'            => 'Postal invoices',
    'zipCmdProcessing'          => 'PDF with postal invoices is being created',
    'last'                      => 'last',
    'of'                        => 'of',
    'parts'                     => 'part|parts',
    'purchase'                  => 'purchase',
    'sale'                      => 'sale',
    'position rectangle'        => 'position rectangle',
    'position polygon'          => 'position polygon',
    'nearest amp/node object'   => 'nearest amp/node object',
    'assosicated upstream interface' => 'assosicated upstream interface',
    'cluster (deprecated)'      => 'cluster (deprecated)',
    'Cable Modem'               => 'Cable Modem',
    'CPE Private'               => 'CPE Private',
    'CPE Public'                => 'CPE Public',
    'MTA'                       => 'MTA',
    'Minimum Maturity'          => 'Minimum Maturity',
    'Enable AGC'                => 'Enable AGC',
    'AGC offset'                => 'AGC offset',
    'spectrum'                  => 'Spectrum',
    'levelDb'                   => 'Level in dBmV',
    'noSpectrum'                => 'No Spectrum available for this Modem',
    'createSpectrum'            => 'Create Spectrum',
    'configfile_outdated'       => 'Configfile is outdated - Error while generating the file!',
    'shouldChangePassword'       => 'Please change your password!',
    'PasswordExpired'           => 'Your Password is outdated. Passwords should be changed regularly to stay secure. Thank You!',
    'newUser'                   => 'Welcome to NMS Prime. Please change your Passwort to secure your account properly. Thank You!',
    'Password Reset Interval'   => 'Password Reset Interval',
    'PasswordClick'             => 'Please click HERE to change your password.',
    'hello'                     => 'Hello',
    'newTicketAssigned'         => 'there is a new Ticket assigned to you.',
    'ticket'                    => 'Ticket',
    'title'                     => 'Title',
    'description'               => 'Description',
    'ticketUpdated'             => 'Ticket :id updated',
    'newTicket'                 => 'New ticket',
    'deletedTicketUsers'        => 'Deleted from ticket :id',
    'deletedTicketUsersMessage' => 'You have been removed from ticket Nr. :id.',
    'ticketUpdatedMessage'      => 'this ticket has been updated.',
    'noReplyMail'               => 'Addresse of noreply E-mail',
    'noReplyName'               => 'Name of noreply E-mail',
    'deleteSettlementRun'       => 'Deleting settlementrun :time...',
    'created'                   => 'Created!',
    'Urban district'            => 'Urban district',
    'Zipcode'                   => 'Zipcode',
    'base' => [
        'delete' => [
            'success' => 'Deleted :model :id',
            'fail' => 'Could not delete :model :id',
        ],
    ],
    'pleaseWait'                => 'This may take a few seconds. Please wait until the process has finished.',
    'import'                    => 'Import',
    'exportConfigfiles'         => 'Export this Configfile and all it\'s children.',
    'importTree'                => 'Please specify the related parent configfile.',
    'exportSuccess'             => ':name exported!',
    'setManually'               => ':file of :name has to be set manually.',
    'invalidJson'               => 'The selected file is not correctly formatted or not a JSON!',
    'proximity'                 => 'Proximity search',
    'all'                       => 'All',
    'dashboard'                 => [
        'log' =>[
            'created'       => 'created',
            'deleted'       => 'deleted',
            'updated'       => 'updated',
            'updated N:M'   => 'updated',
            ],
    ],
    'Modem'                         => 'Modem',
    'PhonenumberManagement'         => 'Phonenumber Management',
    'NetElement'                    => 'Netelement',
    'SepaMandate'                   => 'SEPA Mandate',
    'EnviaOrder'                    => 'EnviaOrder',
    'Ticket'                        => 'Ticket',
    'CccUser'                       => 'CCC-User',
    'EnviaOrderDocument'            => 'Envia Order Document',
    'EnviaContract'                 => 'Envia Contract',
    'Endpoint'                      => 'Endpoint',
    'PhonebookEntry'                => 'Phonebook Entry',
    'Sla'                           => 'SLA',
    'TRC class'                 => 'TRC class',
    'Carrier in'                => 'Carrier in',
    'EKP in'                    => 'EKP in',
    'Incoming porting'          => 'Incoming porting',
    'Outgoing porting'          => 'Outgoing porting',
    'Subscriber company'        => 'Subscriber company',
    'Subscriber department'     => 'Subscriber department',
    'Subscriber salutation'     => 'Subscriber salutation',
    'Subscriber academic degree'    => 'Subscriber academic degree',
    'Subscriber firstname'      => 'Subscriber firstname',
    'Subscriber lastname'       => 'Subscriber lastname',
    'Subscriber street'         => 'Subscriber street',
    'Subscriber house number'   => 'Subscriber house number',
    'Subscriber zipcode'        => 'Subscriber zipcode',
    'Subscriber city'           => 'Subscriber city',
    'Subscriber district'       => 'Subscriber district',
    'Termination date'          => 'Termination date',
    'Carrier out'               => 'Carrier out',
    'geopos_x_y'                => 'Geopos Lon/Lat',
    'error'                     => 'Error',
];
