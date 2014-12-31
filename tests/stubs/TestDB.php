<?php

class TestDB extends DB {

	protected $returns = array();
	
	function getRow($query) {
		if(isset($this->returns['getRow'][$query])) {
			return $this->returns['getRow'][$query];
		}
		return $this->db->getRow($query);
	}
	
	function setReturn($method,$query,$return) {
		$this->returns[$method][$query] = $return;
	}
	
}

?>
