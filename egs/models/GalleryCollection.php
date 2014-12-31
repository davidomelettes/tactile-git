<?php
class GalleryCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Gallery');
			$this->view='';
		}
	
		
		
}
?>
