<?php
class UniquenessValidator implements ModelValidation{
	private $fields=array();
	private $message_stub='%s needs to be unique';
	private $message_stub2='%s needs to be a unique combination';
	/**
	 * Constructor. Takes a fieldname for use when testing
	 * @todo allow passing of an array of fieldnames to be tested in combination
	 */
	function __construct($fields,$message=null) {
		if(!is_array($fields))
			$fields=array($fields);
		$this->fields=$fields;
		if($message!=null) {
			$this->message_stub=$message;
			$this->message_stub2=$message;
		}
	}

	function test(DataObject $do,Array &$errors) {
		$do_name=get_class($do);
		$test_do=new $do_name;
		$values=array();
		foreach($this->fields as $fieldname) {
			$values[]=$do->{$fieldname};
		}
		$test_do->loadBy($this->fields,$values);
		if($test_do->isLoaded() && $test_do->getId() !=$do->getId()) {
			if(count($this->fields)==1)
				$errors[$this->fields[0]]=sprintf($this->message_stub,$do->getField($this->fields[0])->tag);
			else {
				$fieldlist='';
				foreach($this->fields as $fieldname) {
					$fieldlist.=$do->getField($fieldname)->tag.',';
				}
				$fieldlist=substr($fieldlist,0,-1);
				$errors[$this->fields[0]]=sprintf($this->message_stub2,$fieldlist);
			}
			return false;
		}
		return $do;

	}
}
?>
