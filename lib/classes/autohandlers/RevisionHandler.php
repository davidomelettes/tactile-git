<?php
/*
 * Created on 20-Sep-06
 *
 */
 class RevisionHandler extends AutoHandler {

	function handle(DataObject $model) {
		$db=DB::Instance();
		$id=$db->GenID('webpage_revisions_revision_seq');
		return $id;
	}
}
 
?>
