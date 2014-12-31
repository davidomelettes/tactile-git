<?php

class SuperCollection implements Iterator, Countable  {

	/**
	 * array of DataObjectCollection
	 * List of collections to compare
	 *
	 * @var Array
	 */
	protected $_collections = array();
	
	/**
	 * array of String
	 * The list of fields to compare when merging
	 *
	 * @var array
	 */
	protected $_fields;
	
	/**
	 * Optional lambda function to use for comparisons - vales are put through this before being compared
	 * @var Function
	*/
	protected $_cmp_modifier;
	
	/**
	 * A comparison function, takes an array of values and returns which is 'first'
	 *
	 * @var Function
	 */
	protected $_cmp_function;
	
	protected $_winner;
	
	function __construct($cmp_modifier = null, $cmp_function = null) {
		if (isset($cmp_modifier)) {
			$this->_cmp_modifier = $cmp_modifier;
		} else {
			$this->_cmp_modifier = create_function('$x', 'return $x;');
		}
		
		if (isset($cmp_function)) {
			$this->_cmp_function = $cmp_function;
		} else {
			$this->_cmp_function = array('SuperCollection', 'standardComparison');
		}
	}
	
	protected static function standardComparison($arr) {
		return max($arr);
	}
	
	public function addCollection(Iterator $collection, $field) {
		$this->_collections[] = $collection;
		$this->_fields[] = $field;
	}
	
	protected function _fight() {
		$cmp_modifier = $this->_cmp_modifier;
		$cmp_function = $this->_cmp_function;
		
		$currents = array();
		// Grab the field value from the current DO in each DOC
		for ($i = 0; $i < count($this->_collections); $i++) {
			if ($this->_collections[$i]->valid()) {
				$field = $this->_fields[$i];
				$model = $this->_collections[$i]->current();
				$currents[] = $model->{$field};
			} else {
				$currents[] = null;
			}
		}

		$modifieds = array();
		// Apply any modifier
		foreach ($currents as $val) {
			$modifieds[] = call_user_func($cmp_modifier, $val);
		}
		
		// Send the modified results off to be compared and discover the winner
		$winner = call_user_func($cmp_function, $modifieds);
		
		// So who won?
		for ($i = 0; $i < count($currents); $i++) {
			if ($winner === $modifieds[$i]) {
				return $this->_collections[$i];
			}
		}
		return false;
	}
	
	public function current() {
		$this->_winner = $this->_fight();
		return ($this->_winner !== FALSE && $this->_winner->valid() ? $this->_winner->current() : null);
	}
	
	public function next() {
		$this->_winner->next();
	}
	
	public function rewind() {
		for ($i = 0; $i < count($this->_collections); $i++) {
			$this->_collections[$i]->rewind();
		}
	}
	
	public function valid() {
		for ($i = 0; $i < count($this->_collections); $i++) {
			if ($this->_collections[$i]->valid()) {
				return true;
			}
		}
		return false;
	}
	
	public function count() {
		$count = 0;
		for ($i = 0; $i < count($this->_collections); $i++) {
			$count += count($this->_collections[$i]);
		}
		return $count;
	}
	
	public function key() {
		
	}
	
	public function pluck($key) {
		$return = array();
		foreach ($this as $model) {
			if (is_array($key)) {
				foreach ($key as $k) {
					if ($model->isField($k)) {
						$return[$model->$k] = true;
						break;
					}
				}
			} else {
				$return[$model->$key] = true;
			}
		}
		return array_keys($return);
	}
	
}
