<?php
/**
 * Accepts items which are due today
 * @author gj
 */
class TodayFilter extends FilterIterator {
	public function accept() {
		$item = $this->getInnerIterator()->current();
		return $item->due_today() && !$item->is_overdue();
	}
}
?>