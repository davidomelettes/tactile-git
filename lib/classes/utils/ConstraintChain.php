<?php
/**
 * Responsible for representing a sequence of Constraints (and other ConstraintChains) in a series of
 * AND and OR chains
 * @author gj
 */
class ConstraintChain implements Iterator {
	
	/**
	 * Pointer used to make the chain Iterable
	 * @access private
	 * @var Int $_pointer
	 */
	private $_pointer=0;
	
	/**
	 * Array of the Constraint(Chain) objects
	 * @access private
	 * @var Array $_constraints
	 */
	private $_constraints=array();
	
	/**
	 * Add a Constraint(Chain) taking care of grouping AND and OR appropriately
	 * 
	 * Won't add constraints if they already exist, and won't add empty Chains
	 * @param Constraint $c
	 * @param String $type
	 * @return ConstraintChain
	 */
	function add($c,$type='AND') {
		if (!$type == 'AND' && !$type=='OR') {
			$type = 'AND';
		}
		
		if($c instanceof ConstraintChain && count($c->contents)==0) {
			return $this;
		}
		/*if ($this->find($c)) {
			return $this;
		}*/
		else {
			$this->_constraints[]=array('constraint'=>$c,'type'=>$type);
		}
		return $this;
	}

	/**
	 * Returns true iff the supplied Constraint(Chain) already exists
	 * 
	 * @param Constraint|ConstraintChain
	 * @return Boolean
	 */
	function find($c) {
		if($c instanceof ConstraintChain) {
			foreach($c as $constraint) {
				if($this->find($constraint['constraint'])) {
					$this->removeByField($constraint['constraint']->fieldname);
				}				
			}
		}
		foreach ($this->_constraints as $constraint) {
			if ($constraint['constraint'] instanceof ConstraintChain) {
				if ($constraint['constraint']->find($c)) {
					return true;
				}
			}
			else {
				if ($c == $constraint['constraint']) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function findByFieldname($fieldname, $preg_match=false) {
		foreach ($this->_constraints as $c) {
			$c = $c['constraint'];
			if ($c instanceof ConstraintChain) {
				if ($c->findByFieldname($fieldname, $preg_match)) {
					return true;
				}
			} else {
				if ($preg_match) {
					if (preg_match('/'.preg_quote($fieldname).'/', $c->getFieldname())) {
						return true;
					}
				} else {
					if ($c->getFieldname() == $fieldname) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Remove the last-added Constraint
	 */
	function removeLast() {
		return array_pop($this->_constraints);
	}
	
	/**
	 * Remove all constraints that use the given $fieldname
	 * 
	 * @param String $fieldname
	 * @return void
	 */
	function removeByField($fieldname) {
		for ($i=0;$i<count($this->_constraints);$i++) {
			if ($this->_constraints[$i]['constraint']->fieldname == $fieldname) {
				unset($this->_constraints[$i]);
			}
		}
		$constraints = array();
		foreach ($this->_constraints as $c) {
			$constraints[] = $c;
		}
		$this->_constraints = $constraints;
	}
	
	/**
	 * Accessor for the array of constraints
	 * @magic
	 * @todo this is unnecessary for a single paramater!
	 */
	function __get($var) {
		if($var=='contents') {
			return $this->_constraints;
		}
	}

	/**
	 * Returns a string representing the chain suitable for use in an SQL query, handles nesting ()s properly
	 * 
	 * @param String [$table_prefix]
	 * @return String
	 */
	function __toString($table_prefix=null) {
		if(empty($table_prefix)) {
			$table_prefix='';
		}
		$cons = $this->_constraints;
		if (count($cons) > 0) {
			$constraint = array_shift($cons);
			$string = '('.$constraint['constraint']->__toString($table_prefix);
			if (count($cons) > 0) {
				$constraint = array_shift($cons);
				$string .= ' '.$constraint['type'].' ('.$constraint['constraint']->__toString($table_prefix);
				foreach ($cons as $constraint)
					$string .= ' '.$constraint['type'].' '.$constraint['constraint']->__toString($table_prefix);
				$string .= ')';
			}
			$string .= ')';
			return $string;
		}
		return false;
	}
	
	/**
	 * Use the constraints in the chain to provide a set of values that will help match the constraint (intended for 'defaults')
	 * @return array an associative array of fieldname=>value
	 */
	function useAsValues() {
		$values = array();
		foreach($this->_constraints as $constraint) {
			$values[$constraint['constraint']->fieldname] = $constraint['constraint']->value;
		}
		return $values;
	}
	
	/*Implementing Iterator*/
	function current() {
		return $this->_constraints[$this->_pointer];
	}
	
	function next() {
		$this->_pointer++;
	}
	
	function key() {
		return $this->_pointer;
	}
	
	function valid() {
		if(isset($this->_constraints[$this->_pointer]))
			return true;
		return false;
	}
	
	function rewind() {
		$this->_pointer=0;
	}
}

?>
