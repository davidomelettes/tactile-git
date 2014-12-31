<?php

require_once 'Service/Zendesk.php';

class ZendeskHelper {
	public function ticket_list($contact_object) {
		$db = DB::Instance();
		
		if (get_class($contact_object) == 'Tactile_Organisation') {
			$model = 'organisation';
			$area = 'organisations';
			$model_id = $this->getOrganisation()->id;
		} elseif (get_class($contact_object) == 'Tactile_Person') {
			$model = 'person';
			$area = 'people';
			$model_id = $this->getPerson()->id;
		} else {
			return;
		}
		
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		
		$cache = Zend_Registry::get('cache');
		$cache_key = "zendesk_tickets_" . $model . "_" . $model_id;
		
		if (isset($this->_data['refresh'])) {
			$cache->remove($cache_key);
			sendTo('view', $this->_data['id'], $area);
			return;
		}
		
		if (!$cache->test($cache_key)) {
			$tickets = array();
			
			$zds = new Service_Zendesk(Tactile_AccountMagic::getValue('zendesk_siteaddress'),
				Tactile_AccountMagic::getValue('zendesk_email'),
				Tactile_AccountMagic::getValue('zendesk_password'));
			
			$email_addresses = $db->getCol(
				"SELECT contact FROM {$model}_contact_methods
				WHERE {$model}_id = {$db->qstr($model_id)}
				AND type = 'E'
			");
			if ($model == 'organisation') {
				// Look for tickets from the people in this org
				$extra_email_addresses = $db->getCol(
					"SELECT contact FROM person_contact_methods
					WHERE person_id in (select id from people where organisation_id = {$db->qstr($model_id)})
					AND type = 'E'
				");
				$email_addresses = array_merge($email_addresses, $extra_email_addresses);
			}
			
			foreach ($email_addresses as $email_address) {
				$records = $zds->search('type:ticket requester:' . $email_address);
				
				if ($records->getName() == 'nil-classes' || $records->getName() == 'error') {
					continue;
				}
				
				foreach ($records as $ticket) {
					
					$tickets[] = new Service_Zendesk_Entity_Ticket($ticket);
				}
			}
			
			$ticket_data = array(
				'tickets' => serialize($tickets),
				'last_updated' => date('m/d/Y H:i:s')
			);
			
			$cache->save($ticket_data, $cache_key);
		} else {
			$ticket_data = $cache->load($cache_key);
		}
		
		$ticket_data['tickets'] = unserialize($ticket_data['tickets']);
		
		$this->view->set('zendesk_siteaddress', Tactile_AccountMagic::getValue('zendesk_siteaddress'));
		$this->view->set('refresh_link', '/' . $area . '/zendesk_tickets/' . $this->_data['id'] . '?refresh=1');
		$this->view->set('tickets', $ticket_data['tickets']);
		
		$formatter = new PrettyTimestampFormatter();
		$this->view->set('last_updated', $formatter->format($ticket_data['last_updated']));
	}
}