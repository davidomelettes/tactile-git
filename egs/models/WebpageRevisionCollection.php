<?php
class WebpageRevisionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('WebpageRevision');
			$this->_tablename="webpage_revisions";
			
		$this->view='';
		}
	
		
		
}
?>
