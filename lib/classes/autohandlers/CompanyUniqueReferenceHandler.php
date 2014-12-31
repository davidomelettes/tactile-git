<?php

class CompanyUniqueReferenceHandler extends AutoHandler {
	private $table;
	private $field;
	
	function __construct($table, $field) {
		$this->table = $table;
		$this->field = $field;
	}
	function handle(DataObject $model) {
		$field = $model->{$this->field};
		if(empty($field)) {
			$db=DB::Instance();
			$query='SELECT max(' . $this->field . ') FROM ' . $this->table . ' WHERE usercompanyid='.EGS_COMPANY_ID;
			$current=$db->GetOne($query);
			return $current+1;
		}
	}
}
?>
