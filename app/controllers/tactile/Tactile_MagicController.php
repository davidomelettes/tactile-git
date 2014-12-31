<?php
/**
 * Responsible for keeping track of user-preferences with regards to what they see
 * - foldable areas and so on
 *
 */
class Tactile_MagicController extends Controller {

	/**
	 * These will only be called via AJAX, so we want to send back an empty page
	 *
	 * @param unknown_type $module
	 * @param unknown_type $view
	 */
	public function __construct($module=null,$view) {
		parent::__construct($module,$view);
		$this->view->set('layout','empty');
	}
	
	/**
	 * Toggles the saved preference for a user for a particular 'key' of folding area
	 */
	public function save_foldable_preference() {
		$key = $this->_data['key'];
		$val = $this->_data['value'];
		$val = ($val == 'closed') ? 'closed' : 'open';
		$username = CurrentlyLoggedInUser::Instance()->getRawUsername();
		Omelette_Magic::saveChoice($key, $val, $username);
	}

	/**
	 * Special function for changing the welcome-message toggle
	 * - this can only be done from the home page, so sending back there is acceptable
	 */
	public function hide_welcome_message() {
		Omelette_Magic::saveChoice(
			'hide_welcome_message', 
			true, 
			CurrentlyLoggedInUser::Instance()->getRawUsername()
		);
		Omelette_Magic::saveChoice(
			'show_sample_graph', 
			false, 
			CurrentlyLoggedInUser::Instance()->getRawUsername()
		);
		sendTo();
	}
	
	function dismiss_motd() {
		$id = (int) $this->_data['id'];
		Omelette_Magic::saveChoice('dismissed_motd_id', $id, EGS::getUsername());
		
		if (!$this->view->is_json) {
			sendTo();
		}
	}
	
	public function save_timeline_view_preference() {
		$key = 'timeline_view';
		$val = !empty($this->_data['view']) ? $this->_data['view'] : 'list';
		$val = !in_array($val, array('block', 'list')) ? 'list' : $val;
		$username = CurrentlyLoggedInUser::Instance()->getRawUsername();
		Omelette_Magic::saveChoice($key, $val, $username);
	}
	
	public function save_view_view_preference() {
		$key = !empty($this->_data['view']) ? $this->_data['view'] : 'summary_info';
		$key = !in_array($key, array('summary_stats', 'summary_info', 'recent_activity')) ? 'summary_info' : $key;
		$key = 'view_' . $key;
		$val = isset($this->_data['show']) ? $this->_data['show'] : true;
		$val = ($val !== 'false');
		$username = CurrentlyLoggedInUser::Instance()->getRawUsername();
		Omelette_Magic::saveChoice($key, $val, $username);
	}
	
	public function header_image() {
		$this->view->set('layout','empty');
		
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$img = new Gradient(1, 75, $account->getCustomThemeSecondary(), $account->getCustomThemePrimary());
	}
	
	public function save_opportunity_related_contact_preference() {
		$key = 'opportunity_related_contact_type';
		$value = !empty($this->_data['value']) ? $this->_data['value'] : 'organisation';
		$value = in_array($value, array('organisation', 'person')) ? $value : 'organisation';
		Omelette_Magic::saveChoice($key, $value);
	}
	
}
