<?php
class EGSApplication {
	
	
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
	 * Sorts out dependencies, instantiates a View and adds Omelette-specific smarty_plugins to smarty's search path
	 * 
	 * @constructor
	 * @param Phemto $injector
	 */
	public function __construct($injector, $view = null) {
		$this->injector = $injector;
		$this->injectDependencies();
		if(is_null($view)) {
			try {
				$this->view = $this->injector->instantiate('View');
			}
			catch (PhemtoException $e) {
				$this->view = new View($injector);
			}
		}
		else {
			$this->view = $view;
		}
		$this->view->add_plugin_dir(APP_ROOT.'smarty_plugins');
	}
	
	/**
	 * Setup the Injector with the classnames to use for various things
	 * @todo this should check whether things have already been registered, to allow extending
	 * @return void
	 */
	protected function injectDependencies() {
		//Prettifier basically does uc_words on things, but knows some exceptions (acronyms)
		$this->injector->register('Prettifier');
		//We do redirects differently to EGS, as we have pretty URLs
		$this->injector->register('NewRedirectHandler');
	}
	
	/**
	 * Takes the rewritten-url bit and determines module/controller/action
	 * 
	 * Adds the module-specific paths to AL's search-path
	 * @return void
	 */
	private function parseRoute() {
		$al = AutoLoader::Instance();
		//DB::Debug();
		$rp = RouteParser::Instance();
		$rp->parseRoute(isset($_GET['url'])?$_GET['url']:'');
		
		$this->controller_name = $rp->Dispatch('controller');
		$this->action = $rp->Dispatch('action');
		$al->addPath(FILE_ROOT.'app/controllers/'.$rp->Dispatch('module').'/');
	}
	
/**
	 * 'Does the business'
	 * 
	 * Instantiates a controller, calls an action, renders a view or redirects
	 * @return void
	 */
	public function go() {
		$this->parseRoute();
		if(!isset($_SESSION['preferences'])) {
			$_SESSION['preferences']=array();
		}
		/*check whether the controller name is valid*/
		if(empty($this->controller_name)) {
			exit;sendTo();
		}
		else {
			$name = ucfirst($this->controller_name).'Controller';
			if(class_exists(get_class($this).'_'.$name)) {
				$name=get_class($this).'_'.$name;
			}
			else if(class_exists('Omelette_'.$name)) {
				$name='Omelette_'.$name;
			}
			/*give all the request-like data we have to the controller*/
			$rp = RouteParser::Instance();
			$this->controller = new $name($rp->Dispatch('module'),$this->view);
			$this->controller->setInjector($this->injector);
			$this->controller->setData($rp->Dispatch());
			$this->controller->setData($_GET);
			$this->controller->setData($_POST);
			

			/*give it the default template name (the action)*/
			$this->controller->setTemplateName($this->action);
			/*do the business*/
			$this->controller->{$this->action}();
			/*then we want to make sure all the 'used' models are given to the view*/
			$this->controller->assignModels();
			/*save flash*/
			$flash=Flash::Instance();
			$flash->save();
			$this->view->set('flash',$flash);
			$prefs=$_SESSION['preferences'];
			$this->view->set('prefs',$prefs);
		}
		/*if something has asked to redirect, then do that*/
		$redirector = $this->injector->instantiate('Redirection');
		if($redirector->willRedirect()) {
			$redirector->go();
			return;
		}
		/*otherwise, display the template*/
		$this->view->display('index.tpl');
	}
	
}
?>