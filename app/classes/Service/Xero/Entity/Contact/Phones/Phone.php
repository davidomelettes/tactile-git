<?php

class Service_Xero_Entity_Contact_Phones_Phone extends Service_Xero_Entity_Abstract {
	
	const TYPE_DEFAULT = 'DEFAULT';
	const TYPE_DDI = 'DDI';
	const TYPE_FAX = 'FAX';
	const TYPE_MOBILE = 'MOBILE';
	
	protected $_properties = array(
		'PhoneType',
		'PhoneNumber',
		'PhoneAreaCode',
		'PhoneCountryCode'
	);
}
