<?php
class PollVote extends DataObject {

	function __construct() {
		parent::__construct('poll_votes');
		$this->belongsTo('PollOption');
		$this->getField('ip_address')->addValidator(new PresenceValidator());
		$this->getField('ip_address')->addValidator(new IPValidator());
	}
	
	
}
?>