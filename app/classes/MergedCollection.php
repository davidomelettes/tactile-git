<?php

/**
 *
 */
class MergedCollection implements Iterator, Countable  {

	/**
	 * The DOCs used for the R and L sides of the merge
	 *
	 * @var DataObjectCollection
	 */
	protected $left, $right;
	
	/**
	 * The fields, for R and L sides, to compare when merging
	 *
	 * @var String
	 */
	protected $left_field, $right_field;
	
	/**
	 * Optional lambda function to use for comparisons - vales are put through this before being compared
	 * @var Function
	*/
	protected $cmp_modifier;
	
	/**
	 * A comparison function, takes two values and returns which is 'first'
	 *
	 * @var Function
	 */
	protected $cmp_function;
	
	/**
	 * 
	 */
	function __construct($cmp_modifier = null, $cmp_function = null) {
		if (isset($cmp_modifier)) {
			$this->cmp_modifier = $cmp_modifier;
		}
		else {
			$this->cmp_modifier = create_function('$x', 'return $x;');
		}
		
		if (isset($cmp_function)) {
			$this->cmp_function = $cmp_function;
		}
		else {
			$this->cmp_function = array('MergedCollection', 'standardComparison');
		}
	}
	
	public function setLeft(Iterator $left, $left_field) {
		$this->left = $left;
		$this->left_field = $left_field;
	}
	
	public function setRight(Iterator $right, $right_field) {
		$this->right = $right;
		$this->right_field = $right_field;
	}
	
	public function mergeAgain(Iterator $new_collection, $new_field) {
		$new = new MergedCollection();
		$new->setLeft($this, $this->left_field);
		$new->setRight($new_collection, $new_field);
		return $new;
	}
	
	private function fight() {
		if(!$this->left->valid()) {
			return $this->right;
		}
		if(!$this->right->valid()) {
			return $this->left;
		}
		$cmp_modifier = $this->cmp_modifier;
		$cmp_function = $this->cmp_function;
		
		$left_value = $this->left->current()->{$this->left_field};
		$right_value = $this->right->current()->{$this->right_field};
		
		$left_value = call_user_func($cmp_modifier, $left_value);
		$right_value = call_user_func($cmp_modifier, $right_value);
		
		$winner = call_user_func($cmp_function, $left_value, $right_value);
		
		return $winner == $left_value ? $this->left : $this->right;
	}
	
	protected static function standardComparison($a, $b) {
		if(is_null($b)) {
			var_dump($a);
		}
		return strcmp($a, $b) == 1 ? $a : $b;
	}
	
	public function current() {
		$this->winner = $this->fight();
		$value = $this->winner->current();
		return $value;
	}
	
	public function key() {
		
	}
	
	public function next() {
		$this->winner->next();
	}
	
	public function rewind() {
		$this->left->rewind();
		$this->right->rewind();
	}
	
	public function valid() {
		return $this->left->valid() || $this->right->valid();
	}
	
	public function count() {
		return count($this->left) + count($this->right);
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
