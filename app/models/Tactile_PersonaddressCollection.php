<?php

class Tactile_PersonaddressCollection extends DataObjectCollection
{
	function __construct()
	{
		parent::__construct('Tactile_Personaddress');
		$this->_tablename = 'person_addresses_overview';
	}
}
