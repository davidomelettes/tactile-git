<?php
require_once 'Service/Freshbooks/Response/ListAbstract.php';

class Service_Freshbooks_Response_Estimate_List extends Service_Freshbooks_Response_ListAbstract {
	
	protected $_estimates = array();
	
	protected function _init() {
		require_once 'Service/Freshbooks/Entity/Estimate.php';
		if(isset($this->_xmlElement->estimates->estimate)) {
			foreach($this->_xmlElement->estimates->estimate as $estimate) {
				if(isset($estimate->estimate_id)) {
					$this->_estimates[] = new Service_Freshbooks_Entity_Estimate($estimate, $this->getService());
				}
			}
		}
	}
	
	/**
	 * Return an array of Service_Freshbooks_Entity_Estimate objects
	 *
	 * @return array
	 */
	public function getEstimates() {
		return $this->_estimates;
	}
	
	public function getListElement() {
		return $this->_xmlElement->estimates;
	}
	
}

