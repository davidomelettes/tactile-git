<?php
class EntityAttachment extends DataObject {
	protected $defaultDisplayFields = array(
		'file'=>'Name',
		'type'=>'Type',
		'size'=>'Size',
		'note'=>'Note'
	);
	
	function __construct() {
		parent::__construct('entity_attachments');
		$this->idField='id';
		
		$this->belongsTo('Entity', 'entity_id');
		$this->belongsTo('File', 'file_id');
		
		$this->setAdditional('type','varchar');
		$this->setAdditional('size','bigint');
		$this->setAdditional('note','varchar');
	}
}
?>