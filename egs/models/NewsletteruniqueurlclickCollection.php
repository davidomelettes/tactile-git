<?php
class NewsletteruniqueurlclickCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Newsletterurlclick');
			$this->_tablename="newsletter_unique_url_clicksoverview";
			
		$this->view='';
		}
	
		
		
}
?>
