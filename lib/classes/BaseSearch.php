<?php
$al=AutoLoader::Instance();
$al->addPath(APP_CLASS_ROOT.'searchfields/');
/**
 * Provides the basic functionality for representing both search fields on a form, 
 * and a ConstraintChain for passing to the database
 */
class BaseSearch {
	private $cleared=false;
	private $groups=array('basic','advanced','hidden');
	protected $fields=array();
	public function __construct() {
		foreach($this->groups as $group) {
			$this->fields[$group] = array();
		}
	}
	
	
	/**
	 * @param $search_data array
	 * @return void
	 *  Takes an array representing (typically) $_POST['Search'] and assigns the values to te appropriate fields
	 */
	public function setSearchData($search_data=null,&$errors) {
		if(!empty($search_data['clear'])) {
			if(isset($_SESSION['searches'][get_class($this)])) {
				unset($_SESSION['searches'][get_class($this)]);
			}
			return;
		}
		if($search_data!==null&&count($search_data)==0&&isset($_SESSION['searches'][get_class($this)])) {
			$search_data = $_SESSION['searches'][get_class($this)];
		}
		if($search_data!==null&&count($search_data)>0) {
			foreach($this->fields as $group) {
				foreach($group as $fieldname=>$searchField) {
					if(isset($search_data[$fieldname])) {
						if($searchField->isValid($search_data[$fieldname],$errors)) {
							$searchField->setValue($search_data[$fieldname]);
						}
					}
					else {
						$searchField->setValue(null);
					}
				}
			}
			if(count($errors)==0) {
				$_SESSION['searches'][get_class($this)]=$search_data;
			}
		}
		
	}
	
	/**
	 * @param $fieldname string
	 * [ @param $label string ] is defaulted to prettify($fieldname) when requested
	 * [ @param $type string ] defaults to 'contains'
	 * [ @param $default mixed ] is defaulted to '' (subclasses have option to over-ride the default default...)
	 * @return boolean
	 * @see SearchField::Factory
	 * Adds a searchfield to the search, constructed with the given paramaters
	 */
	public function addSearchField($fieldname,$label=null,$type="contains",$default=null,$group='basic') {
		$this->testGroup($group);
		$field = SearchField::Factory($fieldname,$label,$type,$default);
		return $this->addField($fieldname,$group,$field);
	}
	
	
	/**
	 * @param $fieldname string
	 *[ @param $groupname string ]
	 * @return boolean
	 *
	 * Removes the searchfield attached to the given fieldname.
	 * If a groupname is given, then the removal is quicker
	 */
	public function removeSearchField($fieldname,$groupname=null) {
		if($groupname!==null) {
			if(isset($this->fields[$groupname][$fieldname])) {
				unset($this->fields[$groupname][$fieldname]);
				return true;
			}
			else {
				throw new Exception('Tried to remove field from group that doesn\'t exist: '.$fieldname.' from '.$groupname);
				return false;
			}
		}
		else {
			foreach($this->groups as $groupname) {
				if(isset($this->fields[$groupname][$fieldname])) {
					unset($this->fields[$groupname][$fieldname]);
					return true;
				}
			}
			throw new Exception('Tried to remove field that doesn\'t exist: '.$fieldname);
			return false;
		}
	}
	
	/**
	 * @param $groupname string
	 * @return boolean
	 *
	 * Tests the given $groupname against the list of allowable groupnames
	 */
	private function testGroup($groupname) {
		if(!in_array($groupname,$this->groups)) {
			throw new Exception('$group should be either "basic" or "advanced"');
		}
	}
	
	/**
	 * @param $fieldname string
	 * @param $field SearchField
	 *
	 * Add an already made field to the Search
	 */
	public function addField($fieldname,$group,SearchField $field) {
		$this->testGroup($group);
		$this->fields[$group][$fieldname]=$field;
	}
	
	/**
	 * @param $fieldname string
	 * @param $value mixed
	 * @return void
	 *
	 * Set the value of a field within the search
	 * @see SearchField::setValue()
	 */
	protected function setValue($fieldname,$value) {
		if(isset($this->fields[$fieldname])) {
			$this->fields[$fieldname]->setValue($value);
		}
	}
	
	/**
	 * @param $fieldname
	 * @param $value
	 * @return boolean
	 *
	 * For CheckboxSearchFields (and anything similar) that are representing an equality,
	 * rather than a boolean field, e.g. 'My Tickets' checks for person_id=12 c.f. completed=true
	 *
	 */
	protected function setOnValue($fieldname,$value) {
		foreach($this->fields as $i=>$group) {
			if(isset($group[$fieldname])) {
				$this->fields[$i][$fieldname]->setOnValue($value);
				return true;
			}
		}
		throw new Exception('Fieldname not found: '.$fieldname);
		return false;
	}
	
	protected function setOffValue($fieldname,$value) {
		foreach($this->fields as $i=>$group) {
			if(isset($group[$fieldname])) {
				$this->fields[$i][$fieldname]->setOffValue($value);
				return true;
			}
		}
		throw new Exception('Fieldname not found: '.$fieldname);
		return false;
	}
	
	/**
	 * @param $options array
	 * @return boolean
	 *
	 * Allows the setting of options for a Select-type searchfield (or any other type that wants to accept options)
	 */
	protected function setOptions($fieldname,$options) {
		foreach($this->fields as $i=>$group) {
			if(isset($group[$fieldname])) {
				$this->fields[$i][$fieldname]->setOptions($options);
				return true;
			}
		}
		throw new Exception('Fieldname not found: '.$fieldname);
		return false;
	}
	/**
	 * @param $fieldname string
	 * @param $constraint Constraint(Chain)
	 * 
	 * Attach a constrant to the specified field. This allows for the altering of the constraints built by the SearchFields
	 * (if the field knows what to do with a different constraint- only 'hide' checkboxes do at the moment)
	 */
	public function setConstraint($fieldname,$constraint) {
		foreach($this->fields as $i=>$group) {
			if(isset($group[$fieldname])) {
				$this->fields[$i][$fieldname]->setConstraint($constraint);
			}
		}
	}
	
	/**
	 * @param void
	 * @return string
	 *
	 * Returns an HTML string representing the series of form fields and labels
	 * @see SearchField::toHTML()
	 */
	public function toHTML($group='basic') {
		$this->testGroup($group);
		$html='';
		foreach($this->fields[$group] as $searchField) {
			$html.=$searchField->toHTML().'<br />';
		}
		return $html;
	}
	
	/**
	 * @param $groupname string
	 * @return boolean
	 * 
	 * Returns true if the groupname specified has any searchfields
	 */
	public function hasFields($groupname) {
		return (count($this->fields[$groupname])>0);
	}
	
	/**
	 * @param void
	 * @return ConstraintChain
	 *
	 * Returns a constraint chain representing the search in it's current state
	 * This takes into account both selected and default values, as appropriate
	 * @see SearchField::toConstraint()
	 */
	public function toConstraintChain() {
		$cc = new ConstraintChain();
		if($this->cleared) {
			return $cc;
		}
		
		foreach($this->fields as $group) {
			foreach($group as $searchField) {
				$c = $searchField->toConstraint();
				if($c!==false) {
					$cc->add($c);
				}
			}
		}
		return $cc;
	}
	
	public function clear() {
		$this->cleared=true;
	}
	
}
?>