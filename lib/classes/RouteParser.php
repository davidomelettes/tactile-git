<?php
/**
 * Responsible for parsing routes
 * @author gj
 */
class RouteParser {
	
	/**
	 * An array of added Routes
	 * @access protected
	 * @var Array $routes
	 */
	protected $routes = array();
	
	/**
	 * An array of the parsed paramaters
	 * @access protected
	 * @var Array $dispatch
	 */
	protected $dispatch = array();
	
	/**
	 * An array of routes allowing API access ('module' => 'controllers' => 'actions')
	 * @access protected
	 * @var Array $_api_whitelist
	 */
	protected $_api_whitelist = array();
	
	/**
	 * An array of routes allowing access via a webkey ('module' => 'controllers' => 'actions')
	 * @access protected
	 * @var Array $_webkey_whitelist
	 */
	protected $_webkey_whitelist = array();
	
	/**
	 * Private constructor, use RouteParser::Instance.
	 */
	private function __construct() {
		$this->dispatch=$_GET;	
	}
	
	/**
	 * Returns an instance of RouterParser
	 *
	 * @return RouteParser
	 */
	public static function Instance() {
		static $instance;
		
		if (!isset($instance)) {
			$instance = new RouteParser();
		}
		
		return $instance;
	}
	
	/**
	 * Add a router to the parser
	 *
	 * @param Route $route
	 * @return Boolean
	 */
	public function AddRoute(BaseRoute $route) {
		$this->routes[] = $route;
		return true;
	}
	
	/**
	 * Takes a url and extracts the captured arguments. Returns true iff a route matches.
	 * @todo we could cache this, lots of REs will be fairly slow
	 * @param String $url
	 * @return boolean
	 */
	public function ParseRoute ($url) {
		foreach ($this->routes as $route) {
			preg_match('#' . $route->GetRegex() . '#', $url, $matches);
			
			if ( !empty($matches) ) {
				$this->dispatch = array_merge($route->GetPredefinedArguments(),$matches);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Accessor for items in RouteParser::dispatch. Returns the entire array with no argument
	 * @param String $key
	 * @return mixed
	 */
	public function Dispatch ($key = null) {
		if (empty($key)) {
			return $this->dispatch;
		} else {
			if (array_key_exists($key, $this->dispatch)) {
				if($key == 'action' && $this->dispatch[$key] == 'new'){
					$this->dispatch[$key] = '_new';
				}
				return $this->dispatch[$key];
			} else {
				return null;
			}
		}
	}
	
	public function setDispatch($key,$val) {
		$this->dispatch[$key] = $val;
	}
	
	public function setApiRouteWhitelist($whitelist) {
		$this->_api_whitelist = $whitelist;
	}
	
	public function routeIsApiWhitelisted($module=null, $controller=null, $action=null) {
		if (!isset($module)) {
			$module = $this->Dispatch('module');
		}
		if (!isset($controller)) {
			$controller = $this->Dispatch('controller');
		}
		if (!isset($action)) {
			$action = $this->Dispatch('action');
		}
		
		if (isset($this->_api_whitelist[$module]) &&
			isset($this->_api_whitelist[$module][$controller]) &&
			in_array($action, $this->_api_whitelist[$module][$controller])) {
			
			return true;
		}
		return false;
	}
	
	public function setWebkeyRouteWhitelist($whitelist) {
		$this->_webkey_whitelist = $whitelist;
	}
	
	public function routeIsWebkeyWhitelisted($module=null, $controller=null, $action=null) {
		if (!isset($module)) {
			$module = $this->Dispatch('module');
		}
		if (!isset($controller)) {
			$controller = $this->Dispatch('controller');
		}
		if (!isset($action)) {
			$action = $this->Dispatch('action');
		}
		
		if (isset($this->_webkey_whitelist[$module]) &&
			isset($this->_webkey_whitelist[$module][$controller]) &&
			in_array($action, $this->_webkey_whitelist[$module][$controller])) {
			
			return true;
		}
		return false;
	}
	
	public function setPublicRouteWhitelist($whitelist) {
		$this->_public_whitelist = $whitelist;
	}
	
	public function routeIsPublicWhitelisted($module=null, $controller=null, $action=null) {
		if (!isset($module)) {
			$module = $this->Dispatch('module');
		}
		if (!isset($controller)) {
			$controller = $this->Dispatch('controller');
		}
		if (!isset($action)) {
			$action = $this->Dispatch('action');
		}
		
		if (isset($this->_public_whitelist[$module]) &&
			isset($this->_public_whitelist[$module][$controller]) &&
			in_array($action, $this->_public_whitelist[$module][$controller])) {
			
			return true;
		}
		return false;
	}
}
