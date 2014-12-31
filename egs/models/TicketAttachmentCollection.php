<?php
class TicketAttachmentCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('TicketAttachment');
			$this->_tablename="ticket_attachments_overview";
			
		}
	
		
		
}
?>