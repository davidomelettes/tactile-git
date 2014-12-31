<?php
/**
 * To be used for 'Recently Viewed' (potentially for each model as well as globally)
 * and favourites and such nonsense.
 */
class PreferencePageList extends PageList {
	function __construct($name,$length=10) {
		$userPreferences = UserPreferences::instance(EGS_USERNAME);
		$this->name=$name;
		$preferencePageList = $userPreferences->getPreferenceValue($this->name, '_pagelists');
		if($preferencePageList !== null)
			$this->queue = $preferencePageList;
		else
			$this->queue = new Queue($length);
	}	
	function save() {
		$userPreferences = UserPreferences::instance(EGS_USERNAME);
		$userPreferences->setPreferenceValue($this->name, '_pagelists', $this->queue);
	}
}
?>