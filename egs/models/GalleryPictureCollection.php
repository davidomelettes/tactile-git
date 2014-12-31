<?php
class GalleryPictureCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GalleryPicture');
			//$this->_tablename="gallery_picturesoverview";
			$this->view='';
		}
	
		
		
}
?>
