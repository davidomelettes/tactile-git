<?php
class IntranetPage extends DataObject {

	function __construct() {
		parent::__construct('intranet_pages');
		$this->idField='id';
		
		
 		$this->belongsTo('IntranetPageType', 'type_id', 'type');
 		$this->belongsTo('IntranetSection', 'intranetsection_id', 'section');
 		$this->actsAsTree('parent_id');
		$this->hasMany('IntranetPageRevision','revisions');
		$this->actsAsTree();		
		$sh = new SearchHandler(new IntranetPageRevisionCollection,FALSE);
		$sh->setOrderby('revision','desc');
		$this->setAlias('revision','IntranetPageRevision',$sh,'title');
		$this->hasMany('IntranetPageFile','files');
	}

	public static function Factory($data,&$errors=array(),$do_name=null) {
		//first we get an instance of the desired class
		if(!($do_name instanceof DataObject))
			$do=new $do_name;
		else
			$do=$do_name;
		
		//then get the fields and then loop their validators
		$do_fields=$do->getFields();
		$mode = "NEW";
		foreach ($data as $key=>$value)
			if (!is_array($value))
				$data[$key] = trim($value);
		//if editing, assign current values to $data where fields are empty
		if($do->idField!=$do->getIdentifier()&&!empty($data[$do->idField])) {
			$mode = "EDIT";
			$current = $do->load($data[$do->idField]);
			if($current===false) {
				$do = new $do_name;
			}
			else {
				$maintain=array('created','owner');
				foreach($maintain as $fieldname) {
					if($do->isField($fieldname)) {
						$field=$do->getField($fieldname);
						$field->ignoreField=true;
					}
				}
			}
		}
		$db = &DB::Instance();
		foreach($do_fields as $name=>$field) {
			if($field->ignoreField) {
				continue;
			}
			if($field->type=='oid') {
				$data[$name]=0;
			}
			if($field->type=='numeric'&&isset($data[$name])&&$data[$name]==='0') {
				$data[$name]=0;
			}
			if (empty($data[$name])&&!(isset($data[$name])&&$data[$name]===0)) {
				if($field->type=='bool'&&(!isset($data[$name])||$data[$name]!==true)) {
					$data[$name]='false';
				}
				$test=$do->autoHandle($name);
				
				if($test!==false) {
					$data[$name]=$test;
				}
			}
			if($field->type=='varchar' && isset($data[$name])&& is_numeric($data[$name])) {
				$data[$name] = $db->qstr($data[$name]);
			}
			
			if($mode =="EDIT" && isset($data[$name]) && $do->isNotEditable($name)) {
				unset($data[$name]);
				continue;
			}
			
			if(isset($data[$name])) {
				$do->$name=$field->test($data[$name],$errors);
			}
			if(!isset($data[$name]) && $field->has_default == 1) {
				$do->$name = $field->default_value;
			}
		}

		$do->test($errors);

		//then test the model as a whole
		if(count($errors)==0) {
			return $do;
		}
		return false;
	}	
	
	public function load($id) {
		$result=parent::load($id);
		if($result===false) {
			return false;
		}
		if ($this->owner != EGS_USERNAME) {
			// We're not the owner, are we /really/ allowed to see this page?
			$access = new IntranetPageAccessCollection();
			$sh = new SearchHandler($access, false);
			$sh->AddConstraint(new Constraint('intranetpage_id', '=', $id));
			$sh->AddConstraint(new Constraint('username', '=', EGS_USERNAME));
			$sh->AddConstraint(new Constraint('read', '=', 't'));
			$access->load($sh);

			$applicableRoles = $access->getContents();

			if (count($applicableRoles) == 0) {
				return false;
			}
		}
		return $this;
	}
	
	function getAccess() {
		if ($this->owner != EGS_USERNAME && !isModuleAdmin()) {
			// We're not the owner, are we /really/ allowed to see this page?
			$access = new IntranetPageAccessCollection();
			$sh = new SearchHandler($access, false);
			$sh->AddConstraint(new Constraint('intranetpage_id', '=', $this->id));
			$sh->AddConstraint(new Constraint('username', '=', EGS_USERNAME));
			$sh->AddConstraint(new Constraint('edit', '=', 't'));
			$access->load($sh);

			$applicableRoles = $access->getContents();

			if (count($applicableRoles) == 0) {
				$access = new IntranetPageAccessCollection();
				$sh = new SearchHandler($access, false);
				$sh->AddConstraint(new Constraint('intranetpage_id', '=', $this->id));
				$sh->AddConstraint(new Constraint('username', '=', EGS_USERNAME));
				$sh->AddConstraint(new Constraint('read', '=', 't'));
				$access->load($sh);
				$ar = $access->getContents();
				if (count($ar) == 0) {
					return false;
				}
				return 'read';
			}
		}
		return 'edit';
	}
	
	function getSubPages() {
		$pages = new IntranetPageCollection();
		$sh=new SearchHandler($pages,false);
		$sh->addConstraint(new Constraint('parent_id','=',$this->id));
		$sh->addConstraint(new Constraint('visible','=','true'));
		$sh->setOrderby('menuorder');
		$pages->load($sh);
		$pages_array=array();
		foreach($pages as $page) {
			$pages_array[self::buildURL($page)]=$page->revision->title;
			
		}
		return $pages_array;
	}
	
	
	public static function buildURL($page) {
		if(!$page instanceof IntranetPage) {
			$id=$page;
			$page = new IntranetPage();
			$page->load($id);			
		}
		$db=DB::Instance();
		$url = '/'.strtolower($page->name);
		if($page->parent_id) {
			$url=self::buildURL($page->parent_id).$url;			
		}
		else {
			$url = '/'.strtolower($page->section).$url;			
		}
		return $url;
	}
	
}
?>
