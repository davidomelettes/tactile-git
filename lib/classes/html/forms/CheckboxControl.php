<?php
class CheckboxControl extends InputControl {

	public $type='checkbox';
	protected $checked;
	
	public function render() {
		if($this->checked=='checked')
			$additional='checked="'.$this->checked.'" ';
		else
			$additional='';
		$html = parent::render($additional);
		return $html;
	}
		
}
?>
