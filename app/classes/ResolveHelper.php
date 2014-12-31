<?php

class ResolveHelper {
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
		
		$tickets = array();
		
		$query = "SELECT t.id, t.summary, t.created, t.lastupdated, t.person_id, s.name AS status
			FROM tickets t, ticket_statuses s
			WHERE t.status_id=s.id AND s.closed=false AND 
			t.usercompanyid={$db->qstr(EGS::getCompanyId())} AND 
			t.{$model}_id={$db->qstr($model_id)}";

		$ticket_data= $db->getAll($query);
		
		$formatter = new PrettyTimestampFormatter();
		
		foreach($ticket_data AS $ticket) {
			$ticket['created'] = $formatter->format($ticket['created']);
			$ticket['lastupdated'] = $formatter->format($ticket['lastupdated']);
			
			if($model == 'organisation') {
				$per = new Tactile_Person();
				if (FALSE !== $per->load($ticket['person_id'])) {
					$ticket['person'] = $per->firstname.' '.$per->surname;
				}
			}
			$tickets[] = $ticket;
		}
		//echo '<pre>';print_r($tickets);
		
		$this->view->set('tickets', $tickets);
		$this->view->set('resolve_address', $account->site_address);
	}
}