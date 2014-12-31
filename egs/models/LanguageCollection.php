<?php
class LanguageCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Language');
			$this->_tablename="langoverview";
			
		$this->identifierField='name';
		}
	
		
		
}
?>
