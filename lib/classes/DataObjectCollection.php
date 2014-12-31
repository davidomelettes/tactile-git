<?php
class DataObjectCollection implements Iterator, Countable{
	public $_doname;
	protected $_dataobjects=array();
	protected $_pointer=0;
	protected $_fields;
	protected $_identifierField="description";
	
	/**
	 * The model used for operations
	 * @access protected
	 * @var DataObject
	 */
	protected $_templateobject = null;
	protected $data;
	public $_tablename;

	public $orderby=null;
	public $direction='ASC';
	public $limit=10;
	public $page=1;
	public $cur_page = 1;
	public $num_pages = 1;
	public $pages=1;
	public $offset = 0;
	public $records = 0;
	public $search;
	public $searchField;
	public $searchString='';
	public $headings=array();
	public $clickcontroller='';
	public $clickaction='';
	public $editclickaction='';
	public $deletecontroller='';
	public $deleteclickaction='';
	public $newtext='';
	public $sh = null;
	public $query='';
	function __construct($do) {

		if(is_string($do)) {
			$this->_doname=$do;
			$temp=DataObject::Construct($do);
		}
		else {
			$temp =  $do;
			$this->_doname = $temp->get_name();
		}

		$this->_templateobject = $temp;
		$this->_fields=$temp->getDisplayFields();
		$this->_tablename=$temp->getViewName();
		$this->idField=$temp->idField;
		$this->orderby = $temp->getDefaultOrderby();
		$this->setParams();
		unset($temp);

	}

	function add($do) {
		if (is_array($do)) {
			foreach($do as $object) {
				$this->add($object);
			}
			return;
		}
		$this->_dataobjects[] = $do;
	}

	function remove($index) {
		unset($this->_dataobjects[$index]);
		$this->_dataobjects = array_values($this->_dataobjects);
	}

	function getViewName() {
		return $this->_tablename;
	}

	protected function _load($sh,$qb,$c_query=null) {
		$db = DB::Instance();
		if ($sh instanceof SearchHandler) {
			$this->_fields=$sh->fields;
			$query=$qb->select($sh->fields)
			->from($this->_tablename)
			->where($sh->constraints)
			->orderby($sh->orderby,$sh->orderdir)
			->limit($sh->perpage,$sh->offset)->__toString();
			$this->query=$query;
			$c_query=$qb->countQuery();
			$perpage=$sh->perpage;
			$c_page=$sh->page;
		}
		else {
			$query=$sh;
			$perpage=0;
			$c_page=1;
		}
		$num_records=$db->GetOne($c_query);
		$this->num_records = $num_records;
		$this->per_page = $perpage;
		if($num_records===false) {
			throw new Exception($db->ErrorMsg());
		}
		$this->num_pages=ceil($num_records/max(1,$perpage));
		$this->cur_page=$c_page;
		//no need to do anything else if there aren't any rows!
		if($num_records>0) {
			$rows=$db->GetAssoc($query);
			if($rows===false) {
				throw new Exception("DataObjectCollection load failed: ".$query.$db->ErrorMsg());
			}
			if($sh instanceof SearchHandler) {
				$sh->save();
			}
			foreach($rows as $id=>$row) {
				$do = clone $this->_templateobject;

				$row[$do->idField] = $id;
				$do->_data=$row;
				$do->load($id);

				$this->_dataobjects[]=$this->copy($do);
				$this->data[]=$row;
			}
		}
	}

	/**
	 * Prepares the DOC for loading
	 * - understands 'usernameaccess' i overviews
	 * - understands the 'deleted' flag
	 * @param SearchHandler $sh
	 * @param string optional $c_query
	 */
	function load($sh,$c_query=null) {
		$db=DB::Instance();
		$qb=new QueryBuilder($db,$this->_templateobject);
		if($sh instanceof SearchHandler) {
			if ($this->_templateobject->isAccessControlled()) {

				if(isModuleAdmin()) {
					$qb->setDistinct();
				}
				else {
					$cc = new ConstraintChain();
					$cc->add(new Constraint('usernameaccess', '=', EGS_USERNAME));
					$cc->add(new Constraint('owner','=',EGS_USERNAME),'OR');
					$sh->addConstraintChain($cc);
					$qb->setDistinct();
				}
			}
			if($this->_templateobject->isField('deleted')) {
				$sh->addConstraint(new Constraint('deleted','=',false));
			}
			$this->sh = $sh;
		}
		$this->_load($sh,$qb,$c_query);
	}

	/**
	 * Returns one or all of the DataObjects that make up the collection
	 * - if an index is given, then just that DO is returned, otherwise an array of all of them
	 * - returns false for an invalid index
	 * 
	 * @param Int $index
	 * @return DataObject|Boolean
	 */
	function getContents($index=null) {
		if($index===null) {
			return $this->_dataobjects;
		}
		if(!isset($this->_dataobjects[$index])) {
			return false;
		}
		return $this->_dataobjects[$index];

	}


	/*
	 * Post function should be used to post variables from a
	 * post array and to generate a collection.
	 *
	 * @todo Change the name of this function
	 */
	static function Factory($post,&$errors=array(),$modelName) {
		$collectionName = $modelName.'Collection';

		$do = new $collectionName();
		$rows = $do->joinArray($post);
		if(empty($rows)) {
			return false;
		}
		foreach($rows as $row) {
			$model= call_user_func(array($modelName, "Factory"), $row,$errors,$modelName);
			if(is_a($model, $modelName)) {
				$do->_dataobjects[]=$model;
			}
			else {
				return false;
			}
		}

		return $do;
	}





	function save() {
		$db=DB::Instance();
		$db->StartTrans();
		$fail = false;
		foreach($this->_dataobjects as $ob) {
			if(!$ob->save()) {
				$fail = true;
			}
		}

		if($fail) {
			$db->FailTrans();
			return false;
		}
		$db->CompleteTrans();
		return true;
	}

	function getHeadings() {
		foreach($this->_fields as $fieldname=>$tag) {
			if($fieldname == $this->idField && !($fieldname=='username')) {
				continue;
			}
			$this->headings[$fieldname]=$tag;
		}
		return $this->headings;
	}

	function getFields(){

		return $this->_fields;
	}

	/*
	 * Copy function
	 *
	 * Used to clone an object before storing it in an array.
	 * This is a bit of a fix because of the limitations of clone
	 * P.B.
	 */

	function copy($do) {
		$do = serialize($do);
		return unserialize($do);
	}

	/*
	 *Join Array Function
	 *
	 * Designed to take multiple arrays from post and concatinate them into
	 * a nice array. eg. $x[1,2,3], $y[4,5,6] => $z[key[1,4], key[2,5], key[3,6]
	 *
	 * I did a test with 10000 * 10^3 integers and it took > 0.07 seconds
	 * So probably not much of speed concern
	 * P.B.
	 */
	static function joinArray($post, $start=0) {
		$count = 0;

		foreach($post as $key=>$array) {
			if(is_array($array)) {
				$arrays[$key] = $array;
				$count = max($count,max(array_keys($array))+1);
			}
		}
		if($count < 1) {
			return false;
		}

		for($x = $start; $x < $count+$start; $x++) {
			$nothing_set=true;
			foreach($arrays as $key=>$array) {
				if (isset($array[$x])) {
					$nothing_set=false;
					$result[$x][$key] = $array[$x];
				}
				else {
					$result[$x][$key] = '';
				}
			}
			if($nothing_set) {
				unset($result[$x]);
			}
		}
		return $result;
	}


	function setParams(){
		if(isset($_GET['page']))$this->page = $_GET['page'];
		if(isset($_GET['limit']))$this->limit = $_GET['limit'];
		if($this->limit > 50)$this->limit = 50;
		if(isset($_GET['direction']))$this->direction = $_GET['direction'];
		if(isset($_GET['orderby']))$this->orderby = $_GET['orderby'];
		if(isset($_GET['search']))$this->search = $_GET['search'];
		if(isset($_GET['field']))$this->searchField = $_GET['field'];

		if(count($this->search) !== 0 && count($this->searchField)!==0) {
			$pointer = 0;
			foreach($this->search as $s) {
				$this->searchString.= ' LOWER('.$this->searchField[0].') LIKE '."'".strtolower($s)."%' AND";
				$pointer++;
			}
			$this->searchString = substr($this->searchString,0,(strlen($this->searchString)-3));
			$this->searchString = " WHERE".$this->searchString;
		}
		$this->offset = (($this->page * $this->limit)-$this->limit);
	}


	public function getAssoc($field=null) {
		$result = array();
		$contents = $this->getContents();
		if(!$contents || empty($contents))
		{
			return array();
		}
		$q=$this->query;

		if(!empty($q)) {
			$db=DB::Instance();
			$t_result=$db->GetAssoc($q);
			foreach($t_result as $id=>$data) {
				if($field!==null) {
					$result[$id]=$data[$field];
				}
				else {
					if(isset($data[$this->identifierField])) {
						$result[$id]=$data[$this->identifierField];
					}
					else {
						$result[$id]=current($data);
					}
				}
			}
		}
		else {
			foreach($contents as $model) {
				$result[$model->{$model->idField}] = $model->{$model->identifierField};
			}
		}
		return $result;
	}


	/**
	 * @param $doc1 DataObjectCollection
	 *[ @param $doc2 DataObjectCollection ]
	 *
	 * Merges 2 DataObjectCollections and returns the Union on the items
	 */
	public static function Merge($doc1,$doc2=null) {
		if($doc2==null) {
			return $doc1;
		}

		foreach($doc2 as $item) {
			if(!$doc1->find($item)) {
				$doc1->add($item);
			}
		}
		return $doc1;
	}


	public function find($needle) {
		foreach($this->_dataobjects as $do) {
			if($needle->{$needle->idField}==$do->{$do->idField}) {
				return true;
			}
		}
		return false;
	}
	
	public function getById($id) {
		foreach ($this->_dataobjects as $do) {
			if ($do->{$do->idField} == $id) {
				return $do;
			}
		}
		return false;
	}

	public function toJSON() {
		$array=array();
		foreach($this->getContents() as $item) {
			$array[]=$item->toArray();
		}
		if(function_exists('json_encode')) {
			return json_encode($array);
		}
		return null;
	}
	
	/**
	 * Returns the original model used for creating the collection
	 * if this is grabbed before 'load', then changes can be made to it that will be used
	 *
	 * @return DataObject
	 */
	public function getModel() {
		$temp=$this->_templateobject;
		return $temp;
	}
	
	public function getModelName() {
		return $this->_doname;
	}
	
	public function getTableName() {
		return $this->_tablename;
	}
	
	public function isEmpty() {
		return (count($this->_dataobjects)==0);
	}

	public function contains($key,$val) {
		foreach($this->getContents() as $index=>$model) {
			if($model->$key==$val) {
				return $index;
			}
		}
		return false;
	}

	public function pluck($key) {
		$return=array();
		foreach($this->getContents() as $model) {
			$return[$model->$key]=true;
		}
		return array_keys($return);
	}


	/*Iterator functions*/
	
	/**
	 * Returns the current Object for iteration
	 *
	 * @return DataObject
	 */
	public function current() {
		return $this->_dataobjects[$this->_pointer];
	}

	public function next() {
		$this->_pointer++;

	}

	public function key() {
		return $this->_pointer;
	}

	public function rewind() {
		$this->_pointer=0;
	}

	public function valid() {
		return ($this->_pointer<count($this));
	}
	/*end iterator*/
	//to implement countable
	public function count() {
		return count($this->_dataobjects);
	}
}
?>
