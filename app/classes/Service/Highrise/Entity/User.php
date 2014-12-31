<?php
require_once('Service/Highrise/Entity.php');

class Service_Highrise_Entity_User extends Service_Highrise_Entity{
	
	protected static $_personCollection;
	
	protected $_person;
	
	public function getPerson(){
		if(is_null($this->_person)){
			$collection = self::getPersonCollection();
			$this->_person = $collection->fetchOne($this->person_id);
		}
		return $this->_person;
	}
	
	protected static function getPersonCollection(){
		if(is_null(self::$_personCollection)){
			self::$_personCollection = new Service_Highrise_Collection_People();
		}
		return self::$_personCollection;
	}
	
}
?>