<?php
class Tactile_RolesController extends Controller {
	
	/**
	 * @var Omelette_Role
	 */
	protected $role;
	
	public function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('Role');
	}
	
	
	public function index() {
		$roles = new RoleCollection();
		$sh = new SearchHandler($roles,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('name','NOT LIKE','%//'.Omelette::getUserSpace()));
		parent::index($roles,$sh);
	}
	
	public function _new() {
		$user_model = DataObject::Construct('User');
		$all_users = $user_model->getAll();
		$this->view->set('all_users',$all_users);
	}
	
	public function edit() {
		if(!isset($this->_data['id']) || false=== $this->role->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID specified');
			sendTo(); 
		}
		$this->view->set('selected_users',$this->role->getUsernames());
		$this->setTemplateName('new');
		$this->_new();
	}
	
	public function save() {
		$db = DB::Instance();
		$db->StartTrans();
		$saver = new ModelSaver();
		$errors = array();
		$role = $saver->save($this->_data['Role'],'Role',$errors);
		if($role!==false) {
			$success=$role->setUsernames($this->_data['Role']['users']);
			if($success) {
				$db->CompleteTrans();
				sendTo('groups');
				return;
			}
		}
		$this->saveData();
		if(isset($this->_data['id'])) {
			sendTo('roles','edit','admin',array('id'=>$this->_data['id']));
			return;
		}
		sendTo('roles','new','admin');
	}
	
	function view() {
		$this->role->load($this->_data['id']);
	}
	
}
?>