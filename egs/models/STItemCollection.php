<?php
class STItemCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('STItem');
			$this->_tablename="st_itemsoverview";
			
		}
	
		
		
}
?>
