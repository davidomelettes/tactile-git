<?php
class WebsiteAdmin extends DataObject {

	protected $defaultDisplayFields=array('website'=>'Website','username'=>'Username');
	function __construct() {
		parent::__construct('website_admins');

 		$this->belongsTo('Website', 'website_id', 'website');
 		$this->belongsTo('User', 'username', 'username_id');

	}


}
?>
