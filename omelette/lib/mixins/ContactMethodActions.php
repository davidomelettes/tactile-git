<?php
/**
 * Responsible for saving contact-methods (phone/fax/email/mobile) attached to Person/Org
 * @author gj
 * @package Mixins
 */
class ContactMethodActions {
	protected static $cms = array(
		'phone'		=> 'T',
		'fax'		=> 'F',
		'email'		=> 'E',
		'mobile'	=> 'M',
		'website'	=> 'W',
		'skype'		=> 'S',
		'twitter'	=> 'I',
		'linkedin'	=> 'L',
		'facebook'	=> 'K'
	);
	
	/**
	 * Saves the contact method against the given person/company id. Redirects as appropriate
	 * Note that $this is the calling class (i.e. the controller)
	 * Uses a hash of arguments:
	 * 0= the type of CM ('phone')
	 * @param unknown_type $args
	 */
	function save_contact($args) {
		$type = $args[0];
		if (!in_array($this->_data['type'], self::$cms) && isset(self::$cms[$this->_data['type']])) {
			$this->_data['type'] = self::$cms[$this->_data['type']];
		}
		
		$errors = array();
		$contact = DataObject::Factory($this->_data, $errors, $type);
		if ($contact !== false && $contact->save() !== false) {
			$this->view->set('success', true);
			$this->view->set('contact', $contact);
		} else {
			$this->view->set('success', false);
			Flash::Instance()->addErrors($errors);
		}
	}
	
	function save_contact_multi($args) {
		$type = $args[0];
		$collection_type = $args[1];
		
		if (!empty($this->_data['contact_method']) && is_array($this->_data['contact_method'])) {
			$success = true;
			$contacts = array(); 
			foreach ($this->_data['contact_method'] as $id => $data) {
				$errors = array();
				if (isset($this->_data['person_id'])) {
					$data['person_id'] = $this->_data['person_id'];
				} elseif (isset($this->_data['organisation_id'])) {
					$data['organisation_id'] = $this->_data['organisation_id'];
				}
				if (preg_match('/^[0-9]+$/', $id)) {
					// Edit
					$data['id'] = $id;
				} else {
					// New
				}
				if ($data['contact'] == 'e.g. +44 (0)2476 010105') {
					$data['contact'] = '';
				}
				if (!empty($this->_data['contact_method_main'][$data['type']])) {
					if ($this->_data['contact_method_main'][$data['type']] == $id) {
						$data['main'] = 't';
					} else {
						$data['main'] = 'f';
					}
				}
				if (isset($data['type'])) {
					switch ($data['type']) {
						case 'I':
							$data['contact'] = preg_replace('/\/$/', '', $data['contact']);
							$data['contact'] = preg_replace('/.*\//', '', $data['contact']);
							$data['contact'] = preg_replace('/@/', '', $data['contact']);
							break;
						case 'W':
							if (!preg_match('/^https?:\/\//', $data['contact'])) {
								$data['contact'] = 'http://' . $data['contact'];
							}
							break;
						case 'L':
							$data['contact'] = preg_replace('/\/$/', '', $data['contact']);
							$data['contact'] = preg_replace('/^https?:\/\/(www\.)?linkedin\.com\/in\//', '', $data['contact']);
							break;
						case 'K':
							$data['contact'] = preg_replace('/\/$/', '', $data['contact']);
							$data['contact'] = preg_replace('/^https?:\/\/(www\.)?facebook\.com\/in\//', '', $data['contact']);
							break;
					}
				}
				
				$contact = DataObject::Factory($data, $errors, $type);
				if ($contact !== false && $contact->save() !== false) {
					$contacts[] = $contact;
				} else {
					$success = false;
					Flash::Instance()->addErrors($errors);
				}
			}
			$this->view->set('status', ($success ? 'success' : 'failure'));
			if ($success) {
				$contacts = new $collection_type;
				$sh = new SearchHandler($contacts, false);
				if (isset($this->_data['person_id'])) {
					$sh->addConstraint(new Constraint('person_id', '=' , $this->_data['person_id']));
				} elseif (isset($this->_data['organisation_id'])) {
					$sh->addConstraint(new Constraint('organisation_id', '=' , $this->_data['organisation_id']));
				}
				$sh->setOrderby('position, main desc, name');
				$contacts->load($sh);
				$this->view->set('contacts', $contacts);
			}
		}
	}
	
	function delete_contact($args) {
		$type = $args[0];
		if (FALSE !== ($long_type = array_search($this->_data['type'], self::$cms))) {
			$this->_data['type'] = $long_type; 
		}
		$htype = ucfirst($this->_data['type']);
		$this->_data['type'] = self::$cms[$this->_data['type']];
		$model = new $type;
		$success = ModelDeleter::delete($model,$htype,array());
		if($success!==false) {
			$this->view->set('success',true);
		}
		else {
			$this->view->set('sucess',false);
			Flash::Instance()->addError('Deleting the item failed');
		}
		$this->setTemplatename('delete');
	}
	
	function contact_methods($args) {
		$model_type = $args[0];
		$collection_type = $args[1];
		$fkey = $args[2];
		/* @var $collection DataObjectCollection */
		$collection = new $collection_type;
		$model = DataObject::Construct($model_type);
		$valid = $model->load($this->_data['id']);
		if($valid === false) {
			Flash::Instance()->addError("Invalid ID");
			sendTo();
			return;
		}
		$sh = new SearchHandler($collection, false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint($fkey, '=', $this->_data['id']));
		$sh->addConstraintChain($cc);
		$sh->setOrderby('type, main, name');
		$collection->load($sh);
		$this->view->set('contact_methods', $collection);
	}
}
