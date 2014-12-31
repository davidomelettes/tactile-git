<?php
/**
 * I think this is necessary as more is needed from DB-fields than ADOFieldObject allows for
 * but extending it wouldn't work as that would require changing bits of adodb code, and we don't want to do that.
 * Is this maybe a decorator?
 */
class DataField {

	/**
	 *@private	ADOFieldObject	The field object that the DataField is encapsulating
	 */
	public $_field;
	public $isHandled = false;
	private $defaults_set=false;
	private $formatters_set=false;
	private $default_callback=false;
	private $hasValidators=false;
	private $_blocked_validators=array();
	/**
	 * An array containing the Validators associated with the field
	 * @private	FieldValidation[]	An array of Objects that implement FieldValidation
	 */
	public $_validators=array();
	private $_formatter=null;

//	protected $value;
	/**
	 * Constructor
	 * Turns an ADOFieldObject into a more useful Object
	 * (ADOFieldObject currently (2006/07/03) doesn't have any functions, but will need to probably check future versions don't and think about __calling them...)
	 *
	 * @param	ADOFieldObject	An initialised ADOFieldObject
	 * @see ADOFieldObject
	 */
	public function __construct($field,$value=null) {
		if($field instanceof ADOFieldObject) {
			$this->_field=$field;
			if(!isset($field->value))
				$this->_field->value=null;
		}
		else {
			$this->_field=new ADOFieldObject();
			$this->_field->name=$field;
			$this->_field->value=$value;
		}
		$files=array('image','thumbnail','file_id','fileid');
		if(in_array($this->name,$files)) {
			$this->type='file';
		}
		//$this->setDefaultFormatters();
		//$this->setDefaultValues();
		$ignore=array('created','lastupdated','alteredby','usercompanyid');
		if(in_array($this->name,$ignore)) {
			$this->type='ignore';
		}
		if(!empty($field->type) && $field->type == 'yearperiod') {
			$this->max_length='5';
		}
	}

	public static function Construct($field,$value=null) {
		global $injector;
		try {
			$field_loader = $injector->instantiate('FieldLoading');
			$field = $field_loader->load($field,$value);
		}
		catch(PhemtoException $e) {
			$field = new DataField($field,$value);
		}
		return $field;
	}

	/**
	 * Register a Validator against the field.
	 * Passed argument must be an object that implements FieldValidation
	 *
	 */
	public function addValidator(FieldValidation $validator) {
		if(!in_array(get_class($validator),$this->_blocked_validators)) {
			$this->_validators[]=$validator;
			$this->blockValidator(get_class($validator));
		}
	}
	
	/**
	 * It's sometimes useful to be able to say 'never apply this validator' to a field
	 * (when validators are applied as 'defaults' elsewhere)
	 * @param String $validator_name
	 * @return void
	 */
	public function blockValidator($validator_name) {
		$this->_blocked_validators[]=$validator_name;
	}

	/**
	 * Sets up FieldValidators for the field based on it's DB-properties
	 *
	 * @todo	Probably some more things could go here? (date-types for example, cna compulsory fields!...)
	 * @return void
	 */
	private function setDefaultValidators() {
		global $injector;
		if($this->type=='bool') {
			$this->addValidator(new BooleanValidator());
		}
		if(!empty($this->_field->not_null)&&$this->_field->not_null == 1){
			$this->addValidator(new PresenceValidator());
		}
		if($this->type=='date'||$this->type=='timestamp') {
			try {
				$date_validator = $injector->instantiate('DateValidation');
			}
			catch(PhemtoException $e) {
				$date_validator = new DateValidator();
			}
			$this->addValidator($date_validator);
			$this->is_date=true;
		}
		if(substr($this->type,0,3)=='int' || $this->type=='rate' || $this->type=='numeric' || $this->type=='glref') {
			$this->type='numeric';
			$this->addValidator(new NumericValidator());
		}
		if($this->name =='password') {
			try {
				$validator = $injector->instantiate('PasswordValidator');
				$this->addValidator($validator);
			}
			catch(PhemtoException $e) {
				$this->addValidator(new PasswordValidator());
			}
		}
	}

	/**
	 * Sets the default formatter for the field based on its type or name
	 * 
	 * @return void
	 */
	private function setDefaultFormatters() {
		$this->_formatter = new DefaultFormatter();
		global $injector;
		if ($this->type=='date') {
			try {
				$this->_formatter = $injector->instantiate('DateFieldFormatter');
			}
			catch(PhemtoException $e) {
				$this->_formatter = new DateFormatter();
			}
		}
		if ($this->type=='timestamp'||$this->name=='created'||$this->name=='lastupdated') {
			try {
				$this->_formatter = $injector->instantiate('TimestampFieldFormatter');
			}
			catch(PhemtoException $e) {
				$this->_formatter = new TimestampFormatter();
			}
		}
		if ($this->type=='bool') {
			try {
				$this->_formatter = $injector->instantiate('BooleanFormatter');
			}
			catch(PhemtoException $e) {
				$this->_formatter = new BooleanFormatter();
			}
		}
		
		if (substr($this->name,-5)=='price') {
			$this->_formatter = new PriceFormatter();
		}
		$this->formatters_set=true;
	}

	public function formatted() {
		return $this->formatted;
	}

	/**
	 * Assign a formatter for the field
	 * 
	 * Additionally, stops any default formatters being applied
	 * @param FieldFormatter $formatter
	 * @return void
	 */
	public function setFormatter(FieldFormatter $formatter) {
		$this->_formatter = $formatter;
		$this->formatters_set=true;
	}	

	/**
	 * For defaults that aren't static
	 *
	 */
	private function setDefaultValues() {
			$db=&DB::Instance();
			switch($this->name) {
				case 'owner' :
					$this->has_default=true;
					if (defined('EGS_USERNAME'))
						$this->default_value=EGS_USERNAME;
					break;
				case 'language_code' :
					$this->has_default=true;
					if (defined('EGS_USERNAME')) {
						$query='SELECT language_code FROM people p JOIN users u ON (p.id=u.person_id) WHERE u.username='.$db->qstr(EGS_USERNAME);
						$this->default_value=$db->CacheGetOne($query);
					}
					break;
				/*case 'country_code' :
					$this->has_default=true;
					if(defined('EGS_USERNAME')) {
						$query='SELECT country_code FROM people pa JOIN users u ON (pa.id=u.person_id)'.
								'WHERE u.username='.$db->qstr(EGS_USERNAME);
						$country=$db->GetOne($query);
						if($country===false) {	
								$query='SELECT country_code FROM users u JOIN people p ON (p.id=u.person_id) JOIN organisations ca ON (p.organisation_id=ca.id) '.
								'WHERE u.username='.$db->qstr(EGS_USERNAME);
								$country=$db->GetOne($query);
						}
					}
					if(!isset($country)) {
						$country='GB';
					}
					$this->default_value=$country;
					break;*/
				case 'assigned' :
				case 'assigned_to':
					if (defined('EGS_USERNAME')) {
						$this->has_default=true;
						$this->default_value=EGS_USERNAME;
					}
			}
			if($this->default_callback!==false) {
				$this->default_value=call_user_func($this->default_callback,$this);
				$this->has_default=true;
			}
			$this->defaults_set = true;
	}

	public function dropDefault() {
		$this->has_default = false;
		$this->default_value = null;
		$this->defaults_set = true;
	}


	public function setDefaultCallback($callback) {
		$this->default_callback=$callback;
	}


	/**
	 * Tests a field for being valid against its validators
	 * @param	&$errors	An array passed-by-reference that has error messages put into it
	 * @param	$extra		Additional parameter for TimeValidator (this is a hack for Activities)
	 * @return	mixed		boolean-false on failure,
	 */
	public function test($value,&$errors=array(), $extra=null) {
		if(!$this->hasValidators) {
			$this->setDefaultValidators();
			$this->hasValidators=true;
		}
		if(count($this->_validators)==0)
			return $value;
		$this->value=$value;
		foreach($this->_validators as $validator) {
			if (!is_null($extra) && $validator instanceof TimeValidator) {
				$this->value=$validator->test($this, $errors, $extra);
			} else {
				$this->value=$validator->test($this,$errors);
			}
		}
		if(count($errors)>0) {
			return false;
		}
		return $this->finalvalue;
	}

	/**
	 * Formats date according to user preference
	 *
	 *@param	string	Date in format yyyy-mm-dd
	 *@return	string	Date in user preferred format
	 */
	public function formatDate($date) {
		$timestamp = mktime(0,0,0,substr($date,5,2),substr($date,8,2),substr($date,0,4));
		$formatted = date(DATE_FORMAT,$timestamp);
		return $formatted;
	}

	/**
	 * Allows for the getting of the ADOFieldObject's properties
	 *
	 *@param	string	The name of the property
	 *@return	mixed	The value of the ADOFieldObject's corresponding property
	 */
	public function __get($var) {
		if (substr($var,0,9) == 'formatted') {
			if(!$this->formatters_set) {
				$this->setDefaultFormatters();
			}
			if (isset($this->_formatter)) {
				return $this->_formatter->format($this->_field->value);
			}
			else {
				$var = 'value';
			}
		}
		if($var=='is_safe') {
			if(isset($this->_formatter)) {
				return ($this->_formatter->is_safe===true);
			}
			return false;
		}
		if(($var=='has_default'||$var=='default_value')&&$this->defaults_set==false) {
			$this->setDefaultValues();
		}
		if($var=='tag') {
			$tag = '';
			if(isset($this->_field->tag)) {
				$tag=$this->_field->tag;
			}
			if(empty($tag)) {
				$name=$this->name;
				$this->tag=prettify($name);
			}
		}
	//	if($var=='value'&&$this->name=='size') {
	//		return sizify($this->_field->value);
	//	}
		if($var=='default_value'&&$this->_field->has_default && !empty($this->_field->default_value) && ($this->_field->default_value=='now()') ) {
				return date('Y-m-d H:i:s');
		}
		if($this->_field->type=='interval'&&$var=='default_value') {
			if($this->_field->default_value=="'00:00:00'::interval") {
				return array(0,'hours');
			}
		}
		if(isset($this->_field->$var))
		{
			return $this->_field->$var;
		}
		if($var == 'finalvalue')
		{
			$thevalue=$this->_field->value;
			if(isset($thevalue))
				return $thevalue;
			else
				return null;
		}


	}
	public function setDefault($value) {
		$this->has_default=1;
		$this->default_value=$value;
	}
	public function setnotnull(){
		$this->not_null = true;
	}

	public function dropnotnull(){
		$this->not_null = false;
	}

	public function clearValue() {
		$this->value = "";
	}

	public function __set($var, $val){
		$this->_field->$var = $val;
	}
	function __clone() {
		$this->_field=clone($this->_field);
	}
}
?>
