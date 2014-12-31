<?php
class PollOption extends DataObjectWithImage {
	protected $defaultDisplayFields=array('name','poll','created');
	function __construct() {
		parent::__construct('poll_options');
		$this->belongsTo('Poll');
		$this->hasMany('PollVote','votes','option_id');
	}
	
	function votes() {
		$db = DB::Instance();
		$query = 'SELECT count(id) FROM poll_votes WHERE option_id='.$db->qstr($this->id);
		$total = $db->GetOne($query);
		return $total;
	}
	
	function canVote() {
		return empty($_COOKIE['has_voted'.$this->poll_id]);
	}
}
?>
