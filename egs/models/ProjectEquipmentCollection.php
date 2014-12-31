<?php
class ProjectEquipmentCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('ProjectEquipment');
			$this->_tablename="project_equipment_overview";
			
			$this->identifierField='name';
		}
}
?>