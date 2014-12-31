<?php
class PaymentTerm extends DataObject {

	function __construct() {
		parent::__construct('syterms');
		$this->idField='id';
		$this->setEnum('basis',array('I'=>'Invoice','M'=>'Month'));
		$this->identifierField='description';

	}


}
?>
