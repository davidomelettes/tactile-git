<?php

abstract class Controller {

	private   $_action;
	protected $_uses=array();
	public static $accessControlled=false;
	public $_data=array();
	public $_templateName;
	public $modeltype;
	protected $saved_model=false;
	protected $relatedFields;
	protected $sidebar;
	protected $mixins=array();
	protected $is_post = false;
	
	/**
	 * A Zend_Log instance
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	
	/**
	 * Constructor
	 *
	 * Constructs a controller based on module name
	 * @param string $module module name
	 * @param string $action action name
	 * @todo shouldn't need $module *and* $modules
	 */
	 public function __construct($module=null,$view) {
		$this->view = $view;
		$this->view->set('controller',strtolower(str_replace('Controller','',get_class($this))));
		
		$this->view->set('perpage_options', array(10, 30, 50, 100));
		$this->view->set('perpage', SearchHandler::$perpage_default);
		
		global $modules;
		if(is_string($module)) {
			$module = array($module);
		}

		if(!empty($modules)) {
			$this->_modules = $modules;
		}
		else {
			 $this->_modules= $module;
		}

		if(!empty($this->_modules) && is_array($this->_modules))
		{
			foreach($this->_modules as $mod)
			{
				$this->_modules_string .='/'.$mod ;
			}
		}
	}

	public function setView($view) {
		$this->view=$view;
	}

	/**
	 * Set template name
	 *
	 * Sets the template name based on an action name
	 * @param string $action action name
	 */
	public function setTemplateName($action) {
		$this->_templateName=$this->getTemplateName($action);
	}

	/**
	 * Get template name
	 *
	 * Returns the file-path of the template to be used for the current page.
	 * First, there is a check for a user-customised template
	 * If this isn't found, it looks in the standard location for templates
	 * @param string $action action name
	 * @param bool $mustexist
	 * @return string File path for template
	 */
	public function getTemplateName($action,$mustexist=true) {
		$cname = str_replace('Controller','',get_class($this));
		return $this->view->getTemplateName($this->_modules_string,$cname,$action);
	}


	/**
	 * Index
	 *
	 * Handles the logic behind overview pages
	 * called from extension-classes, which are left to handle actions and setting up the search fields
	 * @param DataObjectCollection $collection
	 */
	public function index(DataObjectCollection $collection,$sh=null) {
		showtime('start-controller-index');
		if ($sh == null) {
			$sh = new SearchHandler($collection);
			$sh->extract();
			showtime('sh-extracted');
		}
		if (isset($this->search)) {
			$cc = $this->search->toConstraintChain();
			$sh->addConstraintChain($cc);
		}
		
		$this->_rememberPage($sh);
		
		showtime('pre-load');
		$collection->load($sh);
		showtime('post-load');
		
		$this->view->set(strtolower($collection->getModelName().'s'), $collection);
		$this->view->set('num_pages', $collection->num_pages);
		$this->view->set('cur_page', $collection->cur_page);
		$this->view->set('num_records', $collection->num_records);
		$this->view->set('perpage_options', array(10, 30, 50, 100));
		//$this->view->set('perpage', SearchHandler::$perpage_default);
		
		if(isset($this->_data['json'])) {
			$this->view->set('echo',$collection->toJSON());			
		}
		showtime('end-controller-index');
	}
	
	public function _rememberPage($sh) {
		// Don't remember the last page we were on if we are using the JSON view
		$limit = 30;
		if (!$this->view->is_json) {
			// Remember the page limit
			if (empty($this->_data['limit'])) {
				// No limit specified in current URL, try to magic one up
				$limit = (int)Omelette_Magic::getValue('pagination_limit', EGS::getUsername(), $limit);
				$limit = ($limit > 100 ? 100 : $limit);
			} else {
				// Limit specified in current URL, store new value
				$limit = (int)$this->_data['limit'];
				$limit = ($limit > 100 ? 100 : $limit);
				Omelette_Magic::saveChoice('pagination_limit', $limit);
			}
			$this->view->set('perpage', $limit);
			
			// Grab the current URL
			$pagination_uri = empty($_SERVER['REQUEST_URI']) ? $_GET['url'] : $_SERVER['REQUEST_URI'];
			$pagination_uri = preg_replace('/\/?\?$/', '', preg_replace('/&?page=\d+/', '', preg_replace('/&?limit=\d+/', '', $pagination_uri)));
			
			$rp = RouteParser::Instance();
			$uri_key = 'pagination_' . $rp->Dispatch('controller') . '_uri';
			$page_key = preg_replace('/_uri$/', '_page', $uri_key);
			
			switch ($rp->Dispatch('controller')) {
				case 'emails':
				case 'organisations':
				case 'persons':
				case 'opportunitys':
				case 'activitys': {
					// Don't remember the last page we were on if specifying one
					if (empty($this->_data['page'])) {
						$page = 1;
						if ($pagination_uri === Omelette_Magic::getValue($uri_key)) {
							// The current view is the same as the last view we saw for this controller, so remember the page
							$page = Omelette_Magic::getValue($page_key, EGS::getUsername(), 1);
							
						} elseif ($rp->Dispatch('action') === 'index') {
							// We may have remembered a different action
							$restriction = Omelette_Magic::getValue($rp->Dispatch('controller') . '_index_restriction');
							if (!empty($restriction) && preg_match('/\/'.preg_quote($restriction).'/', Omelette_Magic::getValue($uri_key))) {
								// The remembered action matches the last view we saw for this controller, so remember the page
								$page = Omelette_Magic::getValue($page_key, EGS::getUsername(), 1);
							}
							
						} else {
							// This is a different view to the last one we accessed, so land on page 1 and remember for later
							Omelette_Magic::saveChoice($uri_key, $pagination_uri);
							Omelette_Magic::saveChoice($page_key, $page);
						}
					} else {
						// Page parameter was specified, so save new page
						Omelette_Magic::saveChoice($uri_key, $pagination_uri);
						Omelette_Magic::saveChoice($page_key, (int)$this->_data['page']);
					}
					$sh->extractPaging($page, $limit);
					break;
				}
			}
		}
	}

	/**
	 * Save
	 *
	 * Passes data to the save function of a model to save an object
	 * @param string $modelName name of model to be saved
	 * @param array $dataIn data to save
	 * @return bool true on success, false on failure
	 */
	public function save($modelName, $dataIn=array(),&$errors=array()) {
		$db=&DB::Instance();
		$db->StartTrans();
		$flash=Flash::Instance();
		if(!empty($dataIn)) {
			$data = $dataIn;
		}
		else {
			$data = $this->_data[$modelName];
		}
		$model= call_user_func(array($modelName, "Factory"), $data,$errors,$modelName);
		if($model!==false) {
			$model->save();
			$this->saved_model=$model;
			$aliases = $model->aliases;
			foreach($aliases as $aliasname=>$alias) {
				if(isset($data[$aliasname]) && is_array($data[$aliasname])) {
					if (isset($alias['requiredField'])) {
						if (empty($data[$aliasname][$alias['requiredField']])) {
							continue;
						}
					}
					$aliasdata = $data[$aliasname];
					$aliasdata[strtolower($model->get_name()).'_id'] = $model->{$model->idField};
					$aliasmodel= DataObject::Factory($aliasdata,$errors,$alias['modelName']);
					if ($aliasmodel!==false) {
						$aliasmodel->save();
					}
					else {
						//for debug
					}
				}
				else if(isset($data[$aliasname])){
					$aliasdata=array();
					$aliasdata[strtolower($model->get_name()).'_id'] = $model->{$model->idField};
					foreach($alias['constraints'] as $constraint) {
						$aliasdata[$constraint->fieldname]=$constraint->value;
					}
					$aliasdata[$alias['requiredField']]=$data[$aliasname];
					$aliasmodel=call_user_func(array($alias['modelName'],"Factory"),$aliasdata,$errors,$alias['modelName']);
					if($aliasmodel!==false) {
						$aliasmodel->save();
					}
				}
			}
			$this->_data['id'] = $model->{$model->idField};
			$this->$modelName=$model;
			$success=$db->CompleteTrans();
			if($success)
				$flash->addMessage($modelName.' saved successfully');
			if (isset($this->_data['saveAnother'])) {
				$res = array();
				if (isset($_SERVER['HTTP_REFERER'])) {
					$refs = $_SERVER['HTTP_REFERER'];
					$refs = explode('?',$refs);
					$refs = explode('&',$refs[1]);
					foreach ($refs as $ref) {
						$refsplit = explode('=',$ref);
						$res[$refsplit[0]] = $refsplit[1];			
					}
					unset($res['controller']);
					unset($res['action']);
					unset($res['module']);
				}
				sendTo($_GET['controller'],'new',array($_GET['module']),$res);
			}
			return $success;
		}
		else {
			$flash->addErrors($errors, strtolower($modelName).'_');
			$db->CompleteTrans();
			return false;

		}


	}

	protected function saveFiles($key,Array $filenames) {
		$file = new File();
		$errors=array();
		$data = $_FILES[$key];
		$fields=$file->getFields();
		foreach($filenames as $name) {
			$newdata=array();
			if(!empty($data['name'][$name])) {
				foreach($fields as $fieldname=>$field) {
					if(isset($data[$fieldname][$name]))
						$newdata[$fieldname]=$data[$fieldname][$name];
				}
				$newdata['tmp_name']=$data['tmp_name'][$name];
				$newdata['note']='Image attached to '.$key;
				$$name=File::Factory($newdata,$errors,new File());
				if($$name instanceof File)
					$$name->save();
				$this->_data[$key][$name]=$$name->id;
			}
		}


	}

	/**
	 * Save a collection
	 *
	 * Like save but for multiple records
	 * @param string $modelName name of model to be saved
	 * @param array $dataIn data to save
	 * @return bool true on success, false on failure
	 */
	public function saveCollection($modelName, $datain=array())
	{
		$errors=array();
		$flash=Flash::Instance();
		if(isset($datain) && !empty($datain))
		{
			$data = $datain;
		}
		else
		{
			$data = $this->_data[$modelName];
		}

		$collection = call_user_func(array($modelName.'Collection', "Factory"), $data,$errors,$modelName);
		if($collection)
		{
			if($collection->save())
			{
				$flash->addMessage('Collection saved successfully');
				return true;
			}
			else
			{
				$flash->addError('Unable to save collection');
				return false;
			}

		}
		else
		{
			$flash->addErrors($errors, strtolower($modelName).'_');
			return false;
		}
	}



	/**
	 * new
	 *
	 * Sets template models used in a controller for a new record stored in view
	 */
	public function _new() {
		$flash  = Flash::Instance();
		if(!$flash->hasErrors()) {
			$_SESSION['_controller_data']=array();
		}
		$models=array();
		foreach($this->_uses as $model) {
			$models[$model->get_name()]=$model;
		}
		if(isset($_SESSION['formdata'])) {
			$_POST=$_SESSION['formdata'];
			unset($_SESSION['formdata']);
		}
		$this->view->set('models',$models);
		if (isset($this->_data['person_id'])) {
			$person = new Person();
			$person->load($this->_data['person_id']);
			$this->_data['organisation_id'] = $person->organisation_id;
		}		
	}


	/**
	 * edit
	 *
	 * Similar to new, but with data
	 */
	public function edit() {
		$this->_new();
		$this->_templateName=$this->getTemplateName('new');

		if(isset($this->_data)){
			foreach($this->_uses as $modeltype) {
				$loaded = false;
				$model = $modeltype->get_name();
				if(isset($this->_data['id'])) {
					$id=$this->_data['id'];
					$loaded = true;
				}
				else if (isset($this->_data[$model]['id'])) {
					$id=$this->_data[$model]['id'];
					$loaded = true;
				}
				if($loaded) {
					$object=$this->_uses[$model];
					$object->load($id);
				}
			}
		}

	}

	/**
	 * delete
	 *
	 * Instructs a model to delete a record based on id
	 */
	public function delete($modelName) {
		$flash=Flash::Instance();
		$id = $this->_data['id'];
		$model = new $modelName();
		if ($model->delete($id)) {
			$flash->addMessage($modelName.' deleted successfully');
			return true;
		}
		else {
			$errors = array($modelName.' not deleted successfully');
			$flash->addErrors($errors, strtolower($modelName).'_');
			return false;
		}
	}



	public function setData($array,$subarray=null) {
		if (is_array($array)&&!isset($subarray)) {
			$this->_data=$this->_data+$array;
		}
		else {
			$this->_data[$subarray] = array();
			$this->_data[$subarray] = $array;
		}
	}
	
	public function setIsPost(){
		$this->is_post = true;
	}

	protected function uses($model) {
		if(is_string($model)) {
			$model = DataObject::Construct($model);
		}
		if(!$model instanceof DataObject) {
			throw new Exception('Tried to use a non-existant model: '.$model);
		}
		$this->_uses[$model->get_name()] = $model;
		$this->{strtolower($model->get_name())} = $model;
		$this->modeltype = $model->get_name();
	}

	/**
	 * @todo change name
	 */
	function assignModels() {
		$jsos=array();
		foreach($this->_uses as $name=>$model) {
			$this->view->set($name,$model);
		//	$jsos[$name]=$model->toJSON();
		}
		$this->view->set('page_title',$this->getPageName());
		//$this->view->set('jsos',$jsos);
		$this->view->set('controller_data',$this->_data);
		$this->view->set('templateName',$this->_templateName);
		if(isset($this->search)) {
			$this->view->set('search',$this->search);
		}
	}

	protected function getPageName($base=null,$action=null) {
		$inflector = new Inflector();
		if($base==null) {
			$base = str_replace('Controller','',get_class($this));
		}
		switch($this->_action) {
			case 'new':
			case '_new';
				$name= 'new_'.$inflector->singularize($base);
				break;
			case 'edit':
				$name= 'edit_'.$inflector->singularize($base);
				break;
			case 'index':
				$name = 'viewing_'.$inflector->pluralize($inflector->singularize($base));
				break;
			default:
				$name = $this->_action;
		}
		return $name;
	}


	/**
	 * Set dependency injector
	 */
	public function setInjector(&$injector) {
		$this->_injector=$injector;
	}

	/**
	* fills a collection of the specified model type with the fields specified,
	* also gives correct click controller, action and edit handlers so
	* that smarty datatable will work correctly.
	* finally outputs specified smarty variable as the collection for
	* datatable to use
	* used for alternate controller to display specific contents of a different
	* controller
	*/

	public function fillCollection($modelname, $fields, $constraints, $clickcontroller, $clickaction, $editclickaction, $deletecontroller, $smartyname, $tablename=null, $deleteaction=null, $newtext=null, $limit=null, $orderdir=null, $offset=null) {
		$collectionname = $modelname.'Collection';
		$collection = new $collectionname(new $modelname());
		$sh = new SearchHandler($collection);
		$sh->fields = $fields;
		$sh->constraints = $constraints;
		$sh->extractOrdering();
		$sh->extractPaging();
		$sh->perpage = 900000;
		if (isset($orderdir))
			$sh->orderdir = $orderdir;
		if (isset($limit) && isset($offset))
			$sh->setLimit($limit, $offset);
		if (isset($tablename))
			$collection->_tablename = $tablename;
		$collection->load($sh);
		$collection->clickcontroller = $clickcontroller;
		$collection->clickaction = $clickaction;
		$collection->editclickaction = $editclickaction;
		$collection->deletecontroller = $deletecontroller;
		if (isset($deleteaction))
			$collection->deleteclickaction = $deleteaction;
		if (isset($newtext))
			$collection->newtext = $newtext;
		$this->view->set($smartyname,$collection);
	}

	/**
	 * This is here so that viewXXXXXX() can be called for anything that you think you might want to see
	 * Now also handles mixed-in things. @see mixes()
	 */
	public function __call($method,$args) {
		if(isset($this->mixins[$method])) {
			$mixin = $this->mixins[$method]['class'];
			$args = $this->mixins[$method]['args'];
			if($this->mixins[$method]['call']!==null) {
				$method=$this->mixins[$method]['call'];
			}
			$code=$mixin.'::'.$method.'('.var_export($args,true).');';
			eval($code);
			return true;
		}
		if(strtolower(substr($method,0,4))=='view') {
			$view_name=substr($method,4);
			$this->viewRelated($view_name);
			return true;
		}
		if(substr($method,0,3)=='Get') {
			if (isset($this->_data['ajax'])) {
				$id = $this->_data['id'];
				$inflector = new Inflector();
				$property = $inflector->pluralize(strtolower(substr($method,3)));
				$model = new $this->modeltype;
				$model->load($id);
				$hasMany = $model->getHasMany();
				if (isset($hasMany[$property])) {
					$collection = $model->$property;
					$this->_templateName = 'get';
					$json = json_encode($collection->getAssoc());
					echo $json;
					exit;
				}
				else {
					$property = substr($method,3);
					$value = $model->$property;
					$newModel = substr($property,0,-3);
					$newModel = new $newModel;
					$newModel->load($value);
					unset($value);
					$value[$newModel->{$newModel->idField}]  = $newModel->{$newModel->identifierField};
					$json = json_encode($value);
					echo $json;
					exit;
				}
			}
		}
		$this->index();
	}
	/**
	 * Allows common functionality to be handled in a single place, by mapping controller-actions to a class/function/arguments combination
	 * @param $method string the name of the method to be handled
	 * @param $classname string the name of the class that will do the handling
	 * @param $args array an assoc-array of any arguments to be passed to the handling function
	 *[ @param $call string the name of the function to actually call, if different from the mapped action ]
	 * 
	 * e.g. $this->mixes('opportunities','RelatedItemsLoader',array('opportunities','Company'),'get_related');
	 */
	function mixes($method,$classname,$args,$call=null) {
		$this->mixins[$method]=array('class'=>$classname,'args'=>$args,'call'=>$call);
	}
	
	/**
	 * Saves form-data to the session
	 */
	function saveData() {
		$_SESSION['_controller_data']=$this->_data;
	}
	
	function restoreData() {
		if(isset($_SESSION['_controller_data'])) {
			$tmp = $_SESSION['_controller_data'];
			//unset($_SESSION['_controller_data']);
			return $tmp;
		}
		return false;		
	}
	
	protected function viewRelated($name) {
		$collectionName = $this->modeltype.'Collection';
		$sh = new SearchHandler(new $collectionName,false);
		$sh->extract();
		$related_collection = new $collectionName;
		$qstring=$_GET;
		unset($qstring['module']);
		unset($qstring['page']);
		unset($qstring['orderby']);
		unset($qstring['action']);
		unset($qstring['controller']);
		unset($qstring['get'.$name]);
		unset($qstring['ajax']);
		unset($qstring['id']);
		$sh->constraints = new ConstraintChain();
		$model = new $this->modeltype;
		if ($model->isField('usercompanyid')) {
			$sh->addConstraint(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		}
		$link=array();
		
		$link['module']=$_GET['module'];
		$link['controller']=str_replace('Controller','',get_class($this));
		$link['action']='view'.$name;
		foreach($qstring as $key=>$value) {
			if ($key == 'type') {
				$value = ucfirst($value);
			}
			$sh->addConstraint(new Constraint($key,'=',$value));
			$link[$key]=$value;
		}
		unset($sh->fields[$name]);
		unset($sh->fields[$name.'_id']);
		$related_collection->load($sh);
		$this->_templateName=$this->getTemplateName('view_related');
		$c_action=(isset($this->related[$name]['clickaction'])?$this->related[$name]['clickaction']:'view');
		if(isset($this->related[$name]['include_id'])) {
			$c_action.='&'.$name.'_id='.$_GET[$name.'_id'];
		}
		$this->view->set('clickaction',$c_action);
		$this->view->set('related_collection',$related_collection);
		$this->view->set('num_pages',$related_collection->num_pages);
		$this->view->set('cur_page',$related_collection->cur_page);
		$this->view->set('paging_link',$link);
		$this->view->set('no_ordering',true);
		if($this->modeltype=='Project' || $this->modeltype == 'WebpageRevision' || $this->modeltype=='OrderItem')
			$this->view->set('no_delete',true);
	}
	
	function setLogger(Zend_Log $logger) {
		$this->logger = $logger;
	}

}
?>
