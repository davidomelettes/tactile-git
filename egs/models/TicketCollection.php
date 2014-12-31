<?php
class TicketCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Ticket');
			$this->_tablename="tickets_overview";
			
		}
	
		
		
}
?>