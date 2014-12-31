<?php

abstract class EGlet {
	protected $template='eglets/eglet.tpl';
	protected $contents='';
	public $should_render=true;
	protected $renderer;
	function __construct(Renderer $renderer) {
		$this->renderer=$renderer;
	}
	
	abstract function populate() ;
	
	abstract function render();
	
	function getTemplate() {
		return $this->template;
	}
	function setSmarty(&$smarty) {
		$this->smarty=$smarty;
	}
	function getContents() {
		return $this->contents;
	}
	
	function isCached() {
		return (isset($_SESSION['eglet_cache'][$this->getCacheID()]));
	}
	
	function getCache() {
		if(!$this->isCached()) {
			throw new Exception('Cache value doesn\'t exist');
		}
		return unserialize($_SESSION['eglet_cache'][$this->getCacheID()]);
	}
	
	function setCache($val) {
		$_SESSION['eglet_cache'][$this->getCacheID()] = serialize($val);
	}
	
	function getCacheID() {
		return 'eglet'.EGS_COMPANY_ID.get_class($this).date('YmdH');
	}
}


?>