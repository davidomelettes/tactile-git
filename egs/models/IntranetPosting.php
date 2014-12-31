<?php
class IntranetPosting extends DataObject {

	function __construct() {
		parent::__construct('intranet_postings');
		$this->idField='id';
		
		 $this->orderby='created';
		 $this->orderdir='desc';

	}


}
?>
