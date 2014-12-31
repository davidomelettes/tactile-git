<?php
class IntranetLayout extends DataObject {

	protected $defaultDisplayFields = array('name','lastupdated');

	function __construct() {
		parent::__construct('intranet_layouts');
		$this->idField='id';
		
		 

	}
		
	public function load($constraint,$resources=false) {
		$result = parent::load($constraint);
		if($result===false||!$resources) {
			return $result;
		}
		$pattern = '#"resource:([a-z._0-9]+)"#i';
		$replace = '/data/tmp/$1';
		$this->layout=preg_replace($pattern,$replace,$this->layout);
		return $result;
	}
	
	public function get_template($tpl_name,&$tpl_source,&$smarty) {
		if($tpl_name==$this->name){
			$tpl_source = $this->layout;
			return true;
		}
		else {
			return self::getTemplate($tpl_name,$tpl_source);
		}
		return false;
		
	}
	public static function getTemplate($tpl_name,&$tpl_source) {
		$layout = new IntranetLayout();
		$success=$layout->loadBy('name',$tpl_name);
		if($success!==false) {
			$tpl_source=$layout->layout;
		}
		return $success;
	}
	public function get_timestamp($tpl_name,&$timestamp,&$smarty) {
		$timestamp=time();
		return !empty($timestamp);
	}
	
	public function get_secure() {
		return true;
	}
	
	public function get_trusted() {
		return true;
	}	
}
?>
