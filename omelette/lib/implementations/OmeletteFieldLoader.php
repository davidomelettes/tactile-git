<?php
class OmeletteFieldLoader implements FieldLoading {
	public function load($field,$value=null) {
		switch($field->name) {
			case 'assigned':
			case 'owner':
			case 'alteredby':
			case 'username':
			case 'assigned_to':
			case 'assigned_by':
				$field = new UsernameField($field,$value);
				break;
			default: 
				switch($field->type) {
					default:
						$field = new DataField($field,$value);
				}
		}
		
		return $field;
	}	
}

?>
