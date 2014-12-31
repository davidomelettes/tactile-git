<?php
class CampaignCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Campaign');
			$this->_tablename="campaignsoverview";
			
		$this->view='';
		}
	
		
		
}
?>
