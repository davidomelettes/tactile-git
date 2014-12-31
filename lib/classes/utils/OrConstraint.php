<?php
class OrConstraint extends Constraint {
	private $constraints=array();
	public function __construct($fieldname) {
		$this->field= $fieldname;
		$args = func_get_args();
		foreach($args as $arg) {
			if(!is_array($arg)) {
				continue;
			}
			$op=$arg[0];
			$val=$arg[1];
			$this->constraints[]= new Constraint($this->field,$op,$val);
		}
	}
	
	public function __toString() {
		$string='(';
		foreach($this->constraints as $constraint) {
			$string.=$constraint->__toString();
			$string.=' OR ';
		}
		$string.=' 1=0) ';
		return $string;
	}
	
	
}
?>