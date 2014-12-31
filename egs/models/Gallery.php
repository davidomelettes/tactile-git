<?php
class Gallery extends DataObject {

	function __construct() {
		parent::__construct('galleries');
		$this->idField='id';
		
		$this->view='';
		$this->hasMany('GalleryPicture','pictures');
	}

	public function GetFirstImage() {
		$file = new File(FILE_ROOT.'data/tmp/');
		$pic = $this->pictures->getContents(0);
		$file->load($pic->file_id);
		$result = $file->Pull(null,100);
		return $result['filename'];
	}

}
?>
