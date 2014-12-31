<?php
class IntranetPageRevisionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('IntranetPageRevision');
			$this->_tablename="intranet_page_revisions";
			
		}
	
		
		
}
?>
