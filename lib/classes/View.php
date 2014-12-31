<?php
/**
 * Responsible for storing the data passed from Controllers through to Smarty, specifically for web-requests
 * @author gj
 * @package Views
 */
class View extends BaseView {
	public $is_rss = false;
	public $is_html = false;
	public $is_json = false;
	
	protected $_content_type;
	protected $headers = array();
	
	protected $injector;
	
	/**
	 * Constructor
	 * @param Phemto [$injector]
	 */
	function __construct(Phemto $injector=null) {
		parent::__construct($injector);
		$this->injector = $injector;
	}

	/**
	 * Decide on layout types depending on the manner of the request
	 */
	public function initLayout() {
		$injector = $this->injector;
		$accept = '';
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			list($accept,) = explode(',',$_SERVER['HTTP_ACCEPT']);
		}
		
		/*use different layouts for ajax things- uses the HTTP_X-header that prototype adds, along with the 'Accept' header than can be set from Ajax.Request*/
		if (!empty($injector) && (isset($_GET['ajax']) || 
			(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
				$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))) {
			// We are AJAX
			
			if ($accept=='application/json') {
				$this->smarty->assign('layout','json');
				$this->is_json = true;
			} else {
				$this->smarty->assign('layout','blank');
				$this->is_html = true;
			}
			$this->smarty->assign('partial',true);
			try {
				/*we don't want AJAX requests to redirect most of the time*/
				$redirector = $injector->instantiate('Redirection');
				$cname = get_class($redirector);
				call_user_func(array($cname,'Block'));
			}
			catch(PhemtoException $e) {}
		}
		elseif (isset($_GET['rss'])){
			// We are RSS
			$this->smarty->assign('layout', 'rss');
			$this->is_rss = true;
		}
		elseif (Omelette::isApi()) {
			// We are API
			$this->smarty->assign('layout','api');
			$this->smarty->left_delimiter = '<#';
			$this->smarty->right_delimiter = '#>';
			$this->is_json = true;
			/*we don't want API requests to redirect most of the time*/
			try {
				$redirector = $injector->instantiate('Redirection');
				$cname = get_class($redirector);
				call_user_func(array($cname,'Block'));
			} catch (PhemtoException $e) {}
		}
		else {
			// We are everything else
			$this->smarty->assign('layout','default');
			$this->is_html = true;
		}
	}
	
	
	/**
	 * Returns the path of the template to be used
	 * 
	 * Checks for the existence of an appropriately named template in a number of locations,
	 * and returns the 'most specific' one. Strips leading _s from action-names, returns false on failure
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return String
	 */
	public function getTemplateName($module,$controller,$action) {
		$name=strtolower($controller);
		$action = strtolower($action);
		if (!empty($action)&&$action[0] == '_') {
			$action = substr($action,1);
		}

		if ($this->get('layout') == 'api'){
			$custom_json = STANDARD_TPL_ROOT.'api/'.$module.'/'.$name.'/'.$action.'.json';
			$shared_json = STANDARD_TPL_ROOT.'api/shared/'.$action.'.json';
			$missing_json = STANDARD_TPL_ROOT.'api/template_missing.json';
			$path = (file_exists($custom_json) ? $custom_json :
				(file_exists($shared_json) ? $shared_json : $missing_json));
		} else {
		
			/*check first for UC-specific template*/
			if(defined('EGS_COMPANY_ID')) {
				$usercompany_tpl = USER_ROOT.EGS_COMPANY_ID.'/templates/'.$module.'/'.$name.'/'.$action.'.tpl';
				$dirs[] = $usercompany_tpl;
			}
			
			/*then for custom ones for the installation*/
			$custom_tpl=USER_ROOT.'templates/'.$module.'/'.$name.'/'.$action.'.tpl';
			$dirs[] = $custom_tpl;
			
			/*then standard*/
			$standard_tpl=STANDARD_TPL_ROOT.'includes'.$module.'/'.$name.'/'.$action.'.tpl';
			$dirs[]=$standard_tpl;
			
			/*then for module-indexes (think that's the only case for here)*/
			$standard_tpl2=STANDARD_TPL_ROOT.'includes'.$module.'/'.$action.'.tpl';
			$dirs[] = $standard_tpl2;
			
			/*then for 'shared' (between modules) templates*/
			$shared_tpl = STANDARD_TPL_ROOT.'includes/shared/'.$action.'.tpl';
			$dirs[] = $shared_tpl;
			
			foreach($dirs as $path) {
				if($this->get('layout')=='json') {
					$path = str_replace($action.'.tpl','json_'.$action.'.tpl',$path);
				}
				elseif($this->get('layout') =='rss'){
					$path = str_replace($action.'.tpl','rss_'.$action.'.tpl',$path);
				}
				if(file_exists($path)) {
					return $path;
				}
			}
		}
		return $path;
	}
	
	public function setContentType($type) {
		$this->_content_type = $type;
	}
	
	public function display($template){
		if($this->is_rss){
			header('Content-Type: application/rss+xml');
		}
		if($this->is_json) {
			header('Content-Type: application/json');
		}
		if (isset($this->_content_type)) {
			header('Content-Type: ' . $this->_content_type);
		}
		if ($this->get('layout') == 'api'){
			$this->smarty->display('api/'.$template);
		} else {
			$this->smarty->display($template);
		}
	}
	
	public function setHeader($header) {
		$this->headers[] = $header;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
}
?>
