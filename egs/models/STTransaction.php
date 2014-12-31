<?php
class STTransaction extends DataObject {

	protected $defaultDisplayFields = array('whstore','whlocation','whbin','stitem','balance');

	function __construct() {
		parent::__construct('st_transactions');
		$this->idField='id';
		
		
 		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 
 		$this->belongsTo('WHStore', 'whstore_id', 'whstore'); 
 		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation'); 
 		$this->belongsTo('WHBin', 'whbin_id', 'whbin'); 

	}

	static function prepareMove($data, &$errors) {
		$item=new STItem();
		$item->load($data['stitem_id']);
		$copyfields=array('cost','std_cost',' std_mat','std_lab','std_ohd');
 		$from=array();
 		$to=array();

		$from['stitem_id']=$to['stitem_id']=$data['stitem_id'];

 		foreach ($copyfields as $field) {
			$to[$field]=$item->$field;
			$from[$field]=$item->$field;
		}

		$from['balance']=-$data['balance'];
		$to['balance']=$data['balance'];
		$loctypes=array('whstore_id','whlocation_id','whbin_id');

		foreach ($loctypes as $type) {
			$from[$type]=$data['from_'.$type];
			$to[$type]=$data['to_'.$type];
		}

		$from_model=DataObject::Factory($from, $errors, 'STTransaction');

		$to_model=DataObject::Factory($to, $errors, 'STTransaction');

		return array('from'=>$from_model, 'to'=>$to_model);

	}

}
?>
