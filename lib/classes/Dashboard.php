<?php
/**
 * A container for EGlets
 * @todo implement Iterator and Countable
 *
 */
 
$al=AutoLoader::Instance();
$al->addPath(EGLET_ROOT);
class Dashboard {
	private $_EGlets=array();
	
	function __construct() {
		
	}
	
	public function populate($manifest=null) {
		foreach($this->_EGlets as $eglet) {
			$eglet->populate();		
		}
	}
	function addEGlet($name,EGlet $eglet) {
		$this->_EGlets[$name]=$eglet;
	}
	
	function render($params,&$smarty) {
		foreach($this->_EGlets as $eglet) {
			$eglet->setSmarty($smarty);
		}
		$smarty->assign('eglets',$this->_EGlets);
		$smarty->display('elements/dashboard.tpl');
	}
	
	function getEGlets() {
		return $this->_EGlets;
	}
	
}
?>
