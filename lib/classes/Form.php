<?php
class Form implements Iterator {

	protected $uses=array();
	protected $_elements=array();
	protected $_pointer=0;
	public function __construct($controller,$action=null) {
		$this->action=$action;
		$this->controller=$controller;
	}

	public function uses($doname) {
		$this->uses[]=$doname;
	}

	function make($return=false) {
				
		$dos=$this->uses;
		foreach($dos as $doname) {
			
			$do=new $doname;
			$this->_elements[$doname]=array();
			$fields=$do->getFields();
			foreach($fields as $field) {
				$dataitem=FormControlFactory::Factory($field,$do);
				if($dataitem->type === 'hidden')$beginning[$doname][]=$dataitem;
				else if($dataitem instanceof TextAreaControl)$end[$doname][] = $dataitem;
				else if($dataitem!==false) $middle[$doname][]=$dataitem;
			}
			$beginning = $beginning[$doname];
			$middle = $middle[$doname];
			$end = $end[$doname];
			$total = array($beginning, $middle, $end);
			foreach($total as $item){
				if(empty($item))continue;
				foreach($item as $do){
					$this->_elements[$doname][]=$do;
				}
			}
		}
	}
	public function render($return=false) {
		$this->make();
		if(!isset($this->action)|| $this->action == '')
			$action = '{ACTION}';
		else $action = $this->action;
		$output='{form controller="'.$this->controller.'" action="'.$action.'"}'."\n";
		foreach($this->_elements as $doname=>$fieldset) {
			$output.='{with model=$models.'.$doname.' legend="'.$doname.' Details"}'."\n";
			foreach($fieldset as $element)
				$output.=$element->render();
			$output.='{/with}'."\n";
			
		}
		$output.="{submit}{/form}\n";
		if($return!==false)
			return $output;
		echo $output;		
		
		
	}	
	
	public function current() {
		return $this->_elements[$this->_pointer];
	}
	
	public function next() {
		$this->_pointer++;
		return $this->_elements[$this->_pointer];
	}
	
	public function key() {
		return $this->_pointer;
	}
	
	public function rewind() {
		$this->_pointer=0;
	}
	
	public function valid() {
		return ($this->_pointer<=count($this->_elements));
	}
}
?>
