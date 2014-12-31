<?php
class ActivityNoteCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('ActivityNote');
			$this->_tablename="activity_notes";
			
		}
	
		
		
}
?>
