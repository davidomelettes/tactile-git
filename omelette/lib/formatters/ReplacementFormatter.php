<?php

/**
 * A Formatter for use where you want $value to be replaced by something else when it is a particular value
 * 
 * @author gj
 */
class ReplacementFormatter implements FieldFormatter {

	/**
	 * The Find and Replace values
	 *
	 * @var String
	 */
	protected $find, $replace;
	
	/**
	 * A formatter used if the value isn't swapped
	 *
	 * @var FieldFormatter
	 */
	protected $next_formatter;
	
	/**
	 * Constructor. Takes the 'find' and 'replace' values for the replacement
	 *
	 * @param String $find
	 * @param String $replace
	 */
	public function __construct($find, $replace,FieldFormatter $otherwise=null) {
		$this->find = $find;
		$this->replace = $replace;
		if(is_null($otherwise)) {
			$this->next_formatter = new NullFormatter();
		}
		else {
			$this->next_formatter = $otherwise;
		}
	}
	
	/**
	 * 
	 * @param String $value 
	 * @return String 
	 * @see FieldFormatter::format()
	 */
	function format($value) {
		return ($value==$this->find) ? $this->replace : $this->next_formatter->format($value);
	}
}

?>
