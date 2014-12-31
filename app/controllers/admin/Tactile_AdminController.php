<?php

class Tactile_AdminController extends Controller {
	function index() {
		//static template
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		$this->view->set('current_plan', $plan);
		$this->view->set('referral_date', Tactile_AccountMagic::getValue('referral_terms_agreed'));
	}
	
}
