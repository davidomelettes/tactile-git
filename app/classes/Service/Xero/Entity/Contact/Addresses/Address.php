<?php

class Service_Xero_Entity_Contact_Addresses_Address extends Service_Xero_Entity_Abstract {
	
	const TYPE_STREET = 'STREET';
	const TYPE_POBOX = 'POBOX';
	
	protected $_properties = array(
		'AddressType',
		'AddressLine1',
		'AddressLine2',
		'AddressLine3',
		'AddressLine4',
		'City',
		'Region',
		'PostalCode',
		'Country'
	);
}
