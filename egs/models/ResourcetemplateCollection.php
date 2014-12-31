<?php
class ResourcetemplateCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Resourcetemplate');
			$this->_tablename="resource_templates_overview";
			$this->identifierField='id';
		}
	
		
		
}
?>
