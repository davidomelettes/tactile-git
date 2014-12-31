<?php

class Tactile_AppearanceController extends Controller {

	function __construct($module,$view) {
		parent::__construct($module,$view);
		$this->mixes('save_file', 'S3FileHandler', array('TactileAccount', null, 'appearance'));
	}

	public function index() {
		$account = CurrentlyLoggedInUser::getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			Flash::Instance()->addMessage('Themes are only available to non-free accounts. Please upgrade to enable this option.');
			sendTo('account/change_plan');
			return;
		}
		$this->view->set('logo_url', $account->getLogoUrl());
		require_once('Charts/Tactile.php');
		$chart = new Charts_Tactile();
		$chart->graphSample();
		$graph = $chart->getGraph();
		$this->view->set('sample_graph', $graph); 
		
		$this->view->set('primary', Tactile_AccountMagic::getValue('theme_custom_primary', '#0F5E15'));
		$this->view->set('secondary', Tactile_AccountMagic::getValue('theme_custom_secondary', '#569C30'));
	}
	
	public function save_theme() {
		$account = CurrentlyLoggedInUser::getAccount();
		if (empty($this->_data['theme']) || ($account->is_free() && !$account->in_trial())) {
			sendTo('appearance');
			return;
		}
		$theme = $this->_data['theme'];
		switch($theme) {
			case 'red':
			case 'blue':
			case 'grey':
			case 'orange':
			case 'purple':
			case 'custom':
				$account->setTheme($theme);
				break;
			case 'green':
			default:
				$account->setTheme('green');
		}
		sendTo('appearance');
	}
	
	public function save_custom_theme() {
		foreach (array('primary', 'secondary') as $swatch) {
			if (!empty($this->_data[$swatch])) {
				 $key = 'theme_custom_' . $swatch;
				 $value = preg_replace('/[^a-fA-F0-9#]/', '', $this->_data[$swatch]);
				 if (preg_match('/#[a-fA-F0-9]{6}/', $value)) {
				 	$username = CurrentlyLoggedInUser::Instance()->getRawUsername();
				 	Tactile_AccountMagic::saveChoice($key, $value, $username);
				 }
			}
		}
		
		Flash::Instance()->addMessage('Theme adjusted');
		sendto('appearance');
	}
	
	public function delete_logo() {
		$db = DB::Instance();
		$db->StartTrans();
		$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		
		$file = new S3File();
		$file->loadBy('account_id', Omelette::getAccount()->id);
		if ($file->canDelete() === false) {
			Flash::Instance()->addError("You do not have permission to delete your account's logo");
			$db->FailTrans();
		}
		$success = $s3->object->delete($file->object, $file->bucket);
		$file->delete();
		
		if ($success) {
			Flash::Instance()->addMessage('Logo deleted');
		} else {
			Flash::Instance()->addError('Failed to delete logo, please try again');
			$db->FailTrans();
		}
		$db->CompleteTrans();
		sendTo('appearance');
	}
	
}
