<?php
class CompanyaddressCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Companyaddress');
//		$this->_tablename='companyaddressoverview';
		$this->identifierField='name';
		}



}
?>
