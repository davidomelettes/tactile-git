<?php
/**
 * Responsible for validating a model that contains date-type fields that should be in a particular order
 * @author gj
 * @package Validators
 */
class StartBeforeEndValidator implements ModelValidation {
	
	private $message_stub = '%s must be earlier than %s';
	
	private $start_field;
	private $end_field;
	
	/**
	 * Constructor
	 * Takes $start and $end as fieldnames to compare, and an optional error message to use in place of the default
	 * @param String $start
	 * @param String $end
	 * @param String [$message]
	 */
	public function __construct($start,$end,$message=null) {
		
		$this->start_field = $start;
		$this->end_field = $end;
		
		if($message!==null) {
			$this->message_stub = $message;
		}
	}
	
	/**
	 * Returns false iff value of the field set as 'start' is later than the value of the field set as 'end'
	 * @param DataObject $do
	 * @param Array &$errors
	 * @return DataObject|Boolean
	 */
	public function test(DataObject $do,Array &$errors) {
		$start = $do->{$this->start_field};
		$end = $do->{$this->end_field};
		if(!empty($start) && !empty($end) && strtotime($end)<=strtotime($start)) {
			$start_tag = $do->getField($this->start_field)->tag;
			$end_tag = $do->getField($this->end_field)->tag;
			$errors[$this->start_field] = sprintf($this->message_stub,$start_tag,$end_tag);
			return false;
		}
		return $do;
	}
	
}
?>