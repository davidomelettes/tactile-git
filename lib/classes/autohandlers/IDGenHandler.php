<?php

class IDGenHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db=DB::Instance();
		$id=$db->GenID($model->tablename.'_id_seq');
		return $id;
	}
}
?>
