<?php
/**
 *  Dashboards will have lots of things in common, that aren't suitable for inclusion
 * in Controller, so they should extend this instead if they want to use the functionality.
 *
 */
require_once LIB_ROOT.'spyc/spyc.php';
class DashboardController extends Controller {
	protected $dashboard_module;
	function __construct($module=null,$view) {
		if($module==null) {
			throw new Exception('No module passed to __construct');
		}
		parent::__construct($module,$view);
		$this->cache_key=EGS_USERNAME.EGS_COMPANY_ID.$module.'_dashboard';
		$this->dashboard_module=$module;
	}

	public function index() {
		$key=$this->cache_key;
		$dashboard = new Dashboard();
		if(!HAS_APC||false===$eglet_store=apc_fetch($key)) {
			$ao = AccessObject::Instance(EGS_USERNAME);
			$manifest=Spyc::YAMLLoad(EGLET_ROOT.'manifest.yml');
			$app_manifest=Spyc::YAMLLoad(EGLET_ROOT.'manifest.yml');
			$manifest = array_merge($manifest,$app_manifest);
			$man=array();
			$defaults=array();
			foreach($manifest as $definition) {
				$man[$definition['name']]=$definition;
				if(in_array($this->dashboard_module,$definition['modules'])&&isset($definition['default'])&&$definition['default']) {
					$defaults[]=$definition['name'];
				}
			}
			$prefs=UserPreferences::Instance(EGS_USERNAME);
			$eglets=$prefs->getPreferenceValue('dashboard_contents',$this->dashboard_module);
			if(!isset($eglets)) {
				$eglets=$defaults;
			}
			
			$eglet_store=array();	
			foreach($eglets as $name) {
				if(!isset($man[$name])) {
					continue;
				}
				$definition=$man[$name];
			
				//need to check access here in case someone has access from a module removed
				//TODO: should it also remove the eglet from their preferences list? It will go if they make any changes themselves, so maybe not important
				if(!$ao->hasPermissionAny($definition['modules'])) {
					continue;
				}
			
				//EGlets can specify a 'uses' classname which is used instead of their name (for very generic things)	
				$className=((isset($definition['uses']))?$definition['uses']:$definition['name']);
			
				//EGlets get the chance to 'suggest' which Renderer should be used with them
				//somehow, user-preferences will become involved here for graphs and such things
				$renderer = call_user_func(array($className,'getRenderer'));
				$info=array('classname'=>$className,'renderer'=>get_class($renderer));
			
				//eglets that aren't real classes (i.e. they specify 'uses') can also set a list of method=>argument(s) pairs that will be called
				if(isset($definition['call'])&&is_array($definition['call'])) {
					$info['call'] = $definition['call'];
				}
				$eglet_store[$definition['title']]=$info;
			}
			if(HAS_APC) {
				apc_store($key,$eglet_store);
			}
		}
		foreach($eglet_store as $title=>$info) {
			$classname=$info['classname'];
			$renderername = $info['renderer'];
			$eglet = new $classname(new $renderername);
			if(isset($info['call'])&&is_array($info['call'])) {
				foreach($info['call'] as $func=>$arg) {
					if(is_array($arg)) {
						call_user_func_array(array($eglet,$func),$arg);
					}
					else {
						call_user_func(array($eglet,$func),$arg);
					}
				}
			}
						$dashboard->addEGlet($title,$eglet);
		}

		$this->setTemplateName('dashboard');
		showtime('pre-pop');
		$dashboard->populate();
		$this->view->register('dashboard',$dashboard);
	}
	
	public function edit() {
		$prefs=&UserPreferences::instance();
		$ao = &AccessObject::Instance(EGS_USERNAME);
		$contents=$prefs->getPreferenceValue('dashboard_contents',$this->dashboard_module);
		$manifest=Spyc::YAMLLoad(LIB_ROOT.'eglets/manifest.yml');
		$app_manifest=Spyc::YAMLLoad(APP_ROOT.'eglets/manifest.yml');
		$manifest = array_merge($manifest,$app_manifest);
		//print_r($manifest);
		$available=array();
		$selected=array();
		$ordering=array();
		foreach($manifest as $definition) {
			//check permission:
			if(!$ao->hasPermissionAny($definition['modules'])) {
				continue;
			}
			//if the user has picked the EGlet previously, then it belongs in 'selected' (setting the index preserves the ordering)
			if(is_array($contents)&&in_array($definition['name'],$contents)) {
				$selected[array_search($definition['name'],$contents)]=array('title'=>$definition['title'],'name'=>$definition['name']);				
			}
			//if they haven't picked any EGlets, and the EGlet is marked as default for the current module then it's 'selected'			
			else if(in_array($this->dashboard_module,$definition['modules'])&&$contents===null&&isset($definition['default'])&&$definition['default']==true) {
				$selected[]=array('title'=>$definition['title'],'name'=>$definition['name']);	
			}
			//other EGlets belonging to the module get put in 'available':
				//if the current module is in the EGlet's list of module, then it's 'available'
				//if the current module is 'dashboard', and the EGlet has 'dashboard: true', then it's 'available'
			else if(in_array($this->dashboard_module,$definition['modules']) || ($this->dashboard_module=='dashboard'&&isset($definition['dashboard'])&&$definition['dashboard']==true)) {
				$available[$definition['name']]=$definition['title'];		
			}
		}
		//to preserve the ordering:
		ksort($selected);
		
		$this->view->set('selected',$selected);
		$this->view->set('available',$available);
		
		//same template for all modules
		$this->setTemplateName('edit_dashboard');
	}
	
	function save() {
		if(isset($this->_data['eglets'])&&count($this->_data['eglets'])>0) {
			$prefs=&UserPreferences::Instance(EGS_USERNAME);
			$prefs->setPreferenceValue('dashboard_contents',$this->dashboard_module,$this->_data['eglets']);
			$flash=Flash::Instance();
			$flash->addMessage('Dashboard preferences set');
			if(HAS_APC) {
				apc_delete($this->cache_key);
			}
		}
		sendTo('index','index',array($this->dashboard_module));
	}

	
	
}
?>
