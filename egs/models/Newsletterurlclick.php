<?php
class Newsletterurlclick extends DataObject {
	protected $defaultDisplayFields = array('person','url','clicked_at');
	function __construct() {
		parent::__construct('newsletter_url_clicks');
		$this->idField='id';
		
		
 		//$this->belongsTo('Newsletterurl', 'url_id', 'url');
 		$this->belongsTo('Person', 'person_id', 'person'); 
		$this->orderby='clicked_at';
		$this->orderdir='DESC';
		$this->setAdditional('url');
		$this->setAdditional('newsletter');

	}


}
?>
