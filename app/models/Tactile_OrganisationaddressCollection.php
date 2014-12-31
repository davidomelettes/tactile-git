<?php

class Tactile_OrganisationaddressCollection extends DataObjectCollection
{
	function __construct()
	{
		parent::__construct('Tactile_Organisationaddress');
		$this->_tablename = 'organisation_addresses_overview';
	}
}
