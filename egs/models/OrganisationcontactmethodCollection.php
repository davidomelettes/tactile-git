<?php

class OrganisationcontactmethodCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct('Organisationcontactmethod');
		$this->_tablename = "organisation_contact_methods_overview";
	}
}
