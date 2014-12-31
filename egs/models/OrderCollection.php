<?php
class OrderCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct() {
		parent::__construct('Order');
		$this->_tablename="orderoverview";
		
	}
	
}
?>
