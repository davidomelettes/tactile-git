<?php
class GalleryPicture extends DataObject {

	function __construct() {
		parent::__construct('gallery_pictures');
		$this->idField='id';
		
		$this->view='';
		
 		$this->belongsTo('Gallery', 'gallery_id', 'gallery'); 
		$this->belongsTo('File','file_id','file');
	}


}
?>
