<?php

/**
 *
 */
class TermsController extends Controller {

	public function index() {
		
	}
	
	public function process() {
		if(empty($this->_data['terms_agreed'])) {
			Flash::Instance()->addError("You must agree to the terms & conditions before continuing to use Tactile");
			sendTo('terms');
			return;
		}
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$user->terms_agreed = 'now()';
		$user->save();
		sendTo();
	}
	
}

?>
