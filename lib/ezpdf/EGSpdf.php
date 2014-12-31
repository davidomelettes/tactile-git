<?php
/*
 * Created on 3-Oct-06 by Tim Ebenezer
 *
 * EGSpdf.php
 */

require('class.ezpdf.php');

class EGSpdf extends Cezpdf {
	
	function EGSpdf($pagesize='a4',$orientation='portrait') {
		parent::Cezpdf($pagesize,$orientation);
		$this->selectFont(FILE_ROOT.'lib/ezpdf/fonts/Helvetica.afm');
		$this->ezSetMargins(20,20,20,20);
		$this->ezSetY($this->y+20);
		$path=FILE_ROOT.'app/pdf_colours.yml';
		$user_path = FILE_ROOT.'user/'.EGS_COMPANY_ID.'/pdf_colours.yml';
		if(file_exists($user_path)) {
			$path = $user_path;
		}
		$this->colours = Spyc::YAMLLoad($path);
	}

	function ezLn() {	
		$this->ezText("",16);
	}
	
	function doColouredLine() {
		call_user_func_array(array($this,'setStrokeColor'),$this->colours['divider']);
		$this->line(18,$this->y-10,$this->ez['pageWidth']-20,$this->y-10);
		$this->ezLn();
	}
	function addTitle($title,$value=null) {
		if($value!==null) {
			$heading_value = implode(',',$this->colours['heading_value']);
			$old_colour = $this->currentColour;
			call_user_func_array(array($this,'setColor'),$this->colours['heading_label']);
			$this->ezText("<b>$title:</b> <c:setDifferentColor:$heading_value><b>$value</b></c:setDifferentColor>",16);
			$this->currentColour=$old_colour;
		}
	}
	function setDifferentColor($info) {
		switch($info['status']){
	    	case 'start':
	    	case 'sol':
			$colours=explode(',',$info['p']);
			$this->ez['old_colour']=$this->currentColour;
			$this->setColor(($colours[0]/255),($colours[1]/255),($colours[2]/255));
	    		break;
		    case 'end':
		    case 'eol':
			$this->setColor($this->ez['old_colour']['r'],$this->ez['old_colour']['g'],$this->ez['old_colour']['b']);
				break;
		}
	}
	
	
	
	
	function doLogo() {
		if (file_exists(FILE_ROOT.'data/company'.EGS_COMPANY_ID.'/logos/print-logo.jpg')) {
			$image = @imagecreatefromjpeg(FILE_ROOT.'data/company'.EGS_COMPANY_ID.'/logos/print-logo.jpg');
			list($width, $height) = getimagesize(FILE_ROOT.'data/company'.EGS_COMPANY_ID.'/logos/print-logo.jpg');
			$newheight = 25;
			$newwidth = ($width / $height) * 25;
			$this->addImage($image,18,20,$newwidth, $newheight);
		}
	}
}

?>
