<?php
class ProjectphaseCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Projectphase');
			
		$this->identifierField='name';
		}
	
		
		
}
?>
