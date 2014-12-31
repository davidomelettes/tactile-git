<?php
error_reporting(E_ALL);
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Cache.php';
/**
 * The main body of an Omelette-based application, handles routing and view-rendering
 */
class OmeletteComponent {
	
	/**
	 * The application's View instance
	 * @access protected
	 * @var View $view
	 */
	protected $view;
	
	/**
	 * The application's Phemto instance
	 * @access protected
	 * @var Phemto $injector
	 */
	protected $injector;
	
	/**
	 * The generated controller-name
	 * @access protected
	 * @var String $controller_name
	 */
	protected $controller_name;
	
	/**
	 * The application's controller instance
	 * @access protected
	 * @var Controller $controller
	 */
	protected $controller;
		
	/**
	 * The generated action-name
	 * @access protected
	 * @var String $action
	 */
	protected $action;
	
	/**
	 * The instance of the current user
	 * @var CurrentlyLoggedInUser
	 */
	protected $user;
	
	/**
	 * @var Zend_Auth
	 */
	protected $_auth;
	
	
	/**
	 * Sorts out dependencies, instantiates a View and adds Omelette-specific smarty_plugins to smarty's search path
	 * 
	 * @constructor
	 * @param Phemto $injector
	 */
	public function __construct($injector, $view = null) {
		$this->injector = $injector;
		$this->injectDependencies();
		if(is_null($view)) {
			$this->view = new View($injector);
		}
		else {
			$this->view = $view;
		}
		$this->view->add_plugin_dir(FILE_ROOT.'omelette/smarty_plugins');
	}
	
	/**
	 * Setup the Injector with the classnames to use for various things
	 * @todo this should check whether things have already been registered, to allow extending
	 * @return void
	 */
	protected function injectDependencies() {
		//Prettifier basically does uc_words on things, but knows some exceptions (acronyms)
		$this->injector->register('Prettifier');
		//This uses the USER_SPACE /Hostname concept when authenticating
		$this->injector->register('DatabaseHostnameAuthenticator');
		//we want to use login forms though (OpenID would replace this, probably)
		$this->injector->register('HTMLFormLoginHandler');
		//We do redirects differently to EGS, as we have pretty URLs
		$this->injector->register(new Singleton('OmeletteRedirectHandler'));
		//Similarly for links
		$this->injector->register('OmeletteLinkBuilder');
		//DO::Construct lets us put Omelette_{ModelName} or Tactile_{ModelName} in place of EGS models
		$this->injector->register('OmeletteModelLoader');
		//Similarly for DataField, we can over-ride if it's needed
		$this->injector->register('OmeletteFieldLoader');
		//we want to display dates+timestamps in a 'pretty' way:
		$this->injector->register('PrettyTimestampFormatter');		
		
		$this->injector->register('OmeletteModuleAdminChecker');
		
		$this->injector->register('OmeletteDateValidator');
	}
	
	/**
	 * Takes the rewritten-url bit and determines module/controller/action
	 * 
	 * Adds the module-specific paths to AL's search-path
	 * @return void
	 */
	private function parseRoute() {
		$al = AutoLoader::Instance();
		$rp = RouteParser::Instance();
		
		$rp->parseRoute(isset($_GET['url']) ? $_GET['url'] : '');
		$this->view->set('_area', $rp->Dispatch('area'));
		$this->view->set('_is_admin_area', ($rp->Dispatch('module') == 'admin'));
		
		$controller_name = $rp->Dispatch('controller');
		require_once 'Zend/Auth.php';
		$this->_auth = $auth =  Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			// We know who you are
			$identity = $auth->getIdentity();
			
		} else {
			// Have we got a remembered user?
			if(isset($_COOKIE['TactileCookie'])) {
				require_once 'Auth/Adapter/RememberedUser.php';
				$cookie_data = explode(':', $_COOKIE['TactileCookie']);
				$adapter = new Auth_Adapter_RememberedUser($cookie_data);
				$result = $auth->authenticate($adapter);
				if($result->isValid()) {
					$identity = $result->getIdentity();
					RememberedUser::rememberMe($cookie_data[0], $cookie_data[2]);
				}
			}
			
			elseif (!empty($_GET['openid_login'])) {
				// Begin OpenID discovery
				$domain = $_GET['openid_login'];
				if (preg_match('/@(.+)/', $domain, $matches)) {
					$_SESSION['GOOGLE_APPS_EMAIL'] = $domain;
					$domain = $matches[1];
				}
				
				require_once 'Auth/OpenID/FileStore.php';
				$store = new Auth_OpenID_FileStore(OPENID_FILESTORE_DIRECTORY);
				require_once 'Auth/OpenID/Consumer.php';
				$consumer = new Auth_OpenID_Consumer($store);
				require_once 'GApps/OpenID/Discovery.php';
				$helper = new GApps_OpenID_Discovery($consumer);
				
				// Prepare the auth request
				$auth_request = $consumer->begin($domain);
				if (empty($auth_request)) {
					Flash::Instance()->addError('Authentication Error: Invalid OpenID. Please check your email address and try again.');
				} else {
					$realm = 'http' . (Omelette::isHttps() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . '/';
					$redirect_to = $realm . 'openid_login.php';
					$realm = str_replace('://www.', '://*.', $realm);
					$redirect_url = $auth_request->redirectURL($realm, $redirect_to);
					if (Auth_OpenID::isFailure($redirect_url)) {
						Flash::Instance()->addError('Failed to redirect to authentication server: ' . $redirect_url->message);
					} else {
						header('Location: '.$redirect_url);
					}
				}
			}
			
			else {
				// these options are non-persistent
				require_once 'Zend/Auth/Storage/NonPersistent.php';
				
				// API request? - build id from api token
				if (!empty($_GET['api_token']) && $rp->routeIsApiWhitelisted()) {
					Omelette::setIsApi(true);
					$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
					require_once 'Auth/Adapter/ApiToken.php';
					$result = $auth->authenticate(new Auth_Adapter_ApiToken($_GET['api_token']));
					if ($result->isValid()) {
						$identity = $result->getIdentity();
					}
				}
				
				// Public controller? - build from webkey
				if (!empty($_GET['key']) && $rp->routeIsWebkeyWhitelisted()) {
					$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
					require_once 'Auth/Adapter/Webkey.php';
					$result = $auth->authenticate(new Auth_Adapter_Webkey($_GET['key']));
					if ($result->isValid()) {
						$identity = $result->getIdentity();
					}
				}
				
				if ($rp->routeIsPublicWhitelisted()) {
					// Auth as the account's public user
					$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
					if (FALSE !== $identity = Omelette::getPublicIdentity()) {
						$auth->getStorage()->write($identity);
					}
				}
			}
		}
		
		if (!isset($identity)) {
			// Otherwise, send to login
			$rp->setDispatch('module','login');
			$this->controller_name = 'index';
			$this->action = $rp->Dispatch('action');
			$al->addPath(FILE_ROOT.'egs/controllers/login/');
			$al->addPath(FILE_ROOT.'omelette/controllers/login/');
			$al->addPath(FILE_ROOT.'app/controllers/login/');
			return;
		}
		
		// We have a user
		$this->user = CurrentlyLoggedInUser::Instance();
		if(!defined('EGS_USERNAME')) {
			define('EGS_USERNAME',$this->user->getRawUsername());
		}
		EGS::setUsername($this->user->getRawUsername());
		if(!defined('EGS_COMPANY_ID')) {
			define('EGS_COMPANY_ID',$this->user->getUserCompanyID());
		}
		EGS::setCompanyId($this->user->getUserCompanyId());
		EGS::setDateFormat($this->user->getModel()->date_format);
		$this->view->set('DATE_FORMAT', EGS::getDateFormat());
		PrettyTimestampFormatter::setDefaultTimezone($this->user->getTimezoneString());
		
		EGS::setCurrencySymbol('');
		
		EGS::setCountryCode($this->user->getAccount()->country_code);
		$this->view->set('COUNTRY_CODE', EGS::getCountryCode());
		
		$this->view->set('current_user',$this->user);
		if(!isset($_SESSION['user_company_name'])) {
			$_SESSION['user_company_name'] = $this->user->getOrganisationName();
		}
		$this->view->set('user_company_name',$_SESSION['user_company_name']);
		$this->controller_name = $rp->Dispatch('controller');
		$this->action = $rp->Dispatch('action');
		if($this->action=='new') {
			$this->action='_new';
		}
		$al->addPath(FILE_ROOT.'egs/controllers/'.$rp->Dispatch('module').'/');
		$al->addPath(FILE_ROOT.'omelette/controllers/'.$rp->Dispatch('module').'/');
		$al->addPath(FILE_ROOT.'app/controllers/'.$rp->Dispatch('module').'/');
		
		$this->view->set('action',$this->action);
	}
	
	/**
	 * 'Does the business'
	 * 
	 * Instantiates a controller, calls an action, renders a view or redirects
	 * @return void
	 */
	final public function go() {
		$this->parseRoute();
		
		$redirector = $this->injector->instantiate('Redirection');
		$continue = $this->checkPermission();
		if(!$continue) {
			$flash=Flash::Instance();
			$flash->save();
			$redirector->go();
			return;
		}
		
		$rp = RouteParser::Instance();
		if (empty($this->action) || empty($this->controller_name)) {
			// Something went wrong
			require_once '../app/controllers/omelette/ErrorController.php';
			$rp->setDispatch('module', 'omelette');
			$this->controller_name = 'Error';
			$this->action = 'not_found';
		}
		
		if(!isset($_SESSION['preferences'])) {
			$_SESSION['preferences']=array();
		}
		/*check whether the controller name is valid*/
		if(empty($this->controller_name)) {
			throw new Exception("Controller Not Found");
		}
		
		$name = ucfirst($this->controller_name).'Controller';
		if(class_exists(get_class($this).'_'.$name)) {
			$name=get_class($this).'_'.$name;
		}
		else if(class_exists('Omelette_'.$name)) {
			$name='Omelette_'.$name;
		}
		
		// Set the layout (rss/ajax/api/etc.)
		$this->view->initLayout();
		
		/*give all the request-like data we have to the controller*/
		$this->controller = new $name($rp->Dispatch('module'),$this->view);
		$this->controller->setInjector($this->injector);
		$this->controller->setData($rp->Dispatch());
		$this->controller->setData($_GET);
		if (isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD'] &&
			isset($_SERVER['CONTENT_TYPE']) && preg_match('/application\/json/', $_SERVER['CONTENT_TYPE'])) {
			
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);
			$this->controller->setData($data);
		} else {
			$this->controller->setData($_POST);
		}
		if (count($_POST) > 0) {
			$this->controller->setIsPost();
		}
		
		if($this->isLoggedIn() && $rp->Dispatch('controller') != 'public') {
			Omelette_Magic::loadAll($this->user->getRawUsername());
		}
		
		//the controller should be able to log things:
		$log = new Zend_Log();
		
		try {
			$file_writer = new Zend_Log_Writer_Stream(DATA_ROOT.'application.log');
		} catch (Zend_Log_Exception $e) {
			echo "Failed to open log file for writing: " . $e->getMessage();
			exit;
		}
		$file_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::INFO));
		$log->addWriter($file_writer);
		
		if (defined('PRODUCTION') && PRODUCTION) {
			$mail_writer = new Log_Writer_Mail(NOTIFICATIONS_TO, 'Tactile Error');
			$mail_writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN));
			$log->addWriter($mail_writer);
		}
		
		$this->controller->setLogger($log);
		
		$registry = Zend_Registry::getInstance();
		$registry->set('logger', $log);
		$frontendOptions = array(
			'automatic_serialization' => true,
			'caching' => defined('PRODUCTION') && PRODUCTION,
			'write_control' => true,
			'logging' => true,
			'logger' => $log
		   );
		$backendOptions  = array(
			'cache_dir' => '../data/cache'
		);
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$registry->set('cache', $cache);
		/*give it the default template name (the action)*/
		$this->controller->setTemplateName($this->action);
		
		// Anything that needs to happen before the action is carried out
		$this->_preDispatch();

		/*do the business*/
		try {
			$this->controller->{$this->action}();
		}
		catch(Exception $e) {
			$log->crit('Exception: ' . $e->getMessage());
			$log->crit('Trace:' . $e->getTraceAsString());
			$this->handleError($e);
			return;
		}
		/*then we want to make sure all the 'used' models are given to the view*/
		$this->controller->assignModels();
		/*save flash*/
		$flash=Flash::Instance();
		$flash->save();
		$this->view->set('flash',$flash);
		$prefs=$_SESSION['preferences'];
		$this->view->set('prefs',$prefs);
		
		/*if something has asked to redirect, then do that*/
		if($redirector->willRedirect()) {
			$redirector->go();
			return;
		}
		
		foreach ($this->view->getHeaders() as $header) {
			header($header);
		}
		
		// Anything that needs to happen after the action, but before the template render
		$this->_postDispatch();
		
		/*otherwise, display the template*/
		$this->view->display('index.tpl');

		// Anything that needs to happen after the template render (this is normally the end of thread execution)
		$this->_postRender();
	}
	
	/**
	 * Use ErrorController to display something instead of a blank screen
	 */
	public function handleError($e=null) {
		require_once '../app/controllers/omelette/ErrorController.php';
		$rp = RouteParser::Instance();
		$rp->setDispatch('module', 'omelette');
		$this->controller_name = 'Error';
		$this->action = 'error';
		
		$controller = new ErrorController($rp->Dispatch('module'), $this->view);
		$controller->setInjector($this->injector);
		$controller->setTemplateName($this->action);
		$controller->assignModels();
		$controller->error($e);
		
		$this->view->display('index.tpl');
		
		throw $e;
	}
	
	/**
	 * Checks permission of the App's module/controller/action
	 * 
	 * Redirects if no-access
	 * @return void
	 */
	private function checkPermission() {
		$rp = RouteParser::Instance();
		$user = CurrentlyLoggedInUser::Instance();
		if($user === false) {
			return ($rp->Dispatch('module') == 'login' ||
				$rp->routeIsPublicWhitelisted()
			);
		}
		
		// New Users must agree to terms, if account is not suspended
		if(!$user->hasAgreedToTerms()) {
			if(!($rp->Dispatch('module')=='tactile' && $rp->Dispatch('controller')=='terms') && $rp->Dispatch('module')!=='suspension') {
				sendTo('terms');
				return false;
			}
		}
		
		// Only allowed to log out and view the suspension page if account is suspended
		if($user->hasValidAccount()===false && ($rp->Dispatch('module')!=='login' && $rp->Dispatch('action')!=='logout')) {
			if($rp->Dispatch('module')!=='suspension') {
				if ($user->isAccountOwner()) {
					sendTo('suspension', 'take_payment');
				} else {
					sendTo('suspension');
				}
				return false;
			}
			//so module is 'suspension'...only admins can pay, everyone else just sees a message
			if(!$user->isAccountOwner() && $rp->Dispatch('action')!=='index') {
				Flash::Instance()->addError('Only admins can pay for subscriptions');
				sendTo('suspension');
				return false;
			}
		}
		if($rp->Dispatch('module')=='admin' && (!$user->isAdmin() || ($rp->Dispatch('controller')=='account' && !$user->isAccountOwner()))) {
			Flash::Instance()->addError('You don\'t have permission to access that');
			sendTo();
			return false;
		}
		return true;
	}
	
	public function isLoggedIn(){
		return $this->_auth->hasIdentity();
	}
	
	public function requiresLogin($module) {
		return true;
	}
	
	public function getControllerName() {
		return $this->controller_name;
	}
	
	protected function _checkMotd() {
		$user = CurrentlyLoggedInUser::Instance();
		if (FALSE !== $user) {
			$sql = 'SELECT id, content, important FROM motds WHERE active AND message_start <= now() AND (message_end IS NULL OR message_end > now()) ORDER BY message_start DESC LIMIT 1';
			$dismissed_id = Omelette_Magic::getValue('dismissed_motd_id', $user->getRawUsername());
			$db = DB::Instance();
			$row = $db->getRow($sql);
			if (!empty($row) && $dismissed_id != $row['id']) {
				$this->view->set('_motd_id', $row['id']);
				if ($row['important'] == 't') {
					$this->view->set('_motd_important', $row['content']);
				} else {
					$this->view->set('_motd', $row['content']);
				}
			}
		}
	}

	protected function _preDispatch() {
		
	}
	
	protected function _postDispatch() {
		$rp = RouteParser::Instance();
		if ($this->isLoggedIn() && $rp->Dispatch('controller') != 'public') {
			$this->_checkMotd();
			
			$recently_viewed = ViewedPage::getList(10);
			$this->view->Set('recently_viewed',$recently_viewed);
			
			// Load custom searches
			$advanced_searches = new AdvancedSearchCollection();
			$sh = new SearchHandler($advanced_searches, false);
			$sh->extract(true);
			$sh->perpage = 0;
			$sh->setOrderBy('name');
			$advanced_searches->load($sh);
			$this->view->set('advanced_searches', $advanced_searches);
			$org_searches = array();
			$per_searches = array();
			$opp_searches = array();
			$act_searches = array();
			foreach ($advanced_searches as $search) {
				switch ($search->record_type) {
					case 'org':
						$org_searches[] = $search;
						break;
					case 'per':
						$per_searches[] = $search;
						break;
					case 'opp':
						$opp_searches[] = $search;
						break;
					case 'act':
						$act_searches[] = $search;
						break;
				}
			}
			$this->view->set('advanced_searches_org', $org_searches);
			$this->view->set('advanced_searches_per', $per_searches);
			$this->view->set('advanced_searches_opp', $opp_searches);
			$this->view->set('advanced_searches_act', $act_searches);
		}
	}
	
	protected function _postRender() {
		$min = date('i');
		if (!empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['HTTP_HOST'])) {
			if ((defined('LOG_SQL') && LOG_SQL) || ($min%5 == 0) || ($min == date('j')) || ($min == date('w')) || ($min == date('W')) || ($min == date('n')) || ($min == date('G'))) {
				$db = DB::Instance();
				$sql = 'INSERT INTO page_load (host, url, runtime) VALUES ('.$db->qstr($_SERVER['HTTP_HOST']).','.$db->qstr($_SERVER['REQUEST_URI']).','.$db->qstr(microtime(true) - START_TIME).')';
				$db->Execute($sql);
			}
		}
	}	
}
