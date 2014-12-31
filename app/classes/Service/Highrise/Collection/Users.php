<?php
require_once 'Service/Highrise/Collection.php';
require_once 'Service/Highrise/Entity/User.php' ;

class Service_Highrise_Collection_Users extends Service_Highrise_Collection{
	
	protected $_path = "users";
	
	/**
	 * overload FTW
	 *
	 * @var string
	 */
	protected $_entityClass = "Service_Highrise_Entity_User";
	

}
?>