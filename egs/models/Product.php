<?php
class Product extends DataObjectWithImage{
	public $image_filename;
	protected $defaultDisplayFields = array('name','productcode','section','shortdescription','visible','price','newproduct');
	function __construct() {
		parent::__construct('store_products');
		$this->idField='id';
		$this->orderby='productcode';
		
 		$this->belongsTo('User', 'alteredby', 'product_alteredby');
 		$this->belongsTo('User', 'owner', 'product_owner');
 		$this->belongsTo('Section', 'section_id', 'section'); 
		$this->belongsTo('Supplier','supplier_id','supplier');
		$this->hasOne('File','image','imagefile');
	}
}
?>
