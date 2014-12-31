<?php
class NewsletterviewCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Newsletterview');
			$this->_tablename="newsletter_viewsoverview";
			
		$this->view='';
		}
	
		
		
}
?>
