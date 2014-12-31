<?php
class NewsletterurlclickCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Newsletterurlclick');
			$this->_tablename="newsletter_url_clicksoverview";
			
		}
	
		
		
}
?>
