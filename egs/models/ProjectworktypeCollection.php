<?php
class ProjectworktypeCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Projectworktype');
			$this->identifierField='title';
			$this->_tablename='projectworktypesoverview';
		}
	
		
		
}
?>
