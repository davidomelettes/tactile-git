<?php

class TestView extends View {

	public $template;
	
	public $output;
	
	/**
	 * 
	 *@param Phemto [$injector] 
	 */
	function __construct($injector = null) {
		parent::__construct($injector);
	
	}

	public function display($template) {
		$this->template = $template;
//		$this->smarty->display($template);
//		$this->output = trim(ob_get_clean());
		$this->output = $this->smarty->fetch($template);
	}
	
	public function getTestHeaders() {
		return $this->headers;
	}
	
	public function getHeaders() {
		return array();
	}
}

?>
