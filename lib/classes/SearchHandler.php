<?php
class SearchHandler {
	private $model;
	private $offset;
	private $orderby;
	private $page;
	private $use_session;
	private $use_system_company;	
	private $_customFields = array();
	private $_dbJoins = array();
	public $fields=array();
	public $constraints;
	public $orderdir;
	public $perpage;
	
	
	/**
	 * The DOC the SH works on
	 *
	 * @var DataObjectCollection
	 */
	protected $collection;
	
	public static $perpage_default = 10;
	public static $perpage_minimum = 1;
	public static $perpage_maximum = 100;
	
	public function __construct(DataObjectCollection $collection,$use_session=true,$use_system_company=true) {
		$tablename=$collection->getViewName();
		$this->tablename=$tablename;
		$this->use_session=$use_session;
		$this->constraints = new ConstraintChain();
		$this->use_system_company=$use_system_company;
		if($this->use_session&&!empty($_SESSION['searches'][$tablename])) {
			$model = $collection->getModel();
			foreach($_SESSION['searches'][$tablename] as $key=>$val) {
				if ($key == 'constraints') {
					$validcons = new ConstraintChain();
					foreach ($val as $c)
						if ($c['constraint'] instanceof Constraint)
						 	if ($model->isField($c['constraint']->fieldname))
						 		$validcons->add($c['constraint'],$c['type']);
					$this->$key = $validcons;
				}
				elseif ($key == 'fields') {
					$validfields = array();
					foreach ($val as $field=>$other) {
						if ($model->isField($field))
							$validfields[$field] = $other;
					}
					$this->$key = $validfields;
				}
				else
					$this->$key=$val;
			}
		}
		$this->collection=$collection;
	}
	
	public function save() {
		$array=array();
		$array['fields']=$this->fields;
		$this->constraints->removeByField('usercompanyid');
		$this->constraints->removeByField('usernameaccess');
		$array['constraints']=$this->constraints;
		$array['orderby']=$this->orderby;
		$array['orderdir']=$this->orderdir;
		$array['perpage']=$this->perpage;
		$array['page']=$this->page;
		if($this->use_session) {
			$_SESSION['searches'][$this->tablename]=$array;
		}
	}
	
	public function __get($var) {
		return $this->$var;
	}
	
	public function extract($nopaging=false) {
		$this->extractFields();
		$this->extractConstraints();
		$this->extractOrdering();
		if (!$nopaging) {
			$this->extractPaging();
		}
	}

	public function extractPaging($page=1, $perpage=null) {
		$this->perpage = self::$perpage_default;
		if (!empty($perpage)) {
			$this->perpage = ($perpage >= self::$perpage_minimum && $perpage <= self::$perpage_maximum) ?
				$perpage : self::$perpage_default;
		}
		if (isset($_GET['limit'])) {
			$limit = (int)$_GET['limit'];
			$this->perpage = ($limit >= self::$perpage_minimum && $limit <= self::$perpage_maximum) ?
				$limit : self::$perpage_default;
		}
		if (isset($_GET['page'])) {
			$this->page = (int)$_GET['page'];
		} else {
			$this->page = (int)$page;
		}
		$this->offset = ($this->page - 1) * $this->perpage;
	}

	public function extractFields() {
		//the model has the default fields, so lets get them
		$model = $this->collection->getModel();
		$fields = $model->getDisplayFieldNames();
		foreach ($fields as $field) {
			if ($model->isField($field.'_id'))
				$fields[$field.'_id'] = $field.'_id';
		}
		//we want to make sure we have the identifier though
		/*if(!$model->isField($model->getIdentifier()))
			$fields=array('_identifier'=>'Description')+$fields;
		else
			$fields=array($model->getIdentifier()=>$model->getTag($model->getIdentifier()))+$fields;*/

		//and we want 'id' as well
		$fields=array($model->idField=>$model->getTag($model->idField))+$fields;
		$this->fields=$fields;
	}
	
	public function setFields($fields) {
		$this->fields=array();
		if($fields=='*') {
			$model_fields = $this->collection->getModel()->getFields();
			foreach($model_fields as $name=>$field) {
				$this->fields[$name] = $field->tag;
			}
		}
		else {
			foreach($fields as $field) {
				$this->fields[$field]=prettify($field);
			}
		}
	}
	
	private function extractConstraints() {
		//check for 'active' searches
		if(isset($_REQUEST['clearsearch']))
		{
			$this->constraints = new ConstraintChain();
		}
		if(isset($_POST['search'])) {
			foreach($_POST['search'] as $fieldname=>$search) {
				$this->constraints[$fieldname][]=ConstraintFactory::Factory($this->model->getField($fieldname),$search);
			}
		}
		if(isset($_POST['quicksearch']) && isset($_POST['quicksearchfield']))
		{
			if($_POST['submit']=='Go')
			{
				$this->constraints = new ConstraintChain();
			}
			$model = new $this->collection->_doname;
			$searchfield = strtolower($_POST['quicksearchfield']);
			$search = '%'.strtolower($_POST['quicksearch']).'%';
			$cc = new ConstraintChain();
			if ($model->getField($searchfield)->type == 'bool') {
				switch(strtolower($_POST['quicksearch'])) {
					case "yes":
					case "true":
					case "y":
					case "t":
						$cc->add(new Constraint($searchfield,'=','true'));
						break;
					default:
						$cc->add(new Constraint($searchfield,'=','false'));
				}
			}
			else
				$cc->add(new Constraint('lower('.$searchfield.')','LIKE',$search, 'user'));
			$this->addConstraintChain($cc);		
		}

		//clearing the search will revert to a save, if one exists
		if($this->use_session&&isset($_POST['clearsearch'])) {
			if(isset($_SESSION['preferences']['savedsearches'][$this->tablename])) {
				$this->constraints=$_SESSION['preferences']['savedsearches'][$this->tablename];
			}
			else {
				$this->constraints=new ConstraintChain();
			}
		}
		//saving sets the current search to be called upon in the future
		if($this->use_session&&isset($_POST['savesearch'])) {
			$_SESSION['preferences']['savedsearches'][$this->tablename]=$this->constraints;
		}
		if($this->use_session&&count($this->constraints)==0&&isset($_SESSION['preferences']['savedsearches'][$this->tablename]))
			$this->constraints=$_SESSION['preferences']['savedsearches'][$this->tablename];
		//if usercompanyid is a field, then it's always a constraint
		$model=$this->collection->getModel();
		if($model->isField('usercompanyid') && $this->use_system_company) {
			$this->addConstraint(new Constraint('usercompanyid','=',EGS::getCompanyId()));
		}

	}

	public function extractOrdering() {
		$model=$this->collection->getModel();
		if(isset($_GET['orderby'])&&$model->isField($_GET['orderby'])) {
			if($_GET['orderby']!=$this->orderby) {
				$this->orderby=$_GET['orderby'];
				$this->orderdir='ASC';
			}
			else {
				$this->orderdir=($this->orderdir=='ASC')?'DESC':'ASC';
			}
		}
		else {
			if($this->orderby==null) {
				$this->orderby=$model->orderby;
				$this->orderdir=$model->orderdir;
			}
			if($this->orderdir==null) {
				$this->orderdir='ASC';
			}
		}

	}

	private function checkFields() {
		foreach($this->fields as $fieldname=>$field) {
			if(!$this->model->isField($fieldname))
				$this->model->addField($fieldname, $field);
		}
		return true;
	}

	public function addConstraint($constraint,$type='AND') {
		$this->constraints->add($constraint,$type);
	}
	public function addConstraintChain($cc,$type='AND') {
		$this->constraints->add($cc,$type);
	}
	function setOrderby($orderby,$orderdir='ASC') {
		$this->orderby=$orderby;
		$this->orderdir=$orderdir;
	}
	
	public function getOrderBy() {
		return $this->orderby;
	}
	
	/**
	 * Specify the 'limit' part, and optionally the 'offset', for the search-query
	 *
	 * @param int $limit
	 * @param int optional $offset
	 */
	function setLimit($limit,$offset=0) {
		$this->perpage=$limit;
		$this->offset=$offset;
	}
	
	public function setCustomFields($idTypes) {
		$this->_customFields = $idTypes;
	}
	
	public function getCustomFields() {
		return $this->_customFields;
	}
	
	public function setDbJoins($dbJoins) {
		$this->_dbJoins = $dbJoins;
	}
	
	public function getDbJoins() {
		return $this->_dbJoins;
	}
}
