<?php
class OrderItemCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('OrderItem');
		$this->_tablename = "orderitemoverview";
	}
	
		

}
?>
