<?php
class DataObject implements Iterator {
	/**
	 * @protected	string	The name of the table represented by the Object
	 */
	protected $_tablename;
	protected $_select;
	/**
	 * @private DataField[]	An array of DataField Objects, representing the fields of the table
	 */
	protected $_fields;

	/**
	 * @protected	array	An array used to insert data from a collection to prevent multiple queries
	 */
	public $_data;


	private $ser_fields='';
	/**
	 * @protected	DataField[]	An array of DataField Objects represeiting the ones to be displayed on an overview
	 */
	private $_displayFields = null;

	/**
	 * @protected	boolean	Carries the status of whether the Object has been updated since it was loaded
	 */
	protected $_modified;
	/**
	 * @protected	boolean	Carries the status of whether the Object was loaded from the database
	 */
	protected $_loaded;
	/**
	 * @protected	string[]	An array of field-names that shouldn't be settable from anywhere else (e.g. 'created', 'modified' etc.)
	 */
	protected $_protected=array();
	/**
	 * An array of name->object pairs representing foreign-key relationships
	 *
	 * @protected array
	 */
	public $_lookups=array();
	/**
	 * An array of validators
	 *
	 * @protected array
	 */
	protected $_validators=array();
	/**
	 * @var	string	The name of the table's primary key
	 * @access public
	 * @note	Only accepts single-field keys at the moment
	 */
	public $idField='id';

	/**
	 * @protected the pointer for the iterator
	 * @note	Only accepts single-field keys at the moment
	 */
	protected $_pointer=0;

	/**
	 * @private	string	The name of the view to be used when displaying an overview. Accessed by getViewName
	 */
	private $_viewname;

	/**
	 * @protected	string	The name of the field used as the identifierField and therefore used to sort fields
	 * @todo	This will probably dissapper depending on usage of views
	 */
	protected $identifierField='name';

	/**
	 * @protected	string	Which field should be used to order overviews
	 */
	public $orderby;
	public $orderdir;
	protected $_autohandlers=array();

	protected $parent_field, $parent_relname;
	protected $acts_as_tree=false;
	/**
	 * @protected	array	Names of enumeration fields and their options
	 */
	protected $enums = array();

	/**
	 * @protected	array	Names of non-editable fields
	 */
	protected $notEditable = array();
	protected $not_settable = array();
	protected $isUnique=array();
	public $belongsTo = array();
	public $belongsToField = array();
	protected $hasOne=array();
	protected $hasMany = array();
	protected $hasManyThrough = array();
	protected $composites=array();
	public $aliases = array();
	protected $isCached=array();
	protected $concatenations=array();
	protected $hashes=array();
	protected $habtm=array();
	// protected array giving fields and values that if true do not allow
	// a given record to be deleted
	protected $indestructable = array();
	/**
	 * @protected	array	Names of fields to be hidden
	 */
	protected $hidden = array();
	protected $defaultDisplayFields = array();
	/**
	 * For holding search-handlers, to be used when a hasMany relationship is loaded
	 *
	 */
	protected $searchHandlers=array();

	/**
	 * @protected  bool  Should this model be limited by usernameaccess fields?
	 */
	protected $_accessControlled = false;
######################################################################################################

	/**
	 * Constructor
	 *
	 * Takes a table name and puts together an Object representing a row in the table
	 *
	 * @param	$tablename	string	The name of a table in the database
	 */
	public function __construct($tablename) {
		$this->_tablename=$tablename;

		$this->setFields();

		if($this->isField('id')) {
			$this->_protected[]='id';
		}
		$this->setDefaultHidden();
		$this->setDefaultValidators();
		DataObject::className(get_class($this));
		$this->getDefaultOrderby();
	}
	
	/**
	 * Constructs a DataObject of the given name, but lets the defaults be overridden if something 
	 * has been registered to implement ModelLoading
	 *
	 * @param String $modelname The name of the model to load
	 * @return DataObject
	 */
	public static function Construct($modelname) {		
		global $injector;
		try {
			$model_loader = $injector->instantiate('ModelLoading');
			$model = $model_loader->load($modelname);
		}
		catch(PhemtoException $e) {
			if(!class_exists($modelname)) {
				throw new Exception('Class '.$modelname.' doesn\'t exist');
			}
			$model = new $modelname;
		}
		if($model instanceof DataObject) {
			return $model;
		}
		else {
			throw new Exception('Failed to load model for '.$modelname);
		}
	}
	
	private function setDefaultRelationships() {
		if($this->isField('parent_id')) {
			$this->setParent();
		}
		foreach($this->_fields as $field) {
			if($field->name!=='parent_id'&&substr($field->name,-3)=='_id') {
				//we have a possible 'belongsTo', so see if there's a model available
				$classname=ucfirst(str_replace('_id','',$field->name));
				if(class_exists($classname)) {
					$this->belongsTo($classname,$field->name,strtolower($classname));
				}
			}
			if($field->name=='owner') {
				$this->belongsTo('User','owner','owner');
			}

		}
	}

	public function actsAsTree($fieldname='parent_id') {
		$this->acts_as_tree=true;
		$this->parent_field=$fieldname;
	}
	public function hasParentRelationship($fieldname) {
		if(!empty($this->parent_field)&&$this->parent_field==$fieldname) {
			return true;
		}
		return false;
	}
	public function getParent() {
		if($this->{$this->parent_field}!==null)
			return $this->{$this->parent_field};
		else
			return false;
	}

	protected function setParent($fieldname='parent_id',$relname='parent') {
		$this->parent_field=$fieldname;
		$this->parent_relname=$relname;
	}

	/**
	 * Certain fields have ways of being rescued from being null
	 */
	 private function setAutoHandlers() {
		$this->_autohandlers['id']=new IDGenHandler();
		$this->_autohandlers['usercompanyid']=new UserCompanyHandler();
		$this->_autohandlers['created']=new CurrentTimeHandler();
		$this->_autohandlers['lastupdated']=new CurrentTimeHandler(true);
		$this->_autohandlers['alteredby']=new CurrentUserHandler(true,'EGS_USERNAME');
		$this->_autohandlers['glperiods']=new CurrentPeriodHandler();
		$this->_autohandlers['owner']=new CurrentUserHandler(false,'EGS_USERNAME');
		//$this->_autohandlers['assigned'] = new CurrentUserHandler(false,EGS_USERNAME);
		$this->_autohandlers['language_code'] = new DefaultLanguageHandler();
		
		$this->_autohandlers['job_no'] = new JobNumberHandler();
		$this->_autohandlers['revision'] = new RevisionHandler();
		$this->_autohandlers['position'] = new PositionHandler();
		$this->_autohandlers['index'] = new PositionHandler('index');
		if(defined('WEBSITE_ID')) {
			$this->_autohandlers['website_id'] = new CurrentWebsiteHandler(false,WEBSITE_ID);
		}
	 }

	function assignAutoHandler($fieldname,AutoHandler $handler) {
		$this->_autohandlers[$fieldname]=$handler;
	}

	function setFields() {
		
		$fields = getFields($this->_tablename);
		if($fields===false) {
			throw new Exception('Failed to load fields, perhaps invalid table name specified in DataObject: '.$this->_tablename);
		}
		foreach($fields as $key=>$field) {
			$this->_fields[$key]=DataField::Construct($field);
		}
	}
	
	/**
	 * Set Display Fields
	 *
	 * Sets the fields this data object should display
	 *
	 *
	 */
	function setDisplayFields(){
		if(count($this->defaultDisplayFields) > 0) {
			foreach($this->defaultDisplayFields as $fieldname=>$tag) {
				if(is_string($fieldname)) {
					$this->_displayFields[$fieldname]=$this->getField($fieldname);
					$this->_displayFields[$fieldname]->tag = $tag;
					$this->_displayFields[$fieldname]->name = $fieldname;
				}
				else {
					$field = $this->getField($tag);
					if($field===false) {
						throw new Exception('Requested field not found: '.$tag);
					}
					$this->_displayFields[$tag]=$field;
//					$this->_displayFields[$tag]->tag = $this->getField($tag)->tag;
					$this->_displayFields[$tag]->name = $tag;
				}
			}
		}
		else {
			foreach($this->_fields as $field)
			{
				if ($field instanceof DataField&&!$this->isHidden($field->name)){
					$this->_displayFields[$field->name] = $field;
				}
			}
			if(count($this->_displayFields)>6)
				$this->_displayFields=array_slice($this->_displayFields,0,6);

		}
	}
	function setAdditional($fieldname,$type=null,$tag=null) {
		$t=new ADOFieldObject();
		$t->type=(isset($type)?$type:'varchar');
		$t->tag=(isset($tag)?$tag:prettify($fieldname));
		$t->name=$fieldname;
		$t->ignoreField=true;
		$this->_fields[$fieldname]=new Datafield($t);
	}
	/**
	 * Might this be needed?
	 *
	 * @return	DataField[]	An array of DataField Objects
	 */
	public function getDisplayFields() {
		if(!isset($this->_displayFields))
		{
			$this->setDisplayFields();
		}
		return $this->_displayFields;
	}
	public function getDisplayFieldNames() {
		$return=array();
		foreach($this->getDisplayFields() as $field) {
			$return[$field->name]=$field->tag;
		}
		return $return;
	}
	/**
	 * Might this be needed?
	 *
	 * @param	string	$field	The name of the field to be checked
	 * @return	boolean	true if is displayed, false if not displayed
	 */
	public function isDisplayedField($fieldname) {
		$temp = $this->displayFields[$fieldname];
		if(isset($temp)) {
			return true;
		}
		return false;
	}

	/**
	 * Set any validators that should be based on the Object as a whole
	 *
	 * @todo	Add some, I'm sure there must be things?
	 */
	function setDefaultValidators() {

	}
	
	public function update($id, $field, $value) {
		$db = DB::Instance();
		if (!is_array($field)) {
			if ($value == 'null') {
				$query = "update {$this->_tablename} set $field=null where {$this->idField} = {$db->qstr($id)}";
			}
			else if (substr($value,0,1) == '(') {
				$query = "update {$this->_tablename} set $field=$value where {$this->idField} = {$db->qstr($id)}";
			}
			else {
				$query = "update {$this->_tablename} set $field={$db->qstr($value)} where {$this->idField} = {$db->qstr($id)}";
			}
		}
		else if (count($field) == count($value)) {
			for ($i=0;$i<count($field);$i++) {
				if (!($this->update($id,$field[$i],$value[$i]))) {
					return false;
				}
			}
			return true;
		}
		return ($db->Execute($query)!==false);
	}
	
	/**
	 * Load a record from the database and assign appropriate values to the Object
	 * -Doesn't load foreign-table properties until they are requested
	 *
	 */
	public function load($clause,$override=false) {
		if(isset($this->_data)){
			$row = $this->_data;
			//$this->setView();
		}
		else{
			$db=&DB::Instance();
			$select=$this->_select;
			if(empty($select)) {
				$select='*';
			}
			if(empty($clause))
				return false;
			$query='SELECT '.$select.' FROM '.$this->_tablename.' WHERE '.$this->idField.'='.$db->qstr($clause);
			if(!$override&&$this->isField('usercompanyid')) {
				$query.=' AND usercompanyid='.$db->qstr(EGS::getCompanyId());
			}
			$row=$db->GetRow($query);
			if($row===false) {
				throw new Exception('Query failed: '.$query);
			}
		}
		if(!is_array($row)) {
			return false;
		}
		foreach($row as $key=>$val) {
			//$this->$key=stripslashes($val);
			$this->$key=$val;
			if(!isset($this->_fields[$key])) {
				$this->_fields[$key]=stripslashes($val);
			}
		}
		foreach($this->hashes as $fieldname=>$array) {
			$this->hashes[$fieldname]=unserialize(base64_decode($this->$fieldname));
		}
		if(count($row)>0) {
			$this->_loaded=true;
			return $this;
		}
		return false;

	}

	/**
	* Delete a record from the database, or set the 'deleted' flag to true
	* after DO has been loaded
	* 
	* @param int $id optional
	* @return boolean
	*/
	public function delete($id=null) {
		if ($id!==null||$this->_loaded) {
			$delete = true;
			foreach ($this->indestructable as $field=>$value) {
				if ($this->{$field} == $value)
					$delete = false;
			}
			if (!$delete) {
				return $delete;
			}			
			$db=&DB::Instance();
			if($id==null) {
				$id=$this->{$this->idField};
			}
			if ($this->isField('deleted')) {
				$query = 'UPDATE '.$this->_tablename.' SET deleted=true WHERE '.$this->idField.'='.$db->qstr($id);
			} else {
				$query = 'DELETE FROM '.$this->_tablename. ' WHERE '.$this->idField.'='.$db->qstr($id);
			}
			if ($this->isField('usercompanyid')) {
				$query .=  " AND usercompanyid = " . $db->qstr(EGS::getCompanyId());
			}
			$result=$db->Execute($query);
			return ($result!==false);
		}
		else {
			return false;
		}
	}

	/**
	 * Load a DO based on something other than it's idField
	 *
	 * Need to decide whether multiple DOs meeting the criteria will cause error, or pick one, or just ignore?
	 */
	function loadBy($field,$value=null,$tablename=false) {
		$db=&DB::Instance();
		if($field instanceof SearchHandler) {
			$sh = $field;
			$sh->setLimit(1);
			$qb=new QueryBuilder($db);
			$query=$qb->select($sh->fields)
				->from($this->_tablename)
				->where($sh->constraints)
				->orderby($sh->orderby,$sh->orderdir)
				->limit($sh->perpage,$sh->offset)->__toString();
		}
		else {
			if($field instanceof ConstraintChain) {
				$where = $field->__toString();
			}
			elseif(!is_array($field)&&!is_array($value))
				$where=$field.'='.$db->qstr($value);
			elseif(!(is_array($field)&&is_array($value))) {
				throw new Exception('Error: $fieldname and $value must be of same type, array or string');
			}
			else {
				$where='1=1';
				for($i=0;$i<count($field);$i++) {
					if ((!$tablename) && (($this->getField($field[$i])->type == 'numeric') || (substr($this->getField($field[$i])->type,0,3) == 'int')) && ($value[$i] == ''))
						$where .= ' AND '.$field[$i].'=null';
					else
						$where.=' AND '.$field[$i].'='.$db->qstr($value[$i]);
				}

			}
			if($this->isField('usercompanyid')) {
				try {
					$ucid = EGS::getCompanyId();
					$where.=' AND usercompanyid='.$db->qstr($ucid);
				}
				catch(Exception $e) {
					//means it wasn't set, this isn't the place to care...
				}
			}
			if ($tablename)
				$query = 'SELECT id FROM ' .$tablename.' WHERE '.$where;
			else
				$query='SELECT * FROM '.$this->_tablename.' WHERE '.$where;
		}
		$row=$db->GetRow($query);
		if($row===false) {
			throw new Exception("LoadBy query failed: " . $db->ErrorMsg() . ':' . $query);
		}
		if(count($row)>0) {
			$this->_data=$row;
			return $this->load($row[$this->idField]);
		}
		return false;
	}

	/**
	 * Saves the current state of the Object to the database.
	 * Assumes data has been validated, so will result in exception if update/insert fails
	 * Will call save() on any loaded hasMany() relationships. (Actual DB-updates on such Objects will depend on their check for modification)
	 * @throws	Exception
	 * @return	boolean	true on success, false otherwise
	 * @todo	Use a 'modified' variable to avoid un-necessary saves
	 * @todo	If caching is implemented elsewhere, will probably need to be able to flush appropriate bits from here
	 */
	function save($debug=false) {
		$db=DB::Instance();
		if($debug)
			$db->debug = true;

		$data=array();
		$myIdField = $this->{$this->idField};
		foreach($this->getFields() as $key=>$field) {
			if($field->ignoreField) {
				continue;
			}
			$value = trim($field->finalvalue, "'");
			if(($field->type=='file'||substr($field->type,0,3)=='int') && empty($value) ) {
				$this->saveFile();
				continue;
			}
			//quote numeric-looking strings so as to preserve leading 0s
			if($field->type=='varchar' && isset($value)&& is_numeric($value)) {
				$value = $db->qstr($value);
			}
			
			if(
				($field->type=='timestamp'&&trim($value)==='')
				||($field->type=='time'&&trim($value)==='')
				||($field->type=='uuid'&&trim($value)==='')
				||($field->type=='numeric'&&trim($value)==='')
				||($field->type=='varchar'&&trim($value)===''&&$field->not_null!==true)
				||($field->type=='date'&&trim($value)==='')) {
				$data[$key]='NULL';
			}
			else {
				$data[$key]=$value;
			}

		}
		foreach($this->hashes as $fieldname=>$array) {
			$data[$fieldname]=base64_encode(serialize($array));
		}
		if(isset($data[$this->idField])&&$data[$this->idField]=='NULL'){
			unset($data[$this->idField]);
		}
		if($key=='usercompanyid'&&empty($data[$key])) {
			$data[$key] = EGS::getCompanyId();
		}
		$ret = $db->Replace($this->_tablename, $data,$this->idField,true);
		if($ret===0) {
			throw new Exception('Save of '.get_class($this).' failed: '.$db->ErrorMsg());
		}
		//else
		$id = $this->{$this->idField};
		if(empty($id)) {
			$this->id = $db->get_last_insert_id();
		}
		
		if($debug) {
			$db->debug = false;
		}
		return true;
		
	}
	
	private function saveFile() {
	//		$db = DB::Instance();
	//		$db->UpdateBlobFile('file','file','','id='.$this->{$this->idField});
	}

	public static function className($var=null) {
		static $name;
		if(empty($name)&&!empty($var)) {
			$name=$var;
		}
		return $name;
	}
	function getBooleanFields() {
		$return=array();
		foreach($this->getFields() as $field) {
			if($field->type=='bool') {
				$return[]=$field;
			}
		}
		return $return;
	}

	function autoHandle($fieldname) {
		$this->setAutoHandlers();
		if(empty($this->_autohandlers[$fieldname])) {
			return false;
		}
		return $this->_autohandlers[$fieldname]->handle($this);

	}

	/**
	 * Static function that attempts to construct a dataobject based on the passed $data array
	 * @param	$data	array	An array of key=>value pairs to use when constructing the DataObject
	 * @param	&$errors	array	An array passed by reference to which errors will be added
	 *
	 * @return	DataObject	On success, a valid DataObject will be returned. Otherwise, false.
	 *
	 */
	public static function Factory(Array $data,&$errors=array(),$do_name=null) {
		//first we get an instance of the desired class
		if(!($do_name instanceof DataObject)) {
			$do=self::Construct($do_name);
		}
		else {
			$do=$do_name;
		}
		/* @var $do DataObject */
		
		//then get the fields and then loop their validators
		$do_fields=$do->getFields();
		$mode = "NEW";
		foreach ($data as $key=>$value) {
			if (!is_array($value)) {
				$data[$key] = trim($value);
			}
			if ($value === 'NULL' || $value === 'null') {
				$data[$key] = '';
			}
		}
		$force_change = array();
		//if editing, assign current values to $data where fields are empty
		if(!empty($data[$do->idField])) {		
			$current = $do->load($data[$do->idField]);
			if($current!==false) {
				$mode = "EDIT";
				$maintain=array('created','owner');
				$force_change = array('lastupdated','alteredby');
				foreach($maintain as $fieldname) {
					if($do->isField($fieldname)) {
						$field=$do->getField($fieldname);
						$field->ignoreField=true;
					}
				}
			}
		}
		foreach($do_fields as $name=>$field) {
			if($field->ignoreField) {
				continue;
			}
			if($field->type=='oid') {
				$data[$name]=0;
			}
			if($do->isNotSettable($name) && !empty($data[$name])) {
				unset($data[$name]);
			}
			if($field->type=='numeric'&&isset($data[$name])&&$data[$name]==='0') {
				$data[$name]=0;
			}
			if($field->type=='timestamp'&&!empty($data[$name])) {
				if (!preg_match('/:/',$data[$name])) {
					if(!empty($data[$name.'_hours'])) {
						$data[$name].=' '.$data[$name.'_hours'];
					}
					else {
						$data[$name].=' 00';
					}
					if(!empty($data[$name.'_hours'])) {
						$data[$name].=':'.$data[$name.'_minutes'];
					}
					else {
						$data[$name].=':00';
					}
				}
			}
			if($field->type=='bool'&&!isset($data[$name]) && isset($data['_checkbox_exists_'.$name])) {
				$data[$name] = 'false';
			}
			
			// Fix lazy usernames
			$username_fields = array('assigned_to', 'assigned_by', 'alteredby', 'owner');
			$us = Omelette::getUserSpace();
			if (in_array($name, $username_fields) && !empty($data[$name]) && !preg_match('#//'.preg_quote($us).'$#', $data[$name])) {
				$data[$name] = $data[$name] . '//' . $us;
			}
			
			//if there's nothing set, then we either want to use the current value, or set it to something
			//so that validation happens
			if(!isset($data[$name])) {
				if($mode=='EDIT') {
					// More dreadful hacking around the separation of date and time
					// Skip this step for activity time fields because otherwise they get formatted before validation, so will test with incorrect values  
					if (!in_array($name, array('time', 'end_time'))) {
						$data[$name] = $current->$name;
					}
				} else {
					$data[$name] = '';
				}
			}
			if(in_array($name,$force_change)) {
				unset($data[$name]);
			}
			if (empty($data[$name])&&!(isset($data[$name])&&$data[$name]===0)) {
				if($field->type=='bool'&&!$field->has_default && (!isset($data[$name])||$data[$name]!==true)) {
					$data[$name]='false';
				}
				$test=$do->autoHandle($name);
				if($test!==false) {
					$data[$name]=$test;
				}
			}
			
			//can't change the value of some fields
			if($mode =="EDIT" && isset($data[$name]) && $do->isNotEditable($name)) {
				unset($data[$name]);
				continue;
			}
			//if there's no value, and a default, then use it
			if($mode =="NEW" && (!isset($data[$name]) || $data[$name] === '') && $field->has_default == 1) {
				$data[$name] = $field->default_value;
			}
			if(isset($data[$name])) {
				// Horrible horrible hack
				// Activity times need to be evaluated with a date context, or we won't know if DST is in force
				// So, we need to pass the time validator another field
				// One day, date and time will not be separate columns
				if ($do->get_name() === 'Activity') {
					if ($name === 'time') {
						$datefield = $do->getField('date');
						$do->$name=$field->test($data[$name], $errors, $datefield);
					} elseif ($name === 'end_time') {
						$datefield = $do->getField('end_date');
						$do->$name=$field->test($data[$name], $errors, $datefield);
					} else {
						$do->$name=$field->test($data[$name],$errors);						
					}
				} else {
					$do->$name=$field->test($data[$name],$errors);
				}
			}
			
		}
		
		$do->test($errors);
		
		//then test the model as a whole
		if(count($errors)==0) {
			return $do;
		}
		return false;
	}

	public function addValidator(ModelValidation $validator) {
		$this->_validators[]=$validator;
	}
	
	public function getValidators(){
		return $this->_validators;
	}

	/**
	 * Add a validation rule that the specified fieldname must be unique
	 *
	 * @param	$fieldname	string	the name of a DataObject field
	 */
	protected function validateUniquenessOf($fields,$message=null) {
		$this->isUnique[]=$fields;
		if(!is_array($fields)) {
			$fields=array($fields);
		}
		foreach($fields as $fieldname) {
			if(!$this->isField($fieldname)) {
				throw new Exception('Invalid fieldname ('.$fieldname.') provided to validateUniquenessOf() in DataObject.php');
			}
		}

		$this->addValidator(new UniquenessValidator($fields,$message));
	}

	public function checkUniqueness($fields) {
		if(!is_array($fields)) {
			return in_array($fields,$this->isUnique);
		}
		return false;
	}
	
	
	protected function validateEqualityOf() {
		$fieldnames=array();
		$args=func_get_args();
		foreach($args as $fieldname) {
			$fieldnames[]=$fieldname;
		}
		if(count($fieldnames)<2) {
			throw new Exception('Need at least 2 fieldnames to test for equality!');
		}
		$this->addValidator(new EqualityValidator($fieldnames));
	}

	protected function test(Array &$errors) {
		foreach($this->getValidators() as $validator) {
			$validator->test($this,$errors);
		}
	}

	public function isLoaded() {
		return $this->_loaded;
	}

	public function setIsLoaded($is=true) {
		$this->_loaded = $is;
	}
	
	public function fieldTest(&$errors)
	{
		$myIdField = $this->{$this->idField};
		$fields = $this->getFields();
		foreach($fields as $field){
			if($field->_name == $myIdField)
				continue;
			$field=$field->test($errors);
		}
		if($errors > 0){
		return false;
		}
		return true;
	}

	/**
	 * Returns the name of the table used by the DataObject
	 * @return	string	the name of the table
	 */
	public function getTableName() {
		return $this->_tablename;
	}

	/**
	 * Allows for the setting of db-field values
	 *
	 * @param	$key	string	the name of the fields
	 * @param	$val		mixed	the value to be assigned
	 *
	 * @todo should this check Validators? Booleans will need to be coerced for example...
	 */
	public function __set($key,$val) {
		if($this->isField($key))
		{
			$this->_fields[$key]->value=$val;
		}
		else
		{
			if(isset($this->belongsTo[$key]))
			{
				$this->belongsTo[$key] = $val;
			}
		}
	}

	
	protected function isProtected($var) {
		if(in_array($var,$this->_protected))
			return true;
		return false;
	}

	/**
	 * Might this be needed?
	 *
	 * @return	Array	An array of DataField Objects
	 */
	public function getFields() {
		$return=array();
		foreach($this->_fields as $fieldname=>$field) {
			$return[$fieldname]=$this->getField($fieldname);
		}
		return $return;
	}
	/**
	 * Return the DataField Object representing the named field
	 *
	 * @param	String	the name of a db field
	 * @return	DataField
	 */
	public function getField($field) {
		$field=strtolower($field);
		if(($field == $this->identifierField)&&(strpos($field,'||')))
		{
			$ob = new ADOFieldObject();
			$ob->value = $this->getIdentifierValue();
			return $ob;
		}
		if(!isset($this->_fields[$field])) {
			if(isset($this->concatenations[$field])) {
				$concat_field=new ADOFieldObject();
				$concat_field->type='varchar';
				$concat_field->name=$field;
				foreach($this->concatenations[$field]['fields'] as $fieldname) {
					$bit = $this->$fieldname;
					if(!empty($bit)) {
						$concat_field->value.=$bit.$this->concatenations[$field]['separator'];
					}
				}
				$concat_field->ignoreField=true;
				$this->_fields[$field]=new DataField($concat_field);
			}
		}
		if(isset($this->belongsTo[$field])) {
			$this->_fields[$field]->ignoreField=true;
		}
		
		if(isset($this->aliases[$field])) {
			$alias=$this->aliases[$field];
			$model = new $alias['modelName'];
			//$constraints=$alias['constraints'];
			//$constraints->add(new Constraint(get_class($this).'_id','=',$this->{$this->idField}));
			//$model->loadBy($constraints);
			$alias_field=clone $model->getField($alias['requiredField']);
			//$alias_field->value = $this->$field;
			return $alias_field;
		}
		if(isset($this->_fields[$field])) {
			return $this->_fields[$field];
		}
		return false;
	}
	/**
	 * Checks if the given value is the name of one of the DB fields represented by the objects
	 *
	 * @param	$var	string	the name to be tested
	 * @return	boolean
	 */
	public function isField($var,$depth=1) {
		if(isset($this->_fields[strtolower($var)])) {
			return true;
		}		
		return false;
	}
	public function getOptions($field,$depth=5) {
		
		if($this->isField($field,0)) {
			if(isset($this->belongsToField[$field])) {
				$bt=$this->belongsTo[$this->belongsToField[$field]];
				$model= new $bt['model'];
				return $model->getAll();				
			}
		}
		if($depth>0) {
			foreach($this->composites as $model_name) {
				$model=new $model_name;
				$options=$model->getOptions($field,$depth-1);
				if($options!==false)
					return $options;
			}
			foreach($this->aliases as $array) {
				$model=new $array['modelName'];
				$options=$model->getOptions($field,$depth-1);
				if($options!==false) {
					return $options;
				}
			}
		}
		return false;
	}
	
	public function getOptionsCount($field,$depth=5) {
		if($this->isField($field,0)) {
			if(isset($this->belongsToField[$field])) {
				$bt=$this->belongsTo[$this->belongsToField[$field]];
				$model = new $bt['model'];
				return $model->getCount();
			}
		}
	}
	
	/**
	 * A function to cycle the fields and assign human-friendly tags
	 * @see getTag();
	 */
	public function setTags() {
		foreach($this->getFields() as $fieldname=>$field) {
			$field->tag=$this->getTag($fieldname);
		}
	}
	/**
	 * Returns a human-friendly title for the field
	 * Expected to be over-ridden by sub-classes, and probably user-customisable as well?
	 *
	 * @param	string	the name of the field
	 * @return	string	the name passed through ucwords()
	 */
	public function getTag($field) {
		global $injector;
		//return ucwords(str_replace('_id','',strtolower($field)));
		$translator=$injector->instantiate('Translation');
		return $translator->translate($field);
	}
	
	protected function getEnum($name,$val) {
		return $this->enums[$name][$val];
	}

	function getFormatted($name, $formatter=null) {
		if (!is_null($formatter)) {
			$value = $this->$name;
			$f = new $formatter;
			return $f->format($value);
		}
		if(isset($this->belongsTo[$name])) {
			$fields=$this->_fields;
			if(isset($fields[$name])) {
				$value=$this->_fields[$name]->value;
			}
			if(empty($value)) {
				$modelname = $this->belongsTo[$name]['model'];
				$model = DataObject::Construct($modelname);
				
					
				$model->load($this->{$this->belongsTo[$name]['field']});
				$field = $model->getField($model->identifierField);
				//$this->_fields[$var] = clone $field;
				$field->tag=prettify($name);
				$value = $field->formatted;
				
			}
			return $value;
		}
		return $this->_fields[$name]->formatted;
	}


	/**
	 * Allows for the getting of the values of DB-fields
	 * (potentially over-ridden by child-classes as a way to modify public variables?)
	 *
	 * @return	mixed	The value of the corresponding field
	 *
	 * @todo	need to use this method for lazy-loading of relationships
	 */
	public function __get($var) {
		$var=strtolower($var);
		if($this->isField($var,1)) {
			if(is_string($this->_fields[$var])) {
				return $this->_fields[$var];
			}
			$attempt=$this->_fields[$var]->value;
			if(!empty($attempt)||$attempt===0||$attempt==='0'||$attempt===(float)0||$attempt==='') {
				
				if ($this->isEnum($var)) {
					return $this->getEnum($var,$attempt);
				}

				return $attempt;
			}
			
		}
		
		if($var==$this->parent_relname) {
			$p_name=get_class($this);
			$parent_model = new $p_name();
			$parent_id=$this->getParent();
		
			$parent_model->load($parent_id);

			$parent=$parent_model->{$this->identifierField};
			return $parent;
		}
		if(isset($this->isCached[$var])) {
			return $this->isCached[$var];
		}
		if(isset($this->aliases[$var])) {
			$id = $this->{$this->idField};
			if (empty($id)) {
				return false;
			}
			
			$model=new $this->aliases[$var]['modelName'];
			$cc = $this->aliases[$var]['constraints'];
			if ($cc instanceof SearchHandler) {
				$cc->addConstraint(new Constraint ($this->get_name().'_id','=',$this->{$this->idField}));					
			}
			else {
				$cc->add(new Constraint ($this->get_name().'_id','=',$this->{$this->idField}));
			}
			$model->loadby($cc);
			$this->isCached[$var]=$model;
			
			return $model;
		}
		if(isset($this->concatenations[$var])) {

			$string='';
			foreach($this->concatenations[$var]['fields'] as $fieldname) {
				$s = $this->$fieldname;
				if (!empty($s))
					$string .= $this->$fieldname.' ';
				
			}
			$string = trim($string);
			return $string;
		}
		if(isset($this->hasManyThrough[$var])) {
			$jo = $this->hasManyThrough[$var]['jo'];
			$collectionname = $jo . 'Collection';
			$collection = new $collectionname(new $jo);
			if (!isset($handlers[$var])) {
				$sh = new SearchHandler($collection,false);
				$sh->extract();
			}
			else
				$sh = $handlers[$var];
			unset($sh->fields[strtolower(get_class($this))]);
			unset($sh->fields[strtolower(get_class($this)).'_id']);
			$sh->addConstraint(new Constraint(get_class($this).'_id','=',$this->{$this->idField}));
			$collection->load($sh);
		}
		if(isset($this->habtm[$var])) {
			$db=DB::Instance();
			$r_model=new $this->habtm[$var]['model'];
			$a=strtolower(get_class($this)).'_id';
			$b=strtolower($this->habtm[$var]['model']).'_id';
			$query='SELECT remote.* FROM '.$this->_tablename.' AS local JOIN '.$this->habtm[$var]['table'].' AS middle ON (local.'.$this->idField.'=middle.'.$a.') JOIN '.$r_model->_tablename.' AS remote ON (middle.'.$b.'=remote.'.$r_model->idField.') WHERE local.'.$this->idField.'='.$db->qstr($this->{$this->idField});
			$c_query=str_replace('remote.*','count(*)',$query);
			$c_name = $this->habtm[$var]['model'].'Collection';
			$collection = new $c_name($r_model);
			$collection->load($query,$c_query);
			$this->isCached[$var]=$collection;
			return $collection;
		}
		if(isset($this->hasMany[$var])) {
			$do=$this->hasMany[$var]['do'];
			$collectionname = $do . 'Collection';
			$collection = new $collectionname(new $do);
			$this->isCached[$var]=$collection;
			if(!$this->isLoaded()) {
				return $collection;
			}
			$handlers=$this->searchHandlers;
			if(!isset($handlers[$var])) {
				$sh=new SearchHandler($collection,false);
				$sh->extractOrdering();
			}
			else
				$sh=$handlers[$var];
			unset($sh->fields[strtolower(get_class($this))]);
			unset($sh->fields[strtolower(get_class($this)).'_id']);
			$sh->addConstraint(new Constraint($this->hasMany[$var]['fkfield'],'=',$this->{$this->idField}));
			$collection->load($sh);
			return $collection;
		}

		if(isset($this->hasOne[$var])) {
			if(!isset($this->_fields[$var])) {
				$model = new $this->hasOne[$var]['model'];
				$model->load($this->{$this->hasOne[$var]['field']});
				return $model;
			}
		}

		if(isset($this->belongsTo[$var])) {
			$fields=$this->_fields;
			if(isset($fields[$var])) {
				$value=$this->_fields[$var]->value;
			}
			if(empty($value)) {
				$modelname = $this->belongsTo[$var]['model'];
				$model = DataObject::Construct($modelname);
				
					
				$model->load($this->{$this->belongsTo[$var]['field']});
				$field = $model->getField($model->identifierField);
				$this->_fields[$var] = $field;
				$field->tag=prettify($var);
				$value = $field->value;
				
			}
			return $value;
		}

		if($var=='identifierField') {
			return $this->getIdentifier();
		}
		if($var==$this->getIdentifier())
		{
			return $this->getIdentifierValue();
		}
		if($var=='loaded'||$var=='modified'||$var=='tablename') {
			return $this->{'_'.$var};
		}
		foreach($this->hashes as $fieldname=>$objects) {
			if(isset($objects[$var])) {
				return unserialize($objects[$var]);
			}
		}
		return null;
	}

	/**
	 * magic method for handling is_XXX methods automatically
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	function __call($method, $args) {
		if(substr($method,0,3) == 'is_') {
			$fieldname = substr($method, 3);
			if($this->isField($fieldname)) {
				return $this->$fieldname == 't';
			}
		}
	}
	

	function getIdentifierValue() {
		$exploded = explode('||',stripslashes($this->identifierField));
		$return = '';
		foreach($exploded as $var) {
			$var = trim($var);
			if($this->isField($var)) {
				$val = $this->getField($var)->value;
				$return.= $val;
			}
			else $return.=trim($var,"'");
		}
		return $return;
	}

	function hasOne($do,$field=null,$name=null) {
		if(!isset($field)) {
			if($this->isField(strtolower($do).'id')) {
				$field=strtolower($do).'id';
			}
			else if($this->isField(strtolower($do).'_id')) {
				$field=strtolower($do).'_id';
			}
			else {
				$this->ErrorMsg='If neither <fk>id nor <fk>_id are fields in the table, the fieldname must be specified';
				return false;
			}
		}
		if(!$this->isField($field)) {
			return false;
		}
		if(!isset($name)) {
			$name=strtolower($do);
		}
		if(class_exists($do)) {
			$this->hasOne[$name]=array('model'=>$do,'field'=>$field,'name'=>$name);
			return true;
		}
		return false;
	}
	public function addSearchHandler($cname,SearchHandler $sh) {
		$this->searchHandlers[$cname]=$sh;
	}

	/**
	 * Register a particular field as being a foreignkey to another table
	 *
	 * @param string $do the name of the dataobject that should be represented
	 * @param string $field the field-name
	 * @param string $name the name by which the object can later be referenced
	 * @return boolean
	 */
	function belongsTo($do,$field=null,$name=null) {
		if(!isset($field)) {
			if($this->isField(strtolower($do).'id')) {
				$field=strtolower($do).'id';
			}
			else if($this->isField(strtolower($do).'_id')) {
				$field=strtolower($do).'_id';
			}
			else {
				$this->ErrorMsg='If neither <fk>id nor <fk>_id are fields in the table, the fieldname must be specified';
				return false;
			}
		}
		if(!$this->isField($field)) {
			return false;
		}
		if(!isset($name)) {
			$name=strtolower($do);
		}
		if(class_exists($do)) {
			$this->belongsTo[$name]=array('model'=>$do, 'field'=>$field);
			$this->belongsToField[$field] = $name;
			$this->_fields[$name] = new DataField(new ADOFieldObject());
			$this->_fields[$name]->name =$name;
			$this->_fields[$name]->tag = prettify($name);
			$this->_fields[$name]->field = $field;
			$this->_fields[$name]->ignoreField = true;
			$this->getField($field)->addValidator(new ForeignKeyValidator($do));
			
			return true;
		}
		return false;
	}

	/**
	 * Tell the class of another dataobject that is dependent on it. Any registered dependents will be loaded
	 * when the object is
	 * 
	 * @param	string	$do the name of the DO that is to be registered
	 * @param	string optional	$name the name that should be given to the collection (defaults to $name.'s')
	 * @param	string	optional $fkfield the name of the foreign-key field in the dependent (defaults to the current-class-name suffixed with 'id')
	 */
	function hasMany($do,$name=null,$fkfield=null) {
		if(!isset($name))	//default to adding an s
			$name=strtolower($do).'s';
		if(!isset($fkfield))	//default to the name of the current class + id
			$fkfield=$this->get_name().'_id';
		//add the DOC to the class's list (will allow for multiple dependents)
		$this->hasMany[$name]=array('do'=>$do,'fkfield'=>$fkfield);
		return true;
	}

	function hasManyThrough($jo,$field,$name) {
		$this->hasManyThrough[$name] = array('jo'=>$jo,'field'=>$field);
	}
	
	function hasAndBelongsToMany($do,$j_table,$name=null) {
		if($name==null) {
			$inflector=new Inflector();
			$name=$inflector->pluralize(strtolower($do));
		}
		$this->habtm[$name]=array('model'=>$do,'table'=>$j_table);
	}

	function getHasMany() {
		return $this->hasMany;
	}

	/**
	 * Sets an alias to a model with extra constraints
	 *
	 * @param	string	$alias	the alias for a model
	 * @param	string	$modelName	the name of the model to alias
	 * @param	constraintchain	$constraints	a chain of constraints to be met by the model
	 * @param	string	$requiredfield	required to be present in order for aliased model to be saved
	 */
	function setAlias($alias,$modelName,$constraints,$requiredField=null,$otherFields=array()) {
		$this->aliases[$alias]=array("modelName"=>$modelName,"constraints"=>$constraints,"requiredField"=>$requiredField,'otherFields'=>$otherFields);
		$model = new $modelName;

		if(count($otherFields)>0) {
			foreach($otherFields as $fieldname) {
				$this->_fields[$fieldname]=clone $model->getField($fieldname);
				$this->_fields[$fieldname]->ignoreField=true;
			}
		}
//		$this->_fields[$alias]=clone $model->getField($requiredField);
//		$this->_fields[$alias]->ignoreField=true;

	}

	/**
	 * Returns a modelname, constraints and required field for a given alias
	 *
	 * @param	string	$alias	fieldname to check alias for
	 * @return	array	an array of modelname, contraints and required field
	 */
	function getAlias($alias) {
		if(isset($this->aliases[$alias]))
			return $this->aliases[$alias];
		return false;
	}

	function setConcatenation($name,Array $fields,$separator=' ') {
		$this->concatenations[$name]=array('fields'=>$fields,'separator'=>$separator);
	}

	function getCount() {
		$db=&DB::Instance();
		$tablename=$this->_tablename;
		if ($this->isAccessControlled()) {
			if($constraint=='')
				$constraint=' WHERE ';
			else
				$constraint.=' AND ';
			$constraint.='usernameaccess='.$db->qstr(EGS_USERNAME);
			$collection_name=get_class($this).'Collection';
			$coln = new $collection_name;
			$tablename=$coln->_tablename;
		}
		if($this->isField('usercompanyid')) {
			if($constraint=='')
				$constraint=' WHERE ';
			else
				$constraint.=' AND ';
			$constraint.='usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		}
		$query = 'SELECT count(*) FROM '.$tablename;
		
		if ($constraint <> '') {
			$query .= $constraint;
		}
		$count=$db->GetOne($query);
		return $count;
	}

	/**
	*Get all the ID Identifier pairs for example to fill a select.
	* @todo 	Change name!
	*
	*/
	function getAll(ConstraintChain $cc=null,$ignore_tree=false,$use_collection=false) {
		$db=DB::Instance();
		$tablename=$this->_tablename;
		if ($use_collection) {
			$collection_name=get_class($this).'Collection';
			$coln = new $collection_name;
			$tablename=$coln->_tablename;
		}
		if (empty($cc))
			$cc = new ConstraintChain();
		if ($this->isAccessControlled()) {
			$cc->add(new Constraint('usernameaccess','=',EGS::getUsername()));
			$collection_name=$this->get_name().'Collection';
			if(!class_exists($collection_name)) {
				throw new Exception("Can't load collection for getAll: " . $collection_name);
			}
			$coln = new $collection_name;
			$tablename=$coln->_tablename;
		}
		if($this->isField('usercompanyid')) {
			$cc->add(new Constraint('usercompanyid','=',EGS::getCompanyId()));
		}
		if(!$ignore_tree&&$this->acts_as_tree) {
			return $this->getAllAsTree($cc,$tablename);
		}
		$query = 'SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$tablename;
		$constraint = $cc->__toString();
		if ($constraint !== false) {
			$query .= ' WHERE '. $constraint;
		}
		if (!empty($this->orderby)) {
			$query .= ' ORDER BY '.$this->orderby;
		}
		else {
			$query .= ' ORDER BY '.$this->getIdentifier();
		}
		$results=$db->GetAssoc($query);
		if($this->idField==$this->getIdentifier()) {
			foreach($results as $key=>$nothing) {
				$results[$key]=$key;
			}
		}
		return $results;
	}
	private function getAllAsTree($cc=null,$tablename) {
		$items=array();
		$this->tree($items,$cc,$tablename);
		return $items;
	}
	private function tree(&$items=array(),$cc=null,$tablename,$parent_id=null,$spacer='-',$indent=0) {
		$db=DB::Instance();
		if($parent_id==null) {
			$cc->add(new Constraint($this->parent_field,'IS','NULL'));
			$query='SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$tablename . ' WHERE '.$cc->__toString();
			if (!empty($this->orderby))
				$query .= ' ORDER BY '.$this->orderby;
			else
				$query .= ' ORDER BY '.$this->getIdentifier();
			$cc->removeLast();
			$top_items=$db->GetAssoc($query);
			foreach($top_items as $id=>$value) {
				$items[$id]=$value;
				$this->tree($items,$cc,$tablename,$id,$spacer,1);
			}
		}
		else {
			$indents='';
			for($i=0;$i<$indent;$i++)
				$indents.=$spacer;
			$cc->add(new Constraint($this->parent_field,'=',$parent_id));
			$query='SELECT '.$this->idField.', \''.$indents.'\' || '.$this->getIdentifier().	' FROM '.$tablename. ' WHERE '.$cc->__toString();
			if (!empty($this->orderby))
				$query .= ' ORDER BY '.$this->orderby;
			else
				$query .= ' ORDER BY '.$this->getIdentifier();
			$cc->removeLast();
			$sublevel=$db->GetAssoc($query);
			foreach($sublevel as $id=>$value) {
				$items[$id]=$value;
				$this->tree($items,$cc,$tablename,$id,$spacer,$indent+1);
			}
		}
	}
	public function getTopLevel($attribute=null) {
		if($attribute!=null) {
			if(!isset($this->belongsTo[$attribute]))
				throw new Exception('getTopLevel($attribute) must be called for a belongsTo relationship');
			$model=new $this->belongsTo[$attribute]['model'];
			return $model->getTopLevel();
		}
		$db=&DB::Instance();
		$query = 'SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$this->_tablename.' WHERE '.$this->parent_field.' IS NULL';
		return $db->GetAssoc($query);
				
		
	}
	public function getChildren() {
		$db=&DB::Instance();
		$query='SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$this->_tablename.' WHERE '.$this->parent_field.'='.$db->qstr($this->{$this->idField});
		return $db->GetAssoc($query);
	}

	public function getChildrenAsDOC($doc=null,$sh=null) {
		if($doc==null) {
			$doc_name = get_class($this).'Collection';	
			$doc = new $doc_name;
		}
		if($sh==null) {
			$sh = new SearchHandler($doc,false);
		}
		$sh->addConstraint(new Constraint($this->parent_field,'=',$this->{$this->idField}));
		$doc->load($sh);
		return $doc;
	}

	public function getAncestors() {
		$db=&DB::Instance();
		$ancestors=array();
		$parent_id=$this->{$this->parent_field};
		while($parent_id!==false&&!empty($parent_id)) {
			$ancestors[]=$parent_id;
			$query='SELECT parent_id FROM '.$this->_tablename.' WHERE id='.$db->qstr($parent_id);
			$parent_id=$db->GetOne($query);		
		
		}
		return $ancestors;
	}

	function getSiblings($id=null,$attribute=null) {
		if($attribute!=null) {
			$model=new $this->belongsTo[$attribute]['model'];
			return $model->getSiblings($id);
		}
		if($id==null)
			$parent_id=$this->parent_id;
		else
			$parent_id=$id;
		$db=&DB::Instance();
		$query='SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$this->_tablename.' WHERE '.$this->parent_field.(!empty($parent_id)?'='.$db->qstr($parent_id):' IS NULL');
		return $db->GetAssoc($query);
	}

	function getSiblingsAsDOC($id=null,$attribute=null) {
		if($attribute!=null) {
			$model=new $this->belongsTo[$attribute]['model'];
			return $model->getSiblings($id);
		}
		if($id==null) {
			$parent_id=$this->parent_id;
		}
		else {
			$parent_id=$id;
		}		
		$doc = new get_class($this).'Collection';		
		$db=&DB::Instance();
		$query='SELECT '.$this->idField.' FROM '.$this->_tablename.' WHERE '.$this->parent_field.(!empty($parent_id)?'='.$db->qstr($parent_id):' IS NULL');
		$siblings = $db->GetCol($query);
		foreach ($siblings as $sibling) {
			$do = new get_class($this);
			$do->load($sibling);
			$doc->add($do);
		}
	}

	function getIdentifier() {
		return $this->identifierField;
	}


	function getDefaultOrderby() {
		$ob=$this->orderby;
		$candidates=array('position','index','title','name','subject','surname');
		if(empty($ob)) {
			foreach($candidates as $candidate) {
				if($this->isField($candidate)) {
					$ob=$candidate;
					break;
				}
			}
		}
		if(empty($ob))
			$ob=$this->idField;
		$this->orderby=$ob;
		return $this->orderby;
	}

	function getViewName() {
		if(isset($this->_viewname)) {
			return $this->_viewname;
		}
		else {
			return $this->getTableName();
		}
	}

	function setViewName($view)	{
		$this->_viewname = $view;

	}

	function setView() {
		$this->setDisplayFields();
		return $this->_displayFields;
	}

	/**
	 * Makes a field an enumeration
	 *
	 * @param	$field		string	The field to be made into an enumeration
	 * @param	$options	array	An array with a list of options
	 */
	public function setEnum($field,$options) {
		$this->enums[$field]=$options;
	}


	/**
	 * Finds out if a field is an enumeration
	 *
	 * @param	$field		string	The field to be checked
	 * @return	boolean		false if not enum, true if is enum
	 */
	public function isEnum($field) {
		$temp = $this->enums;
		return (isset($temp[$field]));
	}

	/**
	 * Gets a list of enumeration options for a given field
	 *
	 * @param	$field		string	The field for which to fetch options
	 * @return	an array of options
	 */
	public function getEnumOptions($field) {
		return $this->enums[$field];
	}

	/**
	 * Makes a field uneditable once saved
	 *
	 * @param	$field		string	The field to be made into uneditable
	 */
	public function setNotEditable($field) {
		$this->notEditable[$field] = 1;
	}

	/**
	 * Finds out if a field should be protected from editing
	 *
	 * @param	$field		string	The field to be checked
	 * @return	boolean		false if editable, true if not editable
	 */
	public function isNotEditable($field) {
		$temp = $this->notEditable;
		if(isset($temp[$field])) {
			return true;
		}
		return false;
	}

	/**
	 * Hides fields which should be hidden by default
	 */
	public function setDefaultHidden() {
		$this->hidden['id']=1;
		$this->hidden['usercompanyid']=1;
		$this->hidden['created']=1;
		
		$this->setNotSettable('created');
		$this->setNotSettable('lastupdated');
	}

	function setNotSettable($field) {
		$this->not_settable[$field] = true;
	}
	
	function isNotSettable($field) {
		return (isset($this->not_settable[$field]));
	}
	
	/**
	 * Hides a field
	 */
	public function setHidden($field) {
		$this->hidden[$field]=1;
	}

	/**
	 * Finds out if a field is hidden
	 */
	public function isHidden($field) {
		$field = strtolower($field);
		if(isset($this->hidden[$field]) && $this->hidden[$field]===1) {
			return true;
		}
		return false;
	}

	public function isHandled($field) {
		$this->getField($field)->isHandled = true;
	}
	
	
	public function addConfirmationField($fieldname,$tag=null) {
		if($tag==null) {
			$tag='Confirm '.ucwords($fieldname);
		}
		$c_fieldname='confirm_'.$fieldname;
		$ado_field=new ADOFieldObject;
		$ado_field->type='password';
		$ado_field->not_null=1;
		$this->_fields[$c_fieldname]=new DataField($ado_field);
		$this->_fields[$c_fieldname]->tag=$tag;
		$this->setDefaultValidators();
	}
	public function addField($fieldname, $field) {
		$this->_fields[$fieldname] = $field;
	}

	function isHash($fieldname) {
		$this->hashes[$fieldname]=array();
	}
	
	function canDelete() {
		return true;
	}
	
	function canEdit() {
		return true;
	}
	
	/**
	 * For allowing models to act as if they inherit something else
	 * e.g. a store_customer is essentially a person, with a few extra fields
	 * so:
	 * $this->hasComposite('Person','person_id');
	 */
	protected function composites($model_name,$field_name=null) {
		if($field_name==null) {
			$field_name=strtolower($model_name).'_id';
		}
		$this->composites[$field_name]=$model_name;
		
		$model=new $model_name;
		foreach($model->getFields() as $fieldname=>$field) {
			$this->_fields[$fieldname]=$field;
		}
	}

	/**
	* The Iterator functions.
	*
	*/
	public function current() {
		$field =  array_values($this->getDisplayFields());
		$name = $field[$this->_pointer]->name;
		$fields = $this->_fields;
		$field = $fields[$name];
		if($field->type === 'bool') {
			if($field->value == 'f' ) {
				return 'false';
			}
			else {
				return 'true';
			}
		}
		return $field->value;
	}

	public function next() {
		$this->_pointer++;
	}

	public function key() {
		$temp=array_keys($this->_fields);
		return $temp[$this->_pointer];
	}

	public function rewind() {
		$this->_pointer=0;
	}

	public function valid() {
		return ($this->_pointer<count($this->_displayFields));
	}

	public function getId() {
		$idF = $this->idField;
		$field = $this->_fields[$idF];
		return $field->value;
	}

	protected function setAccessControlled($controlled) {
		$this->_accessControlled = $controlled === true ? true : false;
	}

	public function isAccessControlled() {
		return $this->_accessControlled;
	}
	public function toArray() {
		$array=array();
		foreach($this->_fields as $fieldname=>$field) {
			$array[$fieldname]=array(
				'_name'=>$fieldname,
				'type'=>$field->type,
				'value'=>stripslashes($field->value),
				'tag'=>$field->tag
			);
		}
		return $array;
	}
	public function toJSON() {
		if(function_exists('json_encode')) {
			return json_encode($this->toArray());
		}
		return null;
	}
	
	public function get_name() {
		return get_class($this);
	}
}
?>
