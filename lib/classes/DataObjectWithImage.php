<?php
class DataObjectWithImage extends DataObject {
	public $image_filename;
	public $thumb_filename;
	private $image_width=100;
	private $image_height=100;
	private $thumb_width=100;
	private $thumb_height=100;
	public $image_dimensions;
	public $thumb_dimensions;
	public function load($constraint) {
		$res=parent::load($constraint);
		
		$this->loadFile();
		return $res;
	}
	
	function loadFile() {
		$path = DATA_ROOT.'tmp/';
		$file=new File($path);
		$image=$this->image;
		if(!empty($image)) {
			$file->load($this->image);
			if($file===false) {
				throw new Exception('Failed to load file for '.get_class($this).' with id '.$this->image);
			}
			$a=$file->Pull($this->image_width,$this->image_height);
			$this->image_filename='/data/tmp/'.$a['filename'];
			$this->image_dimensions=$a['dimensions'];
		}
	}
	
	function loadThumbnail() {
		$path = DATA_ROOT.'tmp/';
		$file=new File($path);
		$image=$this->image;
		if(!empty($image)) {
			$file->load($this->image);
			if($file===false) {
				throw new Exception('Failed to load file for '.get_class($this).' with id '.$this->image);
			}
			$a=$file->Pull($this->thumb_width,$this->thumb_height);
			$this->thumb_filename='/data/tmp/'.$a['filename'];
			$this->thumb_dimensions=$a['dimensions'];
		}
	}
	
	function setImageDimensions($width,$height) {
		$this->image_width = $width;
		$this->image_height = $height;
	}
	function setThumbnailDimensions($width,$height) {
		$this->thumb_width = $width;
		$this->thumb_height = $height;
	}
	
	
}
?>
