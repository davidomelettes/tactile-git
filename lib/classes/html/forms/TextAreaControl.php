<?php
class TextAreaControl extends FormControl {

	public function __construct(DataField $field){
		$this->_data=$field;
		$this->extractData();
	}
	
	public function render() {
		$html="{textarea attribute='{$this->name}' {$this->getClassNameString()}}\n";
		return $html;
	}

	public function setCompulsory() {
		$this->compulsory=true;
		$this->addClassName('compulsory');
	}
	
}
?>
