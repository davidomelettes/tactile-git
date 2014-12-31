<?php
/**
 * Accepts email addresses that are verified
 * @author de
 */
class EmailAddressUnverifiedFilter extends FilterIterator {
	
	public function accept() {
		/* @var $item TactileEmailAddress */
		$item = $this->getInnerIterator()->current();
		return !$item->is_verified();
	}
}
