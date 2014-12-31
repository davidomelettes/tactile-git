<?php

require_once 'Image/Thumbnailer/Exception.php';

class Image_Thumbnailer {
	
	private $_path;
	private $_ctype;
	private $_image;
	private $_thumbnail;
	
	public function __destruct() {
		if (is_resource($this->_image)) {
			@ImageDestroy($this->_image);
		}
		if (is_resource($this->_thumbnail)) {
			@ImageDestroy($this->_thumbnail);
		}
	}
	
	public function __construct($path, $content_type=null) {
		if (!file_exists($path)) {
			throw new Image_Thumbnailer_Exception('Image path not found: ' . $path);
		} else if (!is_readable($path)) {
			throw new Image_Thumbnailer_Exception('Image path not readable: ' . $path);
		}
		$this->_path = $path;
		if (empty($content_type)) {
			if (preg_match('/\.png$/i', $path)) {
				$content_type = 'image/png';
			} elseif (preg_match('/\.jpe?g$/i', $path)) {
				$content_type = 'image/jpeg';
			} elseif (preg_match('/\.gif$/i', $path)) {
				$content_type = 'image/gif';
			}
		}
		$this->_ctype = $content_type;
		
		switch ($this->_ctype) {
			case 'image/gif':
				$this->_image = ImageCreateFromGif($this->_path);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':
				$this->_image = ImageCreateFromJpeg($this->_path);
				break;
			case 'image/png':
				$this->_image = ImageCreateFromPng($this->_path);
				break;
			default:
				throw new Image_Thumbnailer_Exception('Unrecognised content type: ' . $this->_ctype);
		}
	}
	
	public function resize($max_width = 0, $max_height = 0, $crop = false, $grow = true) {
		list($width, $height) = GetImageSize($this->_path);
		$size = array('width'=>$width, 'height'=>$height);
		
		if ($crop) {
			$size = array('width'=>$width, 'height'=>$height);
			$max_width = $max_width == 0 ? $width : $max_width;
			$max_height = $max_height == 0 ? $height : $max_height;
			$width_diff = abs($width - $max_width);
			$height_diff = abs($height - $max_height);
			if ($width_diff > $height_diff) {
				// Shrink by height
				$ratio = $max_height / $height;
				if (!$grow && $ratio > 1) {
					$ratio = 1;
				}
				$size['width'] = $size['width'] * $ratio;
				$size['height'] = $size['height'] * $ratio;
			} else {
				// Shrink by width
				$ratio = $max_width / $width;
				if (!$grow && $ratio > 1) {
					$ratio = 1;
				}
				$size['width'] = $size['width'] * $ratio;
				$size['height'] = $size['height'] * $ratio;
			}
		} else {
			if ($max_width > 0) {
				$size = array('width'=>$width, 'height'=>$height);
				$ratio = $max_width / $size['width'];
				if (!$grow && $ratio > 1) {
					$ratio = 1;
				}
				$size['width'] = $size['width'] * $ratio;
				$size['height'] = $size['height'] * $ratio;
				if ($max_height > 0  && $size['height'] > $max_height) {
					$ratio = $max_height / $size['height'];
					if (!$grow && $ratio > 1) {
						$ratio = 1;
					}
					$size['width'] = $size['width'] * $ratio;
					$size['height'] = $size['height'] * $ratio;
				}
			} elseif ($max_height > 0) {
				$size = array('width'=>$width, 'height'=>$height);
				$ratio = $max_height / $size['height'];
				if (!$grow && $ratio > 1) {
					$ratio = 1;
				}
				$size['width'] = $size['width'] * $ratio;
				$size['height'] = $size['height'] * $ratio;
				if ($max_width > 0 && $size['width'] > $max_width) {
					$ratio = $max_width / $size['width'];
					if (!$grow && $ratio > 1) {
						$ratio = 1;
					}
					$size['width'] = $size['width'] * $ratio;
					$size['height'] = $size['height'] * $ratio;
				}
			}
		}
		
		$new_width = floor($size['width']);
		$new_height = floor($size['height']);
		
		$this->_thumbnail = ImageCreateTrueColor($new_width, $new_height);
		$white = ImageColorAllocate($this->_thumbnail, 255, 255, 255);
		ImageFill($this->_thumbnail, 0, 0, $white);
		ImageCopyResampled($this->_thumbnail, $this->_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		
		if ($crop) {
			$crop_x = ($new_width - $max_width) / 2;
			$crop_y = ($new_height - $max_height) / 2;
			$tn = ImageCreateTrueColor($max_width, $max_height);
			$white = ImageColorAllocate($tn, 255, 255, 255);
			$grey = ImageColorAllocate($tn, 181, 182, 183);
			ImageFill($tn, 0, 0, $grey);
			ImageFilledRectangle($tn, 1, 1, $max_width-2, $max_height-2, $white);
			ImageCopyResampled($tn, $this->_thumbnail, 2, 2, $crop_x, $crop_y, $max_width-4, $max_height-4, $max_width , $max_height);
			$this->_thumbnail = $tn;
		}
	}
	
	public function save($name, $quality=100) {
		switch ($this->_ctype) {
			case 'image/gif':
				ImageGif($this->_thumbnail, $name);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':
				ImageJpeg($this->_thumbnail, $name, $quality);
				break;
			case 'image/png':
				ImagePng($this->_thumbnail, $name);
				break;
			default:
				throw new Image_Thumbnailer_Exception('Unrecognised content type: ' . $this->_ctype);
		}
	}
}
