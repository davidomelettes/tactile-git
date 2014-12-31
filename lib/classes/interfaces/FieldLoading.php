<?php
/**
 * Classes implementing this will be responsible for deciding the type of DataField used to represent a DB-field
 * @author gj
 */
interface FieldLoading {
	/**
	 * Takes an ADOFieldObject, and optional value, and returns a DataField
	 *
	 * @param ADOFieldObject $field
	 * @param mixed $value
	 * @return DataField
	 */
	public function load($field,$value=null);
}
?>