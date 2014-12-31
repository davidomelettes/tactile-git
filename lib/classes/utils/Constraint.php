<?php
/**
 * Responsible for representing a condition in an SQL query
 * @author gj
 */
class Constraint {
	/**
	 * The name of the field the condition involves
	 * @access private
	 * @var String $fieldname 
	 */
	private $fieldname;
	
	/**
	 * The operator used in the condition
	 * @access private
	 * @var String $operator
	 */
	private $operator;
	
	/**
	 * The value the fieldname is compared to
	 * @access private
	 * @var mixed $value
	 */
	private $value;
	
	/**
	 * A constant for date-comparisons involving 'today'
	 */
	const TODAY="'today'::date";
	
	/**
	 * A constant for date-comparisons involving 'tomorrow'
	 */
	const TOMORROW="'tomorrow'::date";
	
	/**
	 * Represent a constraint given fieldname,operator,value
	 * 
	 * @param String $fieldname
	 * @param String $operator
	 * @param Mixed $value
	 */
	function __construct($fieldname,$operator,$value) {
		$this->fieldname=$fieldname;
		$this->value=$value;
		if($this->value === true) {
			$this->value='true';
		}
		if($this->value===false) {
			$this->value='false';
		}
		if($value==='NULL'&&$operator=='=') {
			$operator=' IS ';
		}
		$this->operator=$operator;
		
	}

	/**
	 * Accessor for fieldname,value,operator
	 * @param String $var
	 * @magic
	 */
	function __get($var) {
		return $this->$var;
	}

	/**
	 * Returns the constraint as a string suitable for use in a query
	 * 
	 * Handles escaping of values and spacing around operators
	 * @param String [$table_prefix]
	 */
	function __toString($table_prefix='') {
		if(!empty($table_prefix)) {
			$table_prefix=$table_prefix.'.';
		}
		else {
			$table_prefix='';
		}
		$db=DB::Instance();
		$string='';
		$string.=$table_prefix.$this->fieldname.' ';
		switch($this->operator) {
			case 'LIKE':	//fall through
			case 'ILIKE':	// ""
			case 'IS':		// ""
			case 'IS NOT':	// ""
				$string.=' '.$this->operator.' ';
				break;	
			default:
				$string.=$this->operator.' ';
		}
		switch($this->value) {
			case 'NULL':
			case self::TODAY:
			case self::TOMORROW;
				$string.=$this->value;
				break;
			default:
				if(in_array($this->operator, array('IN', 'NOT IN'))) {
					$string.=$this->value;
					break;
				}
				$string.=$db->qstr($this->value);
		}
		return $string;
	}
	
	public function getFieldname() {
		return $this->fieldname;
	}
}
?>