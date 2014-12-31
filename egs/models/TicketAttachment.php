<?php
class TicketAttachment extends DataObject {
	protected $defaultDisplayFields = array(
		'file'=>'Name',
		'type'=>'Type',
		'size'=>'Size',
	);
	
	function __construct() {
		parent::__construct('ticket_attachments');
		$this->idField='id';
		
		$this->belongsTo('Ticket', 'ticket_id');
		$this->belongsTo('File', 'file_id');
	}
}
?>