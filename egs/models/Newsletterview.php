<?php
class Newsletterview extends DataObject {
	protected $defaultDisplayFields=array('person','newsletter','time_viewed','ip_address');
	function __construct() {
		parent::__construct('newsletter_views');
		$this->idField='id';
		
		$this->orderby='time_viewed';
		$this->orderdir='DESC';
		
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->belongsTo('Newsletter', 'newsletter_id', 'newsletter'); 

	}


}
?>
