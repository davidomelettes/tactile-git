<?php
class TextControl extends InputControl {
	public $type='text';
	protected $value='';
	protected $match;
	protected $compulsory;
	
	public function render() {
		$additional='value="'.$this->value.'" ';
		return parent::render($additional);
	}

	public function setCompulsory() {
		$this->compulsory=true;
		$this->addClassName('compulsory');
	}
	
}
?>
