<?php
require_once 'Service/Highrise/Collection.php';
require_once 'Service/Highrise/Entity/Person.php';

class Service_Highrise_Collection_People extends Service_Highrise_Collection{
	
	protected $_path = "people";
	protected $_entityClass = "Service_Highrise_Entity_Person";
}
?>