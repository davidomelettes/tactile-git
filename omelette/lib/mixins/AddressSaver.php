<?php
/**
 * Responsible for saving addresses, attached to a Organisation or Person
 * @author gj
 * @package Mixins
 */
class AddressSaver {
	/**
	 * save the address and redirects as appropriate
	 * Note that $this is the calling class (i.e. the controller)
	 * Uses a hash of arguments:
	 * 0=classname ('Companyaddress')
	 * 1=foreign key ('organisation_id')
	 * 2=where to redirect to ('companys')
	 * @param Array $args
	 * @return void
	 */
	 
	function save_address($args) {
		$classname = $args[0];
		$classname_collection = $classname . 'Collection';
		$fk = $args[1];
		
		$errors = array();
		$flash = Flash::Instance();
		$db = DB::Instance();
		$user = CurrentlyLoggedInUser::Instance();
		
		$parent = $classname == 'Tactile_Organisationaddress' ? new Tactile_Organisation() : new Tactile_Person();
		if (!$parent->load($this->_data[$fk]) || !$user->canEdit($parent)) {
			$flash->addError('You do not have permission to edit this item');
			return false;
		}
		
		$fields = array('id', 'name', 'main', 'street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country_code');
		
		$address_data = array();
		foreach ($fields as $field) {
			if (!empty($this->_data[$field])) {
				$address_data[$field] = $this->_data[$field];
			}
		}
		$address_data[$fk] = $this->_data[$fk];
		
		// Check if the parent has an existing address
		$addresses = new $classname_collection;
		$sh = new SearchHandler($addresses, false);
		$sh->addConstraint(new Constraint($fk, '=' , $this->_data[$fk]));
		$sh->setOrderby('main desc, name');
		$addresses->load($sh);
		if (count($addresses) < 1) {
			$address_data['main'] = 'on';
		}
		
		$address = DataObject::Factory($address_data, $errors, $classname);
		
		$db->StartTrans();
		if ($address !== false && $address->save() !== false) {
			$flash->addMessage('Address saved Successfully');
			
			if (!empty($address_data['main'])) {
				// This is the new Main address, so update the others
				$db->execute("UPDATE ".preg_replace('/_id$/', '', $fk)."_addresses SET main = FALSE WHERE $fk = " . $db->qstr($this->_data[$fk]) . " AND id != " . $db->qstr($address->id));
			} else {
				// This is not a main address
				$address->main = false;
			}
			
			$addresses = new $classname_collection;
			$sh = new SearchHandler($addresses, false);
			$sh->addConstraint(new Constraint($fk, '=' , $this->_data[$fk]));
			$sh->setOrderby('main desc, name');
			$addresses->load($sh);
			$this->view->set('addresses', $addresses);
			
		} else {
			$db->FailTrans();
			$flash->addErrors($errors);
		}
		$db->CompleteTrans();
	}
	
	function delete_address($args) {
		$classname = $args[0];
		$classname_collection = $classname . 'Collection';
		$errors = array();
		$user = CurrentlyLoggedInUser::Instance();
		
		$this->setTemplateName('save_address');
		
		$address = new $classname;
		$fk = $address->getFkName();
		if (!$address->load($this->_data['id']) || !$user->canDelete($address)) {
			$this->view->set('status', 'failure');
		} else {
			$was_main = $address->isMain();
			$fk_value = $address->$fk;
			if (!$address->delete()) {
				
			} else {
				if ($was_main) {
					// Set new main address
					$new_main_address = new $classname;
					$cc = new ConstraintChain();
					$cc->add(new Constraint($fk, '=', $fk_value));
					$cc->add(new Constraint('main', '=','false'));
					if ($new_main_address->loadBy($cc)) {
						$new_main_address->main = 't';
						$new_main_address->save();
					}
				}
				
				$this->view->set('status', 'success');
				$addresses = new $classname_collection;
				$sh = new SearchHandler($addresses, false);
				$sh->addConstraint(new Constraint($fk, '=' , $fk_value));
				$sh->setOrderby('main desc, name');
				$addresses->load($sh);
				$this->view->set('addresses', $addresses);
			}
		}
	}
}
