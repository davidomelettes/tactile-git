<?php
class HiddenControl extends InputControl {

	public $type='hidden';
	
	public function render() {
		
		$html = parent::render($additional);
		return $html;
	}
		
}
?>
