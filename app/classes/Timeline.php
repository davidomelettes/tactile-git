<?php

class Timeline implements Iterator, Countable {

	private $_timeline = array();
	private $_position = 0;
	
	public $cur_page = 1;
	public $num_pages = 1;
	public $per_page = 50;
	public $total = 0;
	
	protected $_valid_types = array('note', 'email', 'flag', 's3file', 'opportunity', 'new_activity', 'completed_activity', 'overdue_activity');
	protected $_types = array();
	
	/**
	 * Add an item type to the timeline
	 *
	 * @param string $type
	 * @param boolean $which TRUE == all, FALSE == just mine
	 */
	public function addType($type, $which=true) {
		if (in_array($type, $this->_valid_types)) {
			$this->_types[$type] = $which;
		} else {
			throw new Exception('Unknown type for Timeline!');
		}
	}
	
	public function load(ConstraintChain $cc, $page=1, $force_statement_limits=false) {
		$this->_timeline = array();
		
		$db = DB::Instance();
		
		// Common CChain for 'Mine'
		$cc_mine = new ConstraintChain();
		$cc_mine->add(new Constraint('owner', '=', EGS::getUsername()));
		$cc_mine->add(new Constraint('assigned_to', '=', EGS::getUsername()), 'OR');
		
		// Build the SELECT queries we'll be unifying
		$qbs = array();
		foreach ($this->_types as $type => $all) {
			$qb = new QueryBuilder($db);
			$qb->orderby('"when"', 'DESC');
			if ($force_statement_limits) {
				$qb->limit($this->per_page); //, $this->per_page * ($page - 1));
			}
			
			$cc_type = new ConstraintChain();
			
			if (!isModuleAdmin()) {
				$qb->left_join('organisation_roles oroles', 'oroles.organisation_id = t.organisation_id AND oroles.read');
				
				$qb_roles = new QueryBuilder($db);
				$qb_roles->select_simple(array('roleid'))
					->from('hasrole')
					->where(new Constraint('username', '=', EGS::getUsername()));
				$c_roles = new Constraint('oroles.roleid', 'IN', '('.$qb_roles->__toString().')');
				
				// CChain for non-admin users
				switch ($type) {
					case 'note':
						// Notes are slightly different, privacy settings are not overwritten by org permissions
						$cc_access = new ConstraintChain();
						
						$cc_public = new ConstraintChain();
						$cc_public->add(new Constraint('private','=','false'));
						
						$cc_public2 = new ConstraintChain();
						$cc_public_no_org = new Constraint('t.organisation_id','IS','NULL');
						$cc_public2->add($cc_public_no_org);
						$cc_public2->add($c_roles, 'OR');
						$cc_public->add($cc_public2);
						
						$cc_private = new ConstraintChain();
						$cc_private->add(new Constraint('private','=','true'));
						$cc_private->add(new Constraint('owner','=',EGS::getUsername()));
						
						$cc_access->add($cc_public);
						$cc_access->add($cc_private, 'OR');
						$cc_type->add($cc_access);
						break;
					default: {
						$cc_access = new ConstraintChain();
						$cc_access->add($c_roles);
						$cc_access->add(new Constraint('owner','=',EGS::getUsername()), 'OR');
						$cc_access->add(new Constraint('assigned_to','=',EGS::getUsername()), 'OR');
						$cc_public = new ConstraintChain();
						$cc_public->add(new Constraint('t.organisation_id','IS','NULL'));
						$cc_public->add(new Constraint('private','=','false'));
						$cc_access->add($cc_public, 'OR');
						$cc_type->add($cc_access);
					}
				}
				
				$qb->select_simple(array('t.*'), true); // Distinct?
			} else {
				$qb->select_simple(array('*')); // Distinct?
			}
			
			// FALSE means we just asked for "mine"
			if (!$all) {
				$cc_type->add($cc_mine);
			}
			
			$cc_type->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
			$cc_type->add($cc);
			
			switch ($type) {
				case 'note':
					$qb->from('timeline_notes t');
					break;
				case 'email':
					$qb->from('timeline_emails t');
					// Only show unassigned email if it belongs to me
					$cc_email = new ConstraintChain();
					$cc_email->add(new Constraint('owner','=',EGS::getUsername()));
					$cc_email->add(new Constraint('t.organisation_id','IS NOT','NULL'), 'OR');
					$cc_email->add(new Constraint('person_id','IS NOT','NULL'), 'OR');
					$cc_email->add(new Constraint('opportunity_id','IS NOT','NULL'), 'OR');
					$cc_type->add($cc_email);
					break;
				case 'flag':
					$qb->from('timeline_flags t');
					break;
				case 's3file':
					$qb->from('timeline_s3_files t');
					break;
				case 'opportunity':
					$qb->from('timeline_opportunities t');
					break;
				case 'new_activity':
					$qb->from('timeline_activities_new t');
					break;
				case 'completed_activity':
					$qb->from('timeline_activities_completed t');
					break;
				case 'overdue_activity':
					$qb->from('timeline_activities_overdue t');
					break;
				default:
					throw new Exception('Unknown type in Timeline: ' . $type);
			}
			$qb->where($cc_type);
			$qbs[] = $qb;
		}
		
		// Union the SELECTs
		if (!empty($qbs)) {
			$union_qb = array_shift($qbs);
			foreach ($qbs as $select_qb) {
				$union_qb->union($select_qb, TRUE);
			}
			$union_qb->union_order('"when"', 'DESC')
				->union_limit((int) $this->per_page, $this->per_page * ($page - 1));
			
			if (FALSE === ($this->total = $db->getOne($union_qb->countQuery('t.id')))) {
				throw new Exception('Failed to count Timeline results! ' . $db->ErrorMsg());
			}
			
			if ($this->total > 0) {
				if (FALSE === ($results = $db->getArray($union_qb->__toString()))) {
					throw new Exception('Failed to load Timeline! ' . $db->ErrorMsg());
				}
			} else {
				$results = array();
			}
		} else {
			$results = array();
			$this->total = 0;
		}
			
		$this->cur_page = $page;
		$this->num_pages = ceil($this->total / $this->per_page);
		
		$email_attachments = array();
		foreach ($results as $row) {
			switch ($row['type']) {
				case 'email':
					$email_attachments[$row['id']] = 0;
					break;
			}
		}
		if (count($email_attachments) > 0) {
			$email_attachments = $db->getAssoc("SELECT email_id AS id, count FROM timeline_email_attachment_count WHERE email_id IN ('".implode("', '", array_keys($email_attachments))."')");
		}
		
		foreach ($results as $row) {
			switch ($row['type']) {
				case 'email':
					$row['email_attachments'] = empty($email_attachments[$row['id']]) ? 0 : ((int) $email_attachments[$row['id']]);
					break;
			}
			
			$object = new TimelineObject($row);
			$this->_timeline[] = $object;
		}
		return $this;
	}
	
	public function countTimelineSegments() {
		$when = '';
		$segments = 0;
		foreach ($this as $item) {
			if ($item->getTimelineDate() != $when) {
				$when = $item->getTimelineDate();
				$segments++;
			}
		}
		return $segments;
	}
	
	public function pluck($key) {
		$return = array();
		foreach ($this as $model) {
			if (is_array($key)) {
				foreach ($key as $k) {
					$val = $model->$k;
					if (!is_null($val)) {
						$return[$model->$k] = true;
						break;
					}
				}
			} else {
				$return[$model->$key] = true;
			}
		}
		return array_keys($return);
	}
	
	public function current() {
		return $this->_timeline[$this->_position];
	}
	
	public function key() {
		return $this->_position;
	}
	
	public function next() {
		$this->_position++;
	}
	
	public function rewind() {
		$this->_position = 0;
	}
	
	public function valid() {
		return isset($this->_timeline[$this->_position]);
	}
	
	public function count() {
		return count($this->_timeline);
	}
	
}

