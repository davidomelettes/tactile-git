<?php
/**
 * Extends OmeletteComponent to give a prettier interface
 * @author gj
 * @package Tactile
 */
class Tactile extends OmeletteComponent {

	/**
	 * Adds the app-specific smarty_plugins dir to smarty's search path
	 * 
	 * @param $injector Phemto
	 */
	function __construct($injector, $view = null) {
		parent::__construct($injector, $view);
		$this->view->add_plugin_dir(APP_ROOT . 'smarty_plugins',true);
		CurrentlyLoggedInUser::setAccountClassName('TactileAccount');
	}
	
	protected function injectDependencies() {
		parent::injectDependencies();
		$this->injector->register('TactilePasswordValidator');
		
		$this->injector->register('Tactile_BooleanFormatter');
	}

}
?>