<?php
require_once 'Service/Highrise/Collection.php';

class Service_Highrise_Collection_Emails extends Service_Highrise_Collection{
	
	protected $_path = "emails";
	
	
	public function fetchAll(array $search=array()){
		$path = "{$search['type']}/{$search['id']}/emails";
		$this->getService()->setPath($path);
		$xml = $this->getService()->execute();
		if(empty($xml)){
			return;
		}
		foreach($xml as $item){
			$entity = $this->createEntity();
			$entity->setFromXml($item);
			$this->addEntity($entity);
		}
	}
	
}
?>