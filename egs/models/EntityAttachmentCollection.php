<?php
class EntityAttachmentCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('EntityAttachment');
			$this->_tablename="entity_attachments_overview";
			
		}
	
		
		
}
?>