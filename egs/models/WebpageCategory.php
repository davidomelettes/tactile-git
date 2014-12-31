<?php
class WebpageCategory extends DataObject {

	protected $defaultDisplayFields = array('name'=>'Name','title'=>'Title','visible'=>'Visible');

	function __construct() {
		parent::__construct('webpage_categories');
		$this->idField='id';


 		$this->validateUniquenessOf(array('name','website_id','parent_id'));
 		$this->belongsTo('Website', 'website_id', 'website');
		$this->hasMany('WebpageCategory','webpage_categories');
		$this->hasMany('Webpage','webpages');
		$this->actsAsTree();
	}


}
?>
