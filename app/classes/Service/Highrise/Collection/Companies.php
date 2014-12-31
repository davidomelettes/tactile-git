<?php
require_once 'Service/Highrise/Collection.php';
require_once 'Service/Highrise/Entity/Company.php';

class Service_Highrise_Collection_Companies extends Service_Highrise_Collection{
	
	protected $_path = "companies";
	protected $_entityClass = "Service_Highrise_Entity_Company";	
}
?>