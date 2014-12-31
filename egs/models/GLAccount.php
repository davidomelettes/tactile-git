<?php
class GLAccount extends DataObject {
	protected $defaultDisplayFields=array('account'=>'Account','description'=>'Description','actype'=>'Actype','control'=>'control','analysis'=>'analysis');

	function __construct() {
 	      parent::__construct('glmaster');
 		  $this->identifierField = 'account || \' - \' || description';
		  $this->belongsTo('GLAnalysis','glanalysis_id','analysis');
 	      $this->setEnum('actype',array('P'=>'Profit & Loss','B'=>'Balance Sheet'));   
	      $this->orderby='account';
 	}       
 	               
 	function getIdentifier() {
 	     return 'account || \' - \' || description';
 	}
}
?>
