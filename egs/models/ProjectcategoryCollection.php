<?php
class ProjectcategoryCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Projectcategory');
			$this->identifierField='name';
		}
	
		
		
}
?>