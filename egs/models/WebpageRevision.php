<?php
class WebpageRevision extends DataObject {
	protected $defaultDisplayFields=array('title'=>'Title','created'=>'Created');
	function __construct() {
		parent::__construct('webpage_revisions');
		$this->idField='id';
		$this->view='';
		$this->orderby = 'created';
		$this->orderdir = 'desc';	
 		$this->belongsTo('Webpage', 'webpage_id', 'webpage'); 
 		$null_formatter = new NullFormatter;
 		$null_formatter->is_safe = true;
		$this->getField('content')->setFormatter($null_formatter);
	}


}
?>
