<?php

/**
 *
 */
class SuspensionController extends Controller {

	/**
	 * 
	 */
	function __construct($module=null,$view) {
		parent::__construct($module,$view);
	}
	
	function index() {
		
	}
	
	function take_payment() {
		$this->view->set('previous',$this->restoreData());
		unset($_SESSION['_controller_data']);
	}
	
	function process_payment() {
		$db = DB::Instance();
		$db->StartTrans();
		
		$user = CurrentlyLoggedInUser::Instance();
		if(!$user->isAccountOwner()) {
			sendTo('suspension');
			return;
		}
		$account = $user->getAccount();
		
		$plan = new AccountPlan();
		$plan->load($account->current_plan_id);
		
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'payment/');
		//then check the card bits are good
		$card_details = $this->_data['Card'];
		if(!empty($this->_data['Card']['card_number'])) {
			$this->_data['Card']['card_number'] = '*';
		}
		if(!empty($this->_data['Card']['cv2'])) {
			$this->_data['Card']['cv2'] = '*';
		}
		$card = new PaymentCard($card_details);
		if(!$card->isValid()) {
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($card->getErrors());
			$this->saveData();
			sendTo('suspension','take_payment');
			return;
		}
		
		//only get here if account+card seem ok
		
		//then we build the request that will go to Secpay
		$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
		
		$trans_id = 'Tactile'.date('Ymdhis');
		$request->addPaymentCard($card);
		$request->setTransId($trans_id);
		$request->setCustomerIP(!empty($_SERVER['X_FORWARDED_FOR'])?$_SERVER['X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
		
		$paymentAmount = $plan->cost_per_month;
		if ($plan->is_per_user()) {
			$paymentAmount = $plan->cost_per_month * $account->per_user_limit;
		}
		$request->setAmount($paymentAmount);
		
		$request->setTest(SECPAY_TEST_STATUS);
		
		//this needs to be done after other things!!!
		$request->setDigest(SECPAY_REMOTE);
		//then make the request, we get back a SecPaySyncResponse()
		$response = $request->send();
		
		//checks that there are enough fields sent back
		if(!$response->isValid()) {
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			$this->saveData();
			sendTo('suspension','take_payment');
			return;
		}
		
		//then check that the response is 'valid' and with a good code, generates errors for user
		if(!$response->isSuccessful()) {
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			$this->saveData();
			sendTo('suspension','take_payment');
			return;
		}
		$errors = array();
		$record_data = array(
			'account_id'=>$account->id,
			'amount'=>$paymentAmount,
			'authorised'=>true,
			'auth_code'=>$response->getAuthCode(),
			'test_status'=>$response->getTestStatus(),
			'card_no'=>substr($card->getCardNumber(),-5),
			'card_expiry'=>$card->getExpiry(),
			'cardholder_name'=>$card->getCardholderName(),
			'trans_id'=>$trans_id,
			'type'=>PaymentRecord::TYPE_FULL
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			$db->FailTrans();
			$db->CompleteTrans();
		}
		
		$account->enable();
		$account->unsuspend();
		$account->extend();
		Flash::Instance()->addMessage('Payment taken successfully, your account expires in 30 days');
		$db->CompleteTrans();
		sendTo();
		return;
	}
}

?>
