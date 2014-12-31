<?php
class Ticket extends DataObject {
	protected $defaultDisplayFields=array('number','summary','client_ticket_status','internal_ticket_status','originator_person','originator_company','created','lastupdated','last_response_time','last_response_by');
	
	function __construct() {
		parent::__construct('tickets');
		$this->idField='id';
		$this->orderby = 'lastupdated';
		$this->orderdir = 'desc';
		
		$this->identifier='summary';
		$this->identifierField='summary';

		$this->setAdditional('number');
		
		$this->hasMany('TicketResponse','ticket_id');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('type', '=', 'site'));
		$this->setAlias('response', 'TicketResponse', $cc, 'body');
	
		$this->belongsTo('TicketQueue', 'ticket_queue_id', 'ticket_queue');
		
		$this->belongsTo('TicketPriority', 'client_ticket_priority_id', 'client_ticket_priority');
		$this->belongsTo('TicketSeverity', 'client_ticket_severity_id', 'client_ticket_severity');
		$this->belongsTo('TicketStatus', 'client_ticket_status_id', 'client_ticket_status');
		
		$this->belongsTo('TicketPriority', 'internal_ticket_priority_id', 'internal_ticket_priority');
		$this->belongsTo('TicketSeverity', 'internal_ticket_severity_id', 'internal_ticket_severity');
		$this->belongsTo('TicketStatus', 'internal_ticket_status_id', 'internal_ticket_status');
		
		$this->belongsTo('TicketCategory', 'ticket_category_id', 'ticket_category');
		
		$this->belongsTo('Person', 'originator_person_id', 'originator_person');
		$this->belongsTo('Company', 'originator_company_id', 'originator_company');
		
		$this->belongsTo('User', 'assigned_to', 'person_assigned_to');
		
		$this->setAdditional('last_response_time', 'timestamp');
		$this->setAdditional('last_response_by');
	}
}
?>