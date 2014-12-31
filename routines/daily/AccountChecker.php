<?php

/**
 *
 */
class AccountChecker extends EGSCLIApplication {

	/**
	 * The number if failed payments that will be allowed before we stop re-trying
	 *
	 */
	const NUM_TRIES = 3;

	/**
	 * The number of accounts extended during the check
	 *
	 * @var Integer
	 */
	protected $num_extended = 0;


	public function go() {
		// Grab all accounts that have expired
		AutoLoader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');
		$accounts = $this->loadExpiringAccounts();
		$this->logger->info("There are ".count($accounts)." expired accounts");
		$db = DB::Instance();

		foreach($accounts as $account) {
				
			$db->StartTrans();
				
			/* @var $account TactileAccount */
			$account;
			EGS::setCompanyId($account->organisation_id);
			Omelette::setAccount($account);
			Omelette::setAccountPlan($account->current_plan_id);
			$plan = $account->getPlan();
				
			// Extend free accounts automatically	
			if ($plan->is_free()) {
				$this->logger->info("Extending free account for ".$account->company.' ('.$account->id.')');
				
				$account->extend();
				$this->num_extended++;
			
			// Take payment for non-free accounts 
			} else {
				// We must have a previous payment
				if ($plan->is_per_user()) {
					$payment = $account->getLatestRepeatablePayment();
				} else {
					$payment = $account->getLatestPayment();
				}
				
				if ($payment === false) {
					// This is very bad, OR the result of someone fudging an account
					$this->logger->debug("No payment to release or repeat for ".$account->company.'('.$account->id.')');
					$this->handleNoPayment($account);
					
				} elseif (!$payment->isAuthorised()) {
					// Un-released, deferred payments have authorised == false
					$this->logger->info("Release needed for ".$account->company.' ('.$account->id.') (payment-record:  '.$payment->id.')');
					$this->logger->debug('Setting test status: ' . $this->config['test']);
					
					$release = new SecPayRelease(SECPAY_MID,SECPAY_VPN_PASSWORD);
					$release->setTransId($payment->trans_id);
					$release->setRemotePswd(SECPAY_REMOTE);
					$release->setNewTransId('RELEASE'.date('YmdHis'));
					
					if ($plan->is_per_user()) {
						$amount = $plan->cost_per_month * $account->per_user_limit;
					} else {
						$amount = $plan->cost_per_month;
					}
					$release->setAmount($amount);
					
					$response = $release->send();
					$this->logger->debug($release->getRawRequest()->saveXML());
					
					if ($response === false) {
						$this->logger->info("Request Failed horribly for trans_id " . $payment->trans_id);
					}
					
					if ($response->isValid() && $response->isSuccessful()) {
						// Successfully released payment
						$payment->authorised = 't';
						try {
							$payment->save();
							if ($plan->is_per_user()) {
								$description = "{$account->per_user_limit} {$plan->name} Users @ {$plan->cost_per_month} (30 Days)";
							} else {
								$description = "{$plan->name} (30 Days)";
							}
							$payment->generateReleaseRecord($response, $release->getValue('amount'), $description);
							$account->extend(30, $account->account_expires);
							$this->logger->info("Extended account for ".$account->company.'('.$account->id.')');
							$this->num_extended++;
						}
						catch (Exception $e) {
							$this->logger->crit("Error Saving Release Payment Record or extending account: " . $e->getMessage());
						}
					} else {
						$this->logger->warn("Response invalid or not successful");
						$this->logger->warn(print_r($response,true));
						$this->handleFailedRelease($response, $payment, $account);
					}
					
				} else {				
					// Nothing to release, so create a repeat payment
					$this->logger->info("Repeat needed for ".$account->company.' ('.$account->id.')');
					$this->logger->debug('Setting test status: ' . $this->config['test']);
					
					$repeat = new SecPayRepeat(SECPAY_MID,SECPAY_VPN_PASSWORD);
					$repeat->setTest($this->config['test']);
					$repeat->setTransId($payment->trans_id);
					$repeat->setRemotePswd(SECPAY_REMOTE);
					$repeat->setNewTransId('REPEAT'.$payment->id.'-'.date('YmdHis'));
					$repeat->setCardExpiry($payment->card_expiry);
						
					if ($plan->is_per_user()) {
						$amount = $plan->cost_per_month * $account->per_user_limit;
					} else {
						$amount = $plan->cost_per_month;
					}
					$repeat->setAmount($amount);
					
					$response = $repeat->send();
					$this->logger->debug($repeat->getRawRequest()->saveXML());

					if ($response === false) {
						$this->logger->info("Request Failed horribly for trans_id ".$payment->trans_id);
					}
						
					if ($response->isValid() && $response->isSuccessful()) {
						// Successfully made a repeat payment
						try {
							$account->extend(30, $account->account_expires);
							if ($plan->is_per_user()) {
								$description = "{$account->per_user_limit} {$plan->name} Users @ {$plan->cost_per_month} (30 Days)";
							} else {
								$description = "{$plan->name} (30 Days)";
							}
							$payment->generateRepeatRecord($response, $repeat->getValue('amount'), $description);
							$this->logger->info("Extended account for ".$account->company.'('.$account->id.') from '.$account->account_expires);
							$this->num_extended++;
						}
						catch (Exception $e) {
							$this->logger->crit("Error Saving Release Payment Record or extending account: " . $e->getMessage());
						}
					} else {
						$this->logger->warn("Response invalid or not successful");
						$this->logger->warn(print_r($response,true));
						$this->handleFailedRepeat($response, $payment, $account);
					}
				}
			}
			$db->CompleteTrans();
		}
	}

	/**
	 * Load a collection of all accounts that expire today or earlier
	 *
	 * @return TactileAccountCollection
	 */
	protected function loadExpiringAccounts() {
		$accounts = new TactileAccountCollection();
		$sh = new SearchHandler($accounts,false);
		$sh->addConstraint(new Constraint('enabled','=',true));
		$sh->addConstraint(new Constraint('account_expires','>=','2011-11-20'));
		$sh->setLimit(1000);
		$sh->setOrderBy('account_expires');
		$sh->addConstraint(new Constraint('account_expires','<=',Constraint::TODAY));
		$accounts->load($sh);
		return $accounts;
	}

	/**
	 * Performs the series of actions that need to happen when we come across a failed payment:
	 *
	 * @param SecPayResponse $response
	 * @param PaymentRecord $payment
	 * @param TactileAccount $account
	 */
	protected function handleFailedRepeat($response, $payment, $account) {
		//we want to log the failed attempt
		$payment->generateFailedRepeatRecord($response);

		$previous = $payment->getRepeatAttempts();

		switch(count($previous)) {
			case 1:	//if this is the first failure, notify but leave open
			case 2:
				$this->logger->info('Notifying owner of failed payment: '.$account->company.'('.$account->email.', '.$account->id.')');
				$account->notifyOwnerOfFailedPayment();
				break;
			case 3:
				//then suspend the account (suspending notifies the owner)
				$this->logger->info('Disabling account: '.$account->company.'('.$account->id.')');
				$account->notifyOwnerOfSuspension();
				$account->disable();
				break;
		}
	}

	/**
	 * Performs the series of actions that need to happen when we come across a failed release payment:
	 *
	 * @param SecPayResponse $response
	 * @param PaymentRecord $payment
	 * @param TactileAccount $account
	 */
	protected function handleFailedRelease($response, $payment, $account) {
		//we want to log the failed attempt
		$payment->generateFailedReleaseRecord($response);

		$previous = $payment->getReleaseAttempts();

		switch(count($previous)) {
			case 1:	//if this is the first failure, notify but leave open
			case 2:
				$this->logger->info('Notifying owner of failed payment: '.$account->company.'('.$account->email.', '.$account->id.')');
				$account->notifyOwnerOfFailedPayment();
				break;
			case 3:
				//then suspend the account (suspending notifies the owner)
				$this->logger->info('Disabling account: '.$account->company.'('.$account->id.')');
				$account->notifyOwnerOfSuspension();
				$account->disable();
				break;
		}
	}
	
	protected function handleNoPayment($account) {
		$false_payment = new PaymentRecord();
		$false_payment->account_id = $account->id;
		
		// Give us something to work with next time
		// It will fail too but switch to using handleFailedRepeat() 
		$false_payment->generateFalseRecord();
		
		// Send notification of failed payment
		$account->notifyOwnerOfFailedPayment();
	}

}
