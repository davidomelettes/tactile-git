<?php
class SupplierCollection extends DataObjectCollection {
	
	function __construct() {
		parent::__construct(new Supplier);
	}
	
}


?>