<?php

require_once 'Service/Zendesk/Entity/Abstract.php';

class Service_Zendesk_Entity_Ticket extends Service_Zendesk_Entity_Abstract {
	protected $_entity_type = 'ticket';
	
	protected $_callable_properties = array(
		'id',
		'status',
		'priority',
		'created'
	);
	
	private $STATUS_ID_MAP = array(
		0 => 'New',
		1 => 'Open',
		2 => 'Pending',
		3 => 'Solved',
		4 => 'Closed'
	);
	
	private $PRIORITY_ID_MAP = array(
		0 => 'None',
		1 => 'Low',
		2 => 'Normal',
		3 => 'High',
		4 => 'Urgent'
	);
	
	public function id() {
		return $this->_getNode('nice-id');
	}
	
	public function status() {
		return $this->STATUS_ID_MAP[(int) $this->_getNode('status-id')];
	}
	
	public function priority() {
		return $this->PRIORITY_ID_MAP[(int) $this->_getNode('priority-id')];
	}
	
	public function created() {
		$formatter = new PrettyTimestampFormatter();
		return $formatter->format($this->_getNode('created-at'));
	}
	
	public function link_for_site($site_address) {
		return sprintf("http://%s.zendesk.com/tickets/%s", $site_address, $this->id);
	}
}