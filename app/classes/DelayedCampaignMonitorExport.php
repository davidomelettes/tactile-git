<?php

class DelayedCampaignMonitorExport extends DelayedExport {
	
	public static $allowed_types = array('person');
	
	public static $output_fields = array(
		'person' => array(
			'id', 'title', 'firstname', 'surname', 'suffix','email'
		)
	);
	
	public function setListId($cm_list_id) {
		$this->data['cm_list_id'] = $cm_list_id;
	}
	
	public function setListName($cm_list_name) {
		$this->data['cm_list_name'] = $cm_list_name;
	}
	
	public function execute() {
		$logger = $this->logger;
		$logger->debug("Exporting people to Campaign Monitor (list_id: {$this->data['cm_list_id']})");
		$exporter = new SubscribablePersonExporter();
		$exporter->setUserCompanyId(EGS::getCompanyId());
		$exporter->setUsername(EGS::getUsername());
		
		if (isset($this->data['tags']) && count($this->data['tags']) > 0) {
			$logger->debug("Exporting by tags: ".print_r($this->data['tags'], true));
			$people = $exporter->getByTag($this->data['tags']);
		} else if(!empty($this->data['key']) && !empty($this->data['value'])) {
			$logger->debug("Exporting query: ".$this->data['key']." = ".$this->data['value']);
			$people = $exporter->getBy($this->data['key'], $this->data['value']);
		} else {
			$logger->debug("Exporting everything");
			$people = $exporter->getAll();	
		}
		$logger->debug("Ready to export " . count($people) . " rows");
		
		require_once 'Service/CampaignMonitor.php';
		$logger->debug("Using CM Key: " . $this->getCampaignMonitorKey());
		$cm = new Service_CampaignMonitor($this->getCampaignMonitorKey());
		$successes = 0;
		$failures = 0;
		$messages = array();
		foreach ($people as $person) {
			$success = $cm->subscriberAdd($this->data['cm_list_id'], $person['email'], "{$person['firstname']} {$person['surname']}");
			if ($success) {
				$saver = new ModelSaver();
				$flag_data = array(
					'person_id'	=> $person['id'],
					'title'		=> 'Subscribed to &ldquo;' . $this->data['cm_list_name'] . '&rdquo;',
					'owner'		=> EGS::getUsername()
				);
				$errors = array();
				$flag = $saver->save($flag_data, 'Flag', $errors);
				if (!empty($errors)) {
					$logger->debug(print_r($errors));
				}
				$successes++;
			} else {
				if (isset($messages[$cm->getLastResponse()->getErrorMsg()])) {
					$messages[$cm->getLastResponse()->getErrorMsg()]++;
				} else {
					$messages[$cm->getLastResponse()->getErrorMsg()] = 1;
				}
				$failures++;
			}
			$logger->debug("Adding {$person['email']} ({$person['firstname']} {$person['surname']}) - ". ($success ? "SUCCESS" : "FAILURE"));
		}
		
		$mail = $this->getMail();
		$mail = new Omelette_Mail('campaignmonitor_export');
		$template = $mail->getView();
		
		$template->set('list', $this->data['cm_list_name']);
		$template->set('total', count($people));
		$template->set('successes', $successes);
		$template->set('messages', $messages);
		
		$to = $this->getRecipientAddress();
		$logger->debug("Emailing results to " . $to);
		$mail->addTo($to)
			->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME)
			->setSubject("Tactile CRM: Your Campaign Monitor Export")
			->setBodyText($template->fetch('index.tpl'));

		$mail->send();
		$this->cleanup();
	}
	
	protected function getCampaignMonitorKey() {
		$account = new TactileAccount();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('organisation_id','=',EGS::getCompanyId()));
		$account = $account->loadBy($cc);
		if (!$account->isCampaignMonitorEnabled()) {
			return false;
		} else {
			return Tactile_AccountMagic::getValue('cm_key');
		}
	}
	
}

