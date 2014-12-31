<?php
class Newsletter extends DataObject {

	protected $defaultDisplayFields = array('name','newsletter_url','send_at','campaign');

	function __construct() {
		parent::__construct('newsletters');
		$this->idField='id';
		
		
 		$this->belongsTo('Campaign', 'campaign_id', 'campaign');
		$this->hasMany('Newsletterview','views');
		$this->hasMany('Newsletterurlclick','clicks');

	}

	function total_clicks() {
		$db=DB::Instance();
		$query='SELECT count(c.id) FROM newsletter_url_clicks c JOIN newsletter_urls u ON (c.url_id=u.id) WHERE u.newsletter_id='.$db->qstr($this->id);
		return $db->GetOne($query);
	}
	
	function total_views() {
		$db=DB::Instance();
		$query='SELECT count(v.id) FROM newsletter_views v WHERE v.newsletter_id='.$db->qstr($this->id);
		return $db->GetOne($query);
	}
	
	function total_unique_views() {
		$db=DB::Instance();
		$query='SELECT count(DISTINCT person_id) FROM newsletter_views v WHERE v.newsletter_id='.$db->qstr($this->id);
		return $db->GetOne($query);
	}
}
?>
