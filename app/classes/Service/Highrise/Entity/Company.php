<?php
require_once('Service/Highrise/Entity.php');

class Service_Highrise_Entity_Company extends Service_Highrise_Entity{
	
	protected $_noteCollection;
	protected $_notes;
	
	protected $_emailCollection;
	
	public function getNotes(){
		if(is_null($this->_noteCollection)){
			$this->_noteCollection = new Service_Highrise_Collection_Notes();
			$this->_noteCollection->fetchAll(array('type'=>'companies','id'=>(string)$this->id));
		}
		return $this->_noteCollection;
	}
	
	public function getEmails(){
		if(is_null($this->_emailCollection)){
			$this->_emailCollection = new Service_Highrise_Collection_Emails();
			$this->_emailCollection->fetchAll(array('type'=>'companies','id'=>(string)$this->id));
		}
		return $this->_emailCollection;
	}
	
}
?>