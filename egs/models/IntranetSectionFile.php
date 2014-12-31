<?php
class IntranetSectionFile extends DataObject {

	function __construct() {
		parent::__construct('intranet_section_files');
		$this->idField='id';
		$this->identifierField='file';

 		$this->belongsTo('File', 'file_id', 'file');
  		$this->belongsTo('Section', 'intranetsection_id', 'intranetsection'); 

	}


}
?>
