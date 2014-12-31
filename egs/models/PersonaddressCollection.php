<?php
class PersonaddressCollection extends DataObjectCollection {
	
	function __construct() {
		parent::__construct('Personaddress');
		$this->_tablename="personaddress_overview";
	}
}


?>
