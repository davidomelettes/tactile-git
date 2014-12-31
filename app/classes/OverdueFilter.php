<?php
/**
 * Accepts activities that are overdue, i.e. their enddate is in the past
 * @author gj
 */
class OverdueFilter extends FilterIterator {
	
	public function accept() {
		/* @var $item Tactile_Activity */
		$item = $this->getInnerIterator()->current();
		return $item->is_overdue() && !$item->isEvent();
	}
}
?>