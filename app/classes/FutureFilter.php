<?php
/**
 * Accepts items due neither today nor in the past, nor 'later'
 * @author gj
 */
class FutureFilter extends FilterIterator {
	public function accept() {
		$item = $this->getInnerIterator()->current();
		return !($item->is_overdue() || $item->due_today() || $item->is_later());
	}	
}
?>