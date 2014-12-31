<?php
require_once 'Service/Highrise/Exception.php';
require_once 'Service/Highrise/Entity.php';

/**
 * @package Service_Highrise
 * @author Paul M Bain
 */
class Service_Highrise_Collection implements Iterator, Countable{
	
	/*
	 * Path to the corresponding URI 
	 * @var String
	 */
	protected $_path;
	
	/**
	 * @var Service_Highrise
	 */
	static $_service;
	
	/**
	 * @var array Items
	 */
	protected $_elements = array();
	
	/**
	 * @var int position
	 */
	protected $_position;
	
	/**
	 * The default class for entities 
	 */
	protected $_entityClass = "Service_Highrise_Entity";
	
	/**
	 * Set the entity class for this collection
	 * 
	 * @param String @entityClass the class to use for entities within this collection
	 */
	public function setEntityClass($entityClass){
		$this->_entityClass = $entityClass;
	}
	
	/**
	 * Set the service to use for all Service_Highrise_Collection models
	 * 
	 * @param Service_Highrise $service 
	 */
	public static function setDefaultService(Service_Highrise $service){
		self::$_service = $service;
	}
	
	/**
	 * Get the service used by this Service_Highrise_Collection
	 * 
	 * @return Service_Highrise
	 */
	public function getService(){
		return self::$_service;
	}
	
	/**
	 * @return Service_Highrise_Entity
	 */
	public function createEntity(){
		$config = array(
			'collection'	=>	$this
		);
		return new $this->_entityClass($config);
		
	}
	
	/**
	 * Get path
	 * @path
	 */
	public function getPath(){
		return $this->_path;
	}
	
	public function fetchOne($id){
		$this->getService()->setPath($this->getPath()."/$id");
		$xml = $this->getService()->execute();
		$entity = $this->createEntity();
		$entity->setFromXml($xml);
		return $entity;
	}
	
	public function fetchAll(array $search=array()){
		$path = $this->getPath();
		if(isset($search['id'])){
			$path.="/{$search['id']}";
		}
		
		$this->getService()->setPath($path);
		$xml = $this->getService()->execute();
		if (FALSE === $xml) {
			require_once 'Service/Highrise/Exception.php';
			throw new Service_Highrise_Exception('Failed to parse XML from response');
		}
		foreach($xml as $item){
			$entity = $this->createEntity();
			$entity->setFromXml($item);
			$this->addEntity($entity);
		}
		return true;
	}
	
	public function toArray(){
		$data;
		foreach($this as $v){
			$data[]=$v->toArray();
		}
		return $data;
	}
	
	public function addEntity($entity){
		$this->_elements[]=$entity;
	}
	
	
	
	/**
	 * Iterator methods
	 */
    public function rewind() {
        $this->_position = 0;
    }
    public function current() {
        return $this->_elements[$this->_position];
    }
    public function key() {
        return $this->_position;
    }
    public function next() {
        ++$this->_position;
    }
    public function valid() {
        return isset($this->_elements[$this->_position]);
    }
	
    public function count(){
    	return count($this->_elements);
    }
}
?>