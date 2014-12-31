<?php
/**
 *
 * @author gj
 */
class TactileAccountCollection extends DataObjectCollection {
	
	public function __construct() {
		parent::__construct(new TactileAccount());
	}
	
}
?>