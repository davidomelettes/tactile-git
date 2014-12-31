<?php

class FieldFilter {
	function by_field($args) {
		$collection = $args[0];
		$field = $args[1];
		$redirect = $args[2];
		$subtitle = $args[3];

		$constraints = isset($args[4]) ? $args[4] : array();
		
		if (empty($this->_data['q'])) {
			Flash::Instance()->addError('Invalid search term');
			sendTo($redirect);
			return;
		}
		
		$items = new $collection;
		$sh = new SearchHandler($items, false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$sh->extractFields();
		//$sh->addConstraint(new Constraint('usercompanyid', '=', EGS::getCompanyId()));

		// Check if we're searching on an id
		if(strpos($field, '_id') !== false) {
			$sh->addConstraint(new Constraint($field, '=', intval($this->_data['q'])));
		} else if(strpos($field, 'assigned_to') !== false) {
			$sh->addConstraint(new Constraint('lower(' . $field . ')', '=', strtolower(urldecode($this->_data['q'])) . '//'. Omelette::getUserSpace()));
		} else if(strpos($field, 'owner') !== false) {
			$sh->addConstraint(new Constraint('lower(' . $field . ')', '=', strtolower(urldecode($this->_data['q'])) . '//'. Omelette::getUserSpace()));
			// At the moment this field filter is only used on the activities
			$sh->addConstraint(new Constraint(str_replace('owner', 'assigned_to', $field), '!=', strtolower(urldecode($this->_data['q'])) . '//'. Omelette::getUserSpace()));
		} else {
			$sh->addConstraint(new Constraint('lower(' . $field . ')', '=', strtolower(urldecode($this->_data['q']))));
		}

		foreach ($constraints as $key => $value) {
			$sh->addConstraint(new Constraint($key, '=', $value));
		}
		
		Controller::index($items, $sh);
		
		$this->setTemplateName('index');
		$this->useTagList();
		$this->view->set('current_query', http_build_query(array('q' => $this->_data['q'])));
		
		// We need to add a translation if 't' is set
		if(isset($this->_data['t'])) {
			$this->view->set('sub_title', $subtitle . ' "' . $this->_data['t'] . '"');
		} else {
			$this->view->set('sub_title', $subtitle . ' "' . $this->_data['q'] . '"');
		}
	}
}
