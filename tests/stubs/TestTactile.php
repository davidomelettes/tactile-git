<?php

class TestTactile extends Tactile {

	/**
	 * 
	 *@param $injector Phemto 
	 */
	function __construct($injector) {
		$this->injector = $injector;
		$this->injectDependencies();
		$this->view = new View($injector);
		$this->view->add_plugin_dir(FILE_ROOT.'omelette/smarty_plugins');
	}
	
	function go() {
		
	}
}

?>
