<?php
/* Author: Jake */ 

class SyncNewAccounts extends EGSCLIApplication {
	
	// These are domains we don't want to try and guess the website for
	private $shared_domains = array('gmail', 'yahoo', 'hotmail', 'mac', 'me', 'aol', 'googlemail', 'comcast', 'mailinator', 'btinternet', 'live', 'msn');
	
	public function go() {
		$db = DB::Instance();
		require_once 'Zend/Log.php';
		
		// First up we get new unsynced accounts
		$query = "SELECT *, lower(substring(email from '@(.+)')) AS domain FROM tactile_accounts WHERE synced=false AND created=lastupdated";

		$accounts = $db->getArray($query);

		// Invoicing is 2 stage, we'll add new contacts, then start again and do actual invoices
		foreach ($accounts as $account)	{
			try {
				require_once 'Tactile/Api.php';
				require_once 'Tactile/Api/Organisation.php';
				if(PRODUCTION) {
					$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY);
				} else {
					$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY, null, TACTILE_API_TEST_DOMAIN);
				}
					
				$org = new Tactile_Api_Organisation();
				$org->accountnumber = $account['site_address'];	
				$org->name = ucfirst($account['company']);
				//$org->vatnumber=$account['vat_number'];
				$org->country_code = $account['country_code'];
				$org->type_id = 4328;

				if($account['signup_code'] == '') {
					$org->source_id  = 2714;
				} else if($account['signup_code'] == 'iphone') {
					$org->source_id = 9929;
				} else {
					$org->source_id = 2716;
				}
				
				// We set the status id in Tactile to their plan equivilent
				if($account['current_plan_id'] == '10') {
					// Solo
					$org->status_id = 12938;
					$account['plan'] = 'Solo';
				} else if($account['current_plan_id'] == '11') {
					// Premium
					$org->status_id = 12939;
					$account['plan'] = 'Premium';
				} else if($account['current_plan_id'] == '9') {
					// Premium
					$org->status_id = 12939;
					$account['plan'] = 'Enterprise';
				}

				// If the domain isn't a generic one, we'll make a ruff guess at their website
				$domain_parts = explode('.', $account['domain']);
				
				if(!in_array($domain_parts[0], $this->shared_domains)) {
					$org->website = 'http://www.'.$account['domain'];
				}
				
				$new_org = $client->saveOrganisation($org);

				if($new_org->status == "success") {
					$org->id = $new_org->id;
					// Tag with Plan and Tactile CRM
					$client->tagOrganisation($org, 'Tactile CRM');
					$client->tagOrganisation($org, $account['plan']);
										
					// Nothing has gone wrong adding the org so we'll add the person too
					require_once 'Tactile/Api/Person.php';

					// Add a new person
					$person = new Tactile_Api_Person();
					$person->organisation_id = $org->id;

					$person->firstname = ucwords($account['firstname']);
					$person->surname = ucwords($account['surname']);
					$person->email = $account['email'];
					if(!empty($account['telephone'])) {
						$person->phone = trim($account['telephone']);
					}

					$new_person = $client->savePerson($person);

					if($new_person->status == "success") {
						$person->id = $new_person->id;
						// Tag with Plan and Tactile CRM
						$client->tagPerson($person, 'Tactile CRM');
						$client->tagPerson($person, $account['plan']);
						
						require_once 'Tactile/Api/Note.php';
						
						$note = new Tactile_Api_Note();
						$note->organisation_id = $org->id;
						$note->person_id = $person->id;

						$note->title = 'Website Signup - ' . $account['plan'];
						
						$note->note = $person->firstname . ' signed up for a ' . strtolower($account['plan']) .' account on the website at ' . date('H:i \o\n l jS \o\f F Y', strtotime($account['created']));
						
						// If from iphone app we'll add an opportunity
						if($account['signup_code'] == 'iphone') {
							require_once 'Tactile/Api/Opportunity.php';

							$opportunity = new Tactile_Api_Opportunity();
							$opportunity->organisation_id = $org->id;
							$opportunity->person_id = $person->id;
			
							$opportunity->name = 'iPhone App Purchase';

							$opportunity->type_id = 8855;
							$opportunity->status_id = 4487;

							$opportunity->source_id = 13323;
							$opportunity->assigned_to = 'website//team';
							$opportunity->cost = 3;

							$opportunity->probability = 100;
							$opportunity->enddate = date('Y-m-d');

							$new_opportunity = $client->saveOpportunity($opportunity);
						}

						// If on a paid plan we'll add an opportunity
						if($account['current_plan_id'] != '10') {
							require_once 'Tactile/Api/Opportunity.php';

							$opportunity = new Tactile_Api_Opportunity();
							$opportunity->organisation_id = $org->id;
							$opportunity->person_id = $person->id;
			
							if($account['per_user_limit'] == 1) {
								$opportunity->name = $account['plan'].' Signup';
							} else {
								$opportunity->name = $account['plan'].' Signup ('.$account['per_user_limit'].' Users)';
							}

							$opportunity->type_id = 2413;
							$opportunity->status_id = 4486;

							if($account['signup_code'] == '') {
								$opportunity->source_id = 2688;
							} else if($account['signup_code'] == 'iphone') {
								$opportunity->source_id = 13323;
							} else {
								$opportunity->source_id = 13324;
							}

							$opportunity->assigned_to = 'website//team';

							if($account['current_plan_id'] == 9) {
								$opportunity->cost = 12 * $account['per_user_limit'];
							} else {
								$opportunity->cost = 6 * $account['per_user_limit'];
							}

							$opportunity->probability = 70;
							$opportunity->enddate = date('Y-m-d', strtotime('+30 days'));

							$new_opportunity = $client->saveOpportunity($opportunity);

							if($new_opportunity->status == "success") {
								$note->opportunity_id = $new_opportunity->id;
							}
						}

						$new_note = $client->saveNote($note);

						if(!empty($account['telephone'])) {
							// If they are on a paid plan we'd like to chase them
							if($account['current_plan_id'] != '10') {
								require_once 'Tactile/Api/Activity.php';

								// Add a new activity
								$activity = new Tactile_Api_Activity();
								$activity->organisation_id = $org->id;
								$activity->person_id = $person->id;
								$activity->type_id = 2709;
								$activity->name = 'Follow Up (Paid Signup)';
								$activity->assigned_to = 'jake//team';
								$activity->date = date('Y-m-d', strtotime('+2 days'));

								if($new_opportunity->status == "success") {
									$activity->opportunity_id = $new_opportunity->id;
								}
								// We're stopping following up for the mo
								// $new_activity = $client->saveActivity($activity);
				
							} else if($account['country_code'] == 'GB') {
								//$client->tagPerson($person, 'Call Back (UK)');
							}
							else if($account['country_code'] == 'US') {
								//$client->tagPerson($person, 'Call Back (US)');
							}
							else {
								//$client->tagPerson($person, 'Call Back');
							}
						}
					}
				} else {
					require_once 'Zend/Log.php';
					$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Syncing New Account Problem'));
					$logger->crit('Account: '.$org->accountnumber);
				}

			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Error updating our Tactile CRM via the API'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());

				break;
			}
		
			// We call this multiple times to deal with the various autoresponders
			// The first is our monthly newsletter	
			$this->sendToCM($account);
			// And the help/step through emails
			//$this->sendToCM($account, '1429a41633834a97c2366f5fd55eea9e');
			// If they are on a paid account
			if(strtolower($account['plan']) != 'solo') {
				$this->sendToCM($account, '73634f379b469c71e8e31bf78104c777');
				// If they are in the one of the countries that can do dodgy charges
				if(in_array($account['country_code'], array('US', 'CA'))) $this->sendToCM($account, 'e38ecad5ebd49fa50ecd7a8b1e8d0e2b');
			}
			else $this->sendToCM($account, '5230d0f588eda4a723fc81418f421919');

			$this->sendToHubspot($account);
			// Update Record
			$query = 'UPDATE tactile_accounts set synced=true WHERE id='.$db->qstr($account['id']);
			
			$db->StartTrans();
			$db->execute($query);
			$db->CompleteTrans();
			// Send Email
			
			$mail = new Omelette_Mail('signup');

			$mail->getMail()->setSubject("Welcome to Tactile CRM");
			$mail->getMail()->setFrom('support@tactilecrm.com','The Tactile CRM Team');
			//$mail->addBcc('jake+billing@omelett.es');

			if (defined('PRODUCTION') && PRODUCTION == true) {
				$mail->getMail()->addTo($account['email']);
			} else {
				$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
			}

			$mail->getView()->set('firstname', trim(ucwords($account['firstname'])));
			$mail->getView()->set('surname', trim(ucwords($account['surname'])));
			$mail->getView()->set('site_address', $account['site_address']);
			$mail->getView()->set('username', $account['username']);
			
			try {
				$mail->send();	
			} catch (Zend_Mail_Transport_Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Invoice Email Problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}		
	}
	
	function sendToCM($account, $list_id = null) {
		try {
			if (defined('PRODUCTION') && PRODUCTION == true) {
				$CM_api_key = '631cc52a3ed14b21cac26b0b46807028';

				if(!is_null($list_id)) $CM_list_id = $list_id;
				else $CM_list_id = '58ba2364c8dc21b1fa3c9e9ed17569fe';
			} else {
				$CM_api_key = '631cc52a3ed14b21cac26b0b46807028';
				$CM_list_id = 'a88ee06805300337bde7a9eb76218b3f';
			}

			$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
			$response = $client->AddSubscriberWithCustomFields(
				array(
					'ApiKey' => $CM_api_key,
					'ListID' => $CM_list_id,
					'Email' => $account['email'],
					'Name' => trim(ucwords($account['firstname'] . ' ' . $account['surname'])),
					'CustomFields' => array(
						array(
						   'Key' => 'site_address',
						   'Value' => $account['site_address']
						),  
						array(
							'Key' => 'username',
							'Value' => $account['username']
						),  
						array(
							'Key' => 'plan_name',
							'Value' => strtolower($account['plan'])
						),
						array(
							'Key' => 'country_code',
							'Value' => $account['country_code']
						)
					)
				 )
			);
		} catch (Exception $e) {
			  require_once 'Zend/Log.php';
			  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Campaign monitor sign-up subscription problem for '.$CM_list_id));
			  $logger->crit($e->getMessage());
			  $logger->crit($e->getTraceAsString());
			
			break;
		}
	}

	function expandNotes($notes) {
		$note = explode('::', $notes);

		$parsed_notes = array();

		while($data = array_pop($note)) {
			$tmp_note = explode('//', $data);
			$parsed_notes[$tmp_note[0]] = $tmp_note[1];
		}

		return $parsed_notes;
	}

	function sendToHubspot($account) {
		try {
			$notes = $this->expandNotes($account['notes']);
	
			$hubspot_params = array(
				"FirstName"	=> $account['firstname'],
				"LastName"	=> $account['surname'],
				"Email"		=> $account['email'],
				"Phone"		=> $account['telephone'],
				"Company"	=> $account['company'],
				"Country"	=> !empty($account['country'])?$account['country']:'',
				"IPAddress"	=> !empty($notes['SIGNUP_IP'])?$notes['SIGNUP_IP']:'',
				"UserToken"	=> !empty($notes['HUBSPOT_TOKEN'])?$notes['HUBSPOT_TOKEN']:'',
				"SiteAddress"	=> $account['site_address']
			);

			$http_query = http_build_query($hubspot_params);

			$context_params = array(
				'http' => array(
				'method'        => 'POST',
				'timeout'       => 3,
				'content'       => $http_query
				)
			);

			$file_context = stream_context_create($context_params);
			$hubspot_response = @file_get_contents('http://tactilecrm.app3.hubspot.com/?app=leaddirector&FormName='.ucwords($account['plan']).'+Signup', false, $file_context);

		} catch (Exception $e) {
			  require_once 'Zend/Log.php';
			  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Hubspot sync issues'));
			  $logger->crit($e->getMessage());
			  $logger->crit($e->getTraceAsString());
			
			break;
		}
	}

}
