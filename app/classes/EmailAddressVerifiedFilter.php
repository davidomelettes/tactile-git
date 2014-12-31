<?php
/**
 * Accepts email addresses that are verified
 * @author de
 */
class EmailAddressVerifiedFilter extends FilterIterator {
	
	public function accept() {
		/* @var $item TactileEmailAddress */
		$item = $this->getInnerIterator()->current();
		return $item->is_verified();
	}
}
