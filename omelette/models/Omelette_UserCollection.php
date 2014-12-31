<?php
/**
 *
 * @author gj
 */
class Omelette_UserCollection extends DataObjectCollection {
	
	public function __construct($user=null) {
		if ($user == null) {
			$user = DataObject::Construct('Omelette_User');
		}
		parent::__construct($user);
		$this->_tablename='omelette_useroverview';
	}
	
}
