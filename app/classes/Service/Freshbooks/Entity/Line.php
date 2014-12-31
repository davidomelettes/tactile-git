<?php

require_once 'Service/Freshbooks/Entity/Abstract.php';

/**
 * Wrapper for a line-item of an invoice or estimate
 *
 */
class Service_Freshbooks_Entity_Line extends Service_Freshbooks_Entity_Abstract {
	
	protected $_properties = array(
		'name',
		'description',
		'unit_cost',
		'quantity',
		'amount',
		'tax1_name',
		'tax2_name',
		'tax1_percent',
		'tax2_percent'
	);
	
}
