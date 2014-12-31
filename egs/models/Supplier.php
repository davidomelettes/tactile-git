<?php
class Supplier extends DataObjectWithImage {
	protected $defaultDisplayFields=array('name','description','visible');
	function __construct() {
		parent::__construct('store_suppliers');
		$this->belongsTo('Company');
		$this->hasMany('Product');
		$this->hasOne('File','image','imagefile');
	}
	
}

?>