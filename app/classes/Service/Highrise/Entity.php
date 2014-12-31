<?php
require_once 'Service/Highrise/Collection.php';
/**
 * @package Service_Highrise
 * @author Paul M Bain
 *
 */
class Service_Highrise_Entity{
	
	/**
	 * @var Service_Highrise_Collection
	 */
	protected $_collection;
	
	/**
	 * @var array data items associated with this entity
	 */
	public $_data;
	
	/**
	 * Construct the Service_Highrise_Entity object
	 * @param array $config configuration data
	 */
	public function __construct(array $config = array()){
		if(isset($config['collection']) && $config['collection'] instanceof Service_Highrise_Collection){
			$this->_collection = $config['collection'];
		} 
	}
	
	/**
	 * Get the collection object associated within this Entity
	 * @return Service_Highrise_Collection
	 */
	public function getCollection(){
		return $this->_collection;
	}
	
	/**
	 * Set data from an array using key value pairs
	 * @param array $data 
	 */
	public function setFromArray(array $data){
		if(empty($data)){
			return;
		}
		foreach($data as $k=>$v){
			$this->_data->$k = $v;
		}
	}
	
	/**
	 * Update data from XML
	 */
	public function setFromXml($xml){
		$this->_data = $xml;
	}
	
	/**
	 * Access for data stored within this Entity
	 * @param String $var the name of the data item
	 * @return mixed
	 */
	public function __get($var){
		
		$var = str_replace('_','-',$var);
		return $this->_data->$var;
	
	
	}
	
	/**
	 * Set data item
	 * @param String $var name of data item to set
	 * @param mixed $val 
	 */
	public function __set($var,$val){
		$this->_data[$var]=$val;
	}
	
	public function enforce_array($obj) {
		$array = (array)$obj;

		$data=null;
		if(empty($array)) {
			return null;
		}
		foreach($array as $key=>$value) {
		
			echo $key."\n";
			if((string)$key == "email-addresses"){
				print_r($value);
			}
			

			if($key=="SimpleXMLElement"){
				$data[] =  $this->enforce_array($value);
			}			

			if($key=="@attributes"){		
				continue;
			}
			
			
			if(false !== strpos($key,'-')){
				$key = str_replace('-','_',$key);
			}
			if(!is_scalar($value)) {
				$data[$key] = $this->enforce_array($value);
			}
			else {
				$data[$key] = $value;
			}
		}

		return $data;
	}
	
	/**
	 * Return an array rep'n the data stored in this object
	 *
	 * @return mixed
	 */
	public function toArray(){
		return $this->_data;
	}

	
}
?>