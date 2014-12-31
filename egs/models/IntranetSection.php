<?php
class IntranetSection extends DataObject {

	function __construct() {
		parent::__construct('intranet_sections');
		$this->idField='id';
		$this->identifierField = 'title';
		
		$this->hasMany('IntranetPage');
	}

	function getAllFiles() {
		$db = DB::Instance();
		$query = 'select file_id from intranet_section_files where intranetsection_id='.$this->id;
		$files = $db->GetCol($query);
		if ($files) {	
			foreach($files as $file) {
				$fileObj = new File(FILE_ROOT.'data/tmp/');
				$fileObj->load($file);
				$fileObj->Pull();
			}
		}
	}

}
?>
