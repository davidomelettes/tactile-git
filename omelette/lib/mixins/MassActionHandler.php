<?php

class MassActionHandler {

	public function mass_action($args) {
		$do_string = $args[0];
		$sendto = $args[1];
		
		$flash = Flash::Instance();
		$action = !empty($this->_data['mass_action']) ? $this->_data['mass_action'] : '';
		$ids = (!empty($this->_data['ids']) && is_array($this->_data['ids'])) ? $this->_data['ids'] : array();
		if (empty($ids)) {
			$flash->addError('Please select at least one item');
			sendTo($sendto);
			return;
		} elseif (count($ids) > 30) {
			$ids = array_slice($ids, 0, 30);
		}
		$human_name = ucfirst($sendto);
		$errors = array();
		$user = CurrentlyLoggedInUser::Instance();
		$db = DB::Instance();
		
		// Prove ownership
		$models = array();
		foreach ($ids as &$id) {
			$do = new $do_string;
			if (FALSE === $do->load($id)) {
				$flash->addError('Failed to load ' . $human_name);
				sendTo($sendto);
				return;
			}
			if ($action == 'delete' || $action == 'merge') {
				if (!$user->canDelete($do)) {
					$flash->addError('You do not have permission to delete those ' . $human_name);
					sendTo($sendto);
					return;
				}
			} else {
				if (!$user->canEdit($do)) {
					$flash->addError('You do not have permission to edit those ' . $human_name);
					sendTo($sendto);
					return;
				}
			}
			$id = $db->qstr($id);
			$models[] = $do;
		}

		// Do the thing
		switch ($action) {
			case 'delete': {
				$qb = new QueryBuilder($db);
				$delete_cc = new ConstraintChain();
				$delete_cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
				$delete_cc->add(new Constraint('id', 'IN', '('.implode(',', $ids).')'));
				$qb->delete()
					->from($sendto == 'activities' ? 'tactile_activities' : $sendto)
					->where($delete_cc);
				$success = $db->Execute($qb->__toString());
				if ($success) {
					$flash->addMessage(count($ids) . ' ' . $human_name . ' successfully deleted.');
				} else {
					$flash->addError('A problem occurred trying to delete those ' . $human_name);
				}
				break;
			}
				
			case 'add_tags': {
				$tags_added = !empty($this->_data['tags']) ? $this->_data['tags'] : array();
				$tags_added = preg_split('/,\s*/', $tags_added);
				
				foreach ($models as $model) {
					$taggable = new TaggedItem($model);
					
					foreach ($tags_added as $tag) {
						$success = $taggable->addTag(trim($tag));
						$flash->clear(); // Adding a tag to something which already has it produces an error, lol
					}
				}
				
				if (empty($tags_added)) {
					$flash->addMessage('No Tags were added');
				} else {
					$flash->addMessage(count($tags_added) . ' Tag' . (count($tags_added)==1?'':'s') . ' added');
				}
				break;
			}
			
			case 'add_activity': {
				$activities_added = array();
				if (!empty($this->_data['Activity']['name'])) {	
					foreach ($models as $model) {
						$activity_data = $this->_data['Activity'];
						foreach (array('organisation_id', 'person_id', 'opportunity_id') as $fkey) {
							$val = $model->$fkey;
							if (!empty($val)) {
								$activity_data[$fkey] = $val;
							}
						}
						$activity_data[strtolower($model->get_name()) . '_id'] = $model->id;
						$activity_data = Tactile_Activity::processFormData($activity_data);
						
						if (FALSE !== ($activity = DataObject::Factory($activity_data, $errors, 'Activity')) && FALSE !== $activity->save()) {
							$activity_added[] = $activity;
						} else {
							$flash->addErrors($errors);
							sendTo($sendto);
							return;
						}
					
					}
				
					if (empty($activities_added)) {
						$flash->addMessage('No Activities were saved');
					} else {
						$flash->addMessage(count($activities_added) . ' ' . (count($activities_added)==1?'Activity':'Activities') . ' saved');
					}
					$this->view->set('models', $activities_added);
				}
				return;
			}
				
			case 'add_note': {
				$notes_added = array();
				if (!empty($this->_data['Note']['title']) && $this->_data['Note']['title'] == 'Title *') {
					$this->_data['Note']['title'] = '';
				}
				if (!empty($this->_data['Note']['note']) && $this->_data['Note']['note'] == 'Body *') {
					$this->_data['Note']['note'] = '';
				}
				
				foreach ($models as $model) {
					$note_data = $this->_data['Note'];
					foreach (array('organisation_id', 'person_id', 'opportunity_id') as $fkey) {
						$val = $model->$fkey;
						if (!empty($val)) {
							$note_data[$fkey] = $val;
						}
					}
					$note_data[strtolower($model->get_name()) . '_id'] = $model->id;
					
					if (FALSE !== ($note = DataObject::Factory($note_data, $errors, 'Note')) && FALSE !== $note->save()) {
						$notes_added[] = $note;
					} else {
						$flash->addErrors($errors);
						sendTo($sendto);
						return;
					}
					
				}
				
				if (empty($notes_added)) {
					$flash->addMessage('No Notes were saved');
				} else {
					$flash->addMessage(count($notes_added) . ' Note' . (count($notes_added)==1?'':'s') . ' saved');
				}
				$this->view->set('models', $notes_added);
				return;
			}
			
			case 'merge': {
				$master_id = !empty($this->_data['master_id']) ? $this->_data['master_id'] : '';
				$master = new $do_string;
				if (empty($master_id) || FALSE === $master->load($master_id)) {
					$flash->addError('Please choose an item to merge into');
					sendto($sendto);
					return;
				} elseif (!$user->canEdit($master)) {
					$flash->addError('You do not have permission to merge into that item');
					sendto($sendto);
					return;
				}
				
				// Remove master id from list of ids
				$remove_id = $db->qstr($master_id);
				if (FALSE !== ($index = array_search($remove_id, $ids))) {
					unset($ids[$index]);
				}
				
				// Remove user people and account org from list of ids
				switch ($do_string) {
					case 'Tactile_Organisation':	
						$account_org_id = $db->qstr(EGS::getCompanyId());
						if (FALSE !== ($index = array_search($account_org_id, $ids))) {
							unset($ids[$index]);
						}
						break;
					case 'Tactile_Person':
						$user_people_ids = $db->getCol("SELECT person_id FROM users WHERE username like " . $db->qstr('%//'.Omelette::getUserSpace()));
						foreach ($user_people_ids as $user_person_id) {
							$remove_id = $db->qstr($user_person_id);
							if (FALSE !== ($index = array_search($remove_id, $ids))) {
								unset($ids[$index]);
							}
						}
						break;
				}
				
				$referenced_tables = array('notes', 'emails', 'flags', 's3_files');
				switch ($do_string) {
					case 'Tactile_Organisation':
						$referenced_tables = array_merge($referenced_tables, array('tickets', 'people', 'opportunities', 'tactile_activities', 'organisation_contact_methods'));
						$fkey = 'organisation_id';
						break;
					case 'Tactile_Person':
						$referenced_tables = array_merge($referenced_tables, array('opportunities', 'tactile_activities', 'person_contact_methods'));
						$fkey = 'person_id';
						break;
					case 'Tactile_Opportunity':
						$referenced_tables = array_merge($referenced_tables, array('tactile_activities'));
						$fkey = 'opportunity_id';
						break;
					case 'Tactile_Activity':
						$fkey = 'activity_id';
						break;
					default:
						throw new Exception('Unknown type: ' . $do_string);
				}
				
				$db->StartTrans();
				foreach ($referenced_tables as $referenced_table) {
					$success = $db->Execute("UPDATE $referenced_table SET $fkey = " . $db->qstr($master_id) .
						" WHERE $fkey IN (".implode(', ', $ids).")" .
						(!in_array($referenced_table, array('organisation_contact_methods', 'person_contact_methods')) ? (" AND usercompanyid = " . EGS::getCompanyId()) : '')
					);
					if (!$success) {
						$db->FailTrans();
						$db->CompleteTrans();
						$flash->addError('A problem occurred trying to merge those ' . $human_name);
						sendto($sendto);
						return;
					}
				}
				
				// Merge tags
				// Can't do this above because of the UNIQUE contstraint on org_id and tag_id
				$do_model = new $do_string();
				$do_model->load($master_id);
				$ti = new TaggedItem($do_model);
				$new_tag_ids = $db->getCol("SELECT DISTINCT tag_id FROM tag_map WHERE $fkey IN (".implode(', ', $ids).") " .
					"AND tag_id NOT IN (SELECT tag_id FROM tag_map WHERE $fkey = " . $db->qstr($master_id) . ")");
				$success = true;
				foreach ($new_tag_ids as $tag_id) {
					$tag = new Omelette_Tag();
					if (!$tag->load($tag_id) || !$ti->addTag($tag)) {
						$success = false;
					}
				}
				if (!$success) {
					$db->FailTrans();
					$db->CompleteTrans();
					$flash->addError('A problem occurred trying to merge the tags for those ' . $human_name);
					sendto($sendto);
					return;
				}
				
				// Delete the rest
				$qb = new QueryBuilder($db);
				$delete_cc = new ConstraintChain();
				$delete_cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
				$delete_cc->add(new Constraint('id', 'IN', '('.implode(',', $ids).')'));
				$qb->delete()
					->from($sendto == 'activities' ? 'tactile_activities' : $sendto)
					->where($delete_cc);
				$success = $db->Execute($qb->__toString());
				
				if ($success) {
					$db->CompleteTrans();
					$flash->addMessage(count($ids) . ' ' . $human_name . ' successfully merged.');
					sendto($sendto.'/view/'.$master_id);
					return;
				} else {
					$db->FailTrans();
					$db->CompleteTrans();
					$flash->addError('A problem occurred trying to merge those ' . $human_name);
					sendto($sendto);
					return;
				}
				break;
			}
			
			case 'assign_to': {
				$assign_to_username = !empty($this->_data['username']) ? $this->_data['username'] : '';
				$assign_to_user = new Omelette_User();
				if (empty($assign_to_username) || FALSE === $assign_to_user->load($assign_to_username)) {
					$flash->addError('Please choose a user to assign these items to');
					sendto($sendto);
					return;
				}
				
				$db->StartTrans();
				$success = true;
				foreach ($models as $model) {
					$model->assigned_to = $assign_to_user->getRawUsername();
					if ($do_string == 'Tactile_Activity') {
						$model->assigned_by = $user->getRawUsername();
					}
					if (!$model->save()) {
						$success = false;
					}
				}
				
				if ($success) {
					$db->CompleteTrans();
					$flash->addMessage(count($ids) . ' ' . $human_name . ' successfully assigned to ' . $assign_to_user->username);
					sendto($sendto);
					return;
				} else {
					$db->FailTrans();
					$db->CompleteTrans();
					$flash->addError('A problem occurred trying to assign those ' . $human_name);
					sendto($sendto);
					return;
				}
				
				break;
			}
			
			case 'change_permissions': {
				if ($do_string !== 'Tactile_Organisation') {
					sendto($sendto);
					return;
				}
				$sharing = !empty($this->_data['Sharing']) ? $this->_data['Sharing'] : array('read' => 'everyone', 'write' => 'everyone');
				$levels = array('read', 'write');
				
				$everyone_id = Omelette::getUserSpaceRole()->id;
				$private_id = CurrentlyLoggedInUser::getUserRole(EGS::getUsername())->id;
				$db->StartTrans();
				foreach ($models as $model) {
					// Remove old permissions
					OrganisationRoles::deleteForCompany($model->id);
					
					// Prepare new permissions
					$role_datas = array();
					$everyone = array('organisation_id' => $model->id, 'roleid' => $everyone_id);
					$private = array('organisation_id' => $model->id, 'roleid' => $private_id);
					foreach ($levels as $level) {
						if (!isset($sharing[$level])) {
							continue;
						}
						switch ($sharing[$level]) {
							case 'everyone':
								if (!isset($role_datas['everyone'])) {
									$role_datas['everyone'] = $everyone;
								}
								$role_datas['everyone'][$level] = true;
								if ($level == 'write') {
									$role_datas['everyone']['read'] = true;
								}
								break;
							case 'private':
								if (!isset($role_datas['private'])) {
									$role_datas['private'] = $private;
								}
								$role_datas['private'][$level] = true;
								if ($level=='write') {
									$role_datas['private']['read'] = true;
								}
								break;
							default:
								if (is_array($sharing[$level])) {
									foreach ($sharing[$level] as $role_id) {
										if (!isset($role_datas[$role_id])) {
											$role_datas[$role_id] = array(
												'organisation_id' => $model->id,
												'roleid' => $role_id
											);
										}
										$role_datas[$role_id][$level] = true;
										if ($level == 'write') {
											$role_datas[$role_id]['read'] = true;
										}
									}
								}
						}
					}
					
					// Save new permissions
					foreach ($role_datas as $role_data) {
						$role = DataObject::Factory($role_data, $errors, 'OrganisationRoles');
						if ($role == FALSE || FALSE == $role->save()) {
							$db->FailTrans();
							$flash->addError('There was a problem saving the new permissions for one of the selected Organisations');
							sendto($sendto);
							return;
						}
					}
				}
				
				$db->CompleteTrans();
				$flash->addMessage('Permissions saved for ' . count($ids) . ' ' . $human_name);
				sendto($sendto);
				return;
				
				break;
			}
			
			case 'close': {
				if ($do_string !== 'Ticket') {
					$flash->addError('Item type must be Tickets!');
					sendto($sendto);
					return;
				}
				
				$closed_id = $db->getOne("SELECT id FROM ticket_statuses WHERE closed AND usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY position ASC LIMIT 1");
				if (empty($closed_id)) {
					$flash->addError('Could not load ticket closed status');
					sendto($sendto);
					return;
				}
				
				$db->StartTrans();
				$success = true;
				foreach ($models as $model) {
					$ticket_data = array('id' => $model->id, 'status_id' => $closed_id);
					$changed_ticket = DataObject::Factory($ticket_data, $errors, 'Ticket');
					if (!$changed_ticket->save()) {
						$success = false;
						$flash->addError('Error modifying ticket ' . $model->id);
					}
					
					$mod = new TicketModification($changed_ticket, $model);
					$person_id = CurrentlyLoggedInUser::Instance()->getModel()->person_id;
					$mod->setPersonId($person_id);
					require_once 'Zend/Log/Writer/Null.php';
					$logger = new Zend_Log(new Zend_Log_Writer_Null());
					if (!$mod->apply($logger)) {
						$success = false;
						$flash->addError('Error applying modification to ticket ' . $model->id);
					}
				}
				
				if ($success) {
					$db->CompleteTrans();
					$flash->addMessage(count($ids) . ' ' . $human_name . ' successfully clsoed');
					sendto($sendto);
					return;
				} else {
					$db->FailTrans();
					$db->CompleteTrans();
					$flash->addError('A problem occurred trying to close those ' . $human_name);
					sendto($sendto);
					return;
				}
				break;
			}
			
			case '':
				$flash->addError('Please specify an action');
				break;
			default:
				$flash->addError('Invalid action: ' . $action);
		}
		sendto($sendto);
	}
	
}
