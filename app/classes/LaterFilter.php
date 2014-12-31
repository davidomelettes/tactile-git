<?php
/**
 * Accepts activities that are 'later'
 * @author gj
 */
class LaterFilter extends FilterIterator {
	
	public function accept() {
		$item = $this->getInnerIterator()->current();
		return $item->is_later();
	}
}
?>