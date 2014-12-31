<?php
/**
 * Extensions of this will handle the passing of data from Controllers through to Smarty
 * @author gj
 * @package Views
 */
abstract class BaseView implements Iterator, Countable {
	
	/**
	 * Pointer for iterating
	 * @access protected
	 * @var int $pointer
	 */
	protected $pointer=0;
	
	/**
	 * The instance of smarty used for rendering
	 * @access protected
	 * @var Smarty $smarty
	 */
	protected $smarty;
	
	public function __construct(Phemto $injector=null) {
		$this->smarty=new Smarty;
		$this->smarty->caching = 0;	//no caching
		$this->smarty->cache_dir=DATA_ROOT.'cache';
		$this->smarty->cache_lifetime=45;
		$this->smarty->compile_dir=DATA_ROOT.'templates_c';
		$this->smarty->template_dir=APP_ROOT.'templates';
		$this->smarty->plugins_dir[]='custom_plugins';
		$this->smarty->plugins_dir[]= APP_ROOT.'custom_plugins';
		if(defined('PRODUCTION')&&PRODUCTION) {
			$this->smarty->compile_check=false;
		}
		else {
			$this->smarty->compile_check=true;
		}
		
		$this->smarty->assign('DATE_FORMAT', DATE_FORMAT);
	}
	
	/**
	 * Sets given variable in view
	 * @param string $name name of variable
	 * @param mixed $value content of variable
	 */
	function set($name,$value) {
		//$this->data[$name] = $value;
		$this->smarty->assign($name,$value);
	}	


	/**
	 * indicate that $name should be 'registered' rather than assigned
	 * @param string $name
	 * @param mixed &$value
	 */
	function register($name,&$value) {
		//$this->registered_things[$name]=$value;
		$this->smarty->register_object($name,$value);
	}
	
	/**
	 * Get data from given view variable
	 * @param string $name name of variable to get
	 * @return mixed
	 */
	function get($name) {
		if(isset($this->data[$name])) {
			return $this->data[$name];
		}
		else {
			$var=$this->smarty->get_template_vars($name);
			return $var;
		}
	}
	
	abstract public function getTemplateName($module,$controller,$action);
	
	/**
	 * Adds a directory to the search-path for plugins
	 * 
	 * @param string $path
	 * @param Boolean optional $before Whether to put the path at the beginning- default is end
	 * @return void
	 */
	public function add_plugin_dir($path,$before=false) {
		if($before) {
			$this->smarty->plugins_dir = array_merge(array($path),$this->smarty->plugins_dir);
		}
		else {
			$this->smarty->plugins_dir[]=$path;	
		}
	}
	
	/** to implement Iterator**/
	public function current() {
		$vals=array_values($this->data);
		return $vals[$this->pointer];
	}
	
	public function next() {
		$this->pointer++;
	}
	
	public function key() {
		$keys=array_keys($this->data);
		return $keys[$this->pointer];
	}
	
	public function rewind() {
		$this->pointer=0;
	}
	
	public function valid() {
		return ($this->pointer<count($this));
	}
	/** to implement countable **/
	function count() {
		return count($this->data);
	}
	
	/*pass any other calls through to smarty. This is a bad thing in most cases, as this won't be the only View class for ever*/
	function __call($func,$args) {
		if(is_callable(array($this->smarty,$func))) {
			return call_user_func_array(array($this->smarty,$func),$args);
		}
		throw new Exception('Unknown function: '.$func.' - couldn\'t be passed through to Smarty');
	}
}
?>