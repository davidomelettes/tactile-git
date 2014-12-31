<?php
/**
 *
 * @author gj
 */
class TagsController extends Controller {
	
	/**
	 * Tags section home-page, display a tag-cloud in the main area
	 * 
	 **/
	public function index() {
		$db = DB::Instance();
		
		if (!isModuleAdmin()) {
			$rolesQuery = 'SELECT roleid FROM hasrole WHERE username = ' . $db->qstr(EGS::getUsername());
			$roles = $tags = $db->GetCol($rolesQuery);
			foreach ($roles as &$roleid) {
				$roleid = $db->qstr($roleid);
			}
			$query = 'SELECT t.name, count(*)
				FROM tags t
				JOIN tag_map tm ON (t.id = tm.tag_id)
				LEFT JOIN organisations org ON (org.id = tm.organisation_id)
					LEFT JOIN organisation_roles cr ON org.id = cr.organisation_id AND cr.read AND cr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN people p ON (p.id = tm.person_id)
					LEFT JOIN organisation_roles pcr ON p.organisation_id = pcr.organisation_id AND pcr.read AND pcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN opportunities opp ON (opp.id = tm.opportunity_id)
					LEFT JOIN organisation_roles oppcr ON opp.organisation_id = oppcr.organisation_id AND oppcr.read AND oppcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN tactile_activities act ON (act.id = tm.activity_id)
					LEFT JOIN organisation_roles actcr ON act.organisation_id = actcr.organisation_id AND actcr.read AND actcr.roleid IN ('.implode(', ', $roles).')
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (
					cr.roleid IS NOT NULL
					OR pcr.roleid IS NOT NULL
					OR oppcr.roleid IS NOT NULL
					OR actcr.roleid IS NOT NULL
					
					OR org.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR p.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR act.owner = ' . $db->qstr(EGS::getUsername()) . '
					
					OR org.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR p.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR act.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					
					OR (p.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (opp.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (act.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
				)
				GROUP BY t.name ORDER BY lower(t.name)';
		} else {
			$query = 'SELECT t.name, count(*) FROM tags t JOIN tag_map tm ON (t.id = tm.tag_id)
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) .'
				GROUP BY t.name ORDER BY lower(name)';
		}
		$tags = $db->getAssoc($query);
		if(count($tags) > 0) {
			$max = max($tags);
			$min = min($tags);
			
			$percent = $max / 100;
			
			$threshold = array(
				0 => $percent * 1,
				1 => $percent * 5,
				2 => $percent * 40,
				3 => $percent * 75,
				4 => $percent * 97,
				5 => 1+$percent * 100,
			);
			
			$band_size = $max / 6;
			$grouped_tags = array();
			foreach($tags as $tag => $count) {
				foreach($threshold as $candidate_band => $upper) {
					if($count <= $upper+1) {
						$band = $candidate_band;
						break;
					}
				}
				$grouped_tags[$tag] = $band;
			}
			
			if (!isModuleAdmin()) {
				$rolesQuery = 'SELECT roleid FROM hasrole WHERE username = ' . $db->qstr(EGS::getUsername());
				$roles = $tags = $db->GetCol($rolesQuery);
				foreach ($roles as &$roleid) {
					$roleid = $db->qstr($roleid);
				}
				$query = 'SELECT t.name
					FROM tags t
					JOIN tag_map tm ON (t.id = tm.tag_id)
					LEFT JOIN organisations org ON (org.id = tm.organisation_id)
						LEFT JOIN organisation_roles cr ON org.id = cr.organisation_id AND cr.read AND cr.roleid IN ('.implode(', ', $roles).')
					LEFT JOIN people p ON (p.id = tm.person_id)
						LEFT JOIN organisation_roles pcr ON p.organisation_id = pcr.organisation_id AND pcr.read AND pcr.roleid IN ('.implode(', ', $roles).')
					LEFT JOIN opportunities opp ON (opp.id = tm.opportunity_id)
						LEFT JOIN organisation_roles oppcr ON opp.organisation_id = oppcr.organisation_id AND oppcr.read AND oppcr.roleid IN ('.implode(', ', $roles).')
					LEFT JOIN tactile_activities act ON (act.id = tm.activity_id)
						LEFT JOIN organisation_roles actcr ON act.organisation_id = actcr.organisation_id AND actcr.read AND actcr.roleid IN ('.implode(', ', $roles).')
					WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
					AND (
						cr.roleid IS NOT NULL
						OR pcr.roleid IS NOT NULL
						OR oppcr.roleid IS NOT NULL
						OR actcr.roleid IS NOT NULL
						
						OR org.owner = ' . $db->qstr(EGS::getUsername()) . '
						OR p.owner = ' . $db->qstr(EGS::getUsername()) . '
						OR opp.owner = ' . $db->qstr(EGS::getUsername()) . '
						OR act.owner = ' . $db->qstr(EGS::getUsername()) . '
						
						OR org.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
						OR p.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
						OR opp.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
						OR act.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
						
						OR (p.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
						OR (opp.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
						OR (act.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					)
					GROUP BY t.name, t.created
					ORDER BY t.created DESC LIMIT 10';
			} else {
				$query = 'SELECT name from tags t WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' 
					ORDER BY created DESC LIMIT 10';
			}
			$new = $db->GetCol($query);
		}
		else {
			$new = $grouped_tags = array();
		}
		$this->view->set('tags', $grouped_tags);
		$this->view->set('new_tags', $new);
	}
	
	/**
	 * Accessed when selecting a tag from the main-cloud, shows all companies/people/activities/opportunities matching the tag
	 * side-bar will show a list of tags that the matching things are also tagged with, to allow drilldown (come back to this method with more tags)
	 * 
	 **/
	public function by_tag() {
		$user = CurrentlyLoggedInUser::Instance();
		$this->view->set('user', $user);
		
		if (empty($this->_data['tag'])) {
			sendTo('tags');
			return;
		}
		
		$db = DB::Instance();
		if (!is_array($this->_data['tag'])) {
			$this->view->set('current_tag', $this->_data['tag']);
			$this->_data['tag'] = array($this->_data['tag']);
		}
		$this->view->set('selected_tags', $this->_data['tag']);
		$tag_string = implode(',', array_map(array($db, 'qstr'), $this->_data['tag']));
		$this->view->set('tag', $tag_string);
		
		$item_types = array(
			'organisations'	=> array(
				'model'		=> 'Organisation',
				'class'		=> 'company'
			),
			'people'		=> array(
				'model'		=> 'Person',
				'class'		=> 'person'
			),
			'opportunities'	=> array(
				'model'		=> 'Opportunity',
				'class'		=> 'opportunity'
			),
			'activities'	=> array(
				'model'		=> 'Activity',
				'class'		=> 'activity'
			)
		);
		$this->view->set('item_types', $item_types);
		
		if (isset($this->_data['for']) && !in_array($this->_data['for'], array_keys($item_types))) {
			sendTo('tags');
			return;
		}
		
		$items = array();
		$num_pages = array();
		$cur_page = array();
		$current_query = array();
		
		foreach ($item_types as $item => $type) {
			// Fetch all items of all types tagged with foo, or just for one type 
			if (!isset($this->_data['for']) || $this->_data['for'] == $item) {
				$taggable = new TaggedItem(DataObject::Construct($type['model']));
				$collection = $taggable->getCollectionByTags($this->_data['tag']);
				$items[$item] = $collection;
				$cur_page[$item] = $collection->cur_page;
				$num_pages[$item] = $collection->num_pages;
				$query = http_build_query(array('tag'=>$this->_data['tag'],'for'=>$item));
				$query = preg_replace('/&/', '&amp;', $query);
				$current_query[$item] = $query;
			}
		}
		
		$this->view->set('cur_page', $cur_page);
		$this->view->set('num_pages', $num_pages);
		$this->view->set('current_query', $current_query);
		
		if (!isset($this->_data['for'])) {
			$types_with_results = array();
			$this->view->set('items', $items);
			
			// What's on the page?
			foreach ($items as $key => $item) {
				if ($item->count()) {
					$types_with_results[] = $key;
				}
			}
			$this->view->set('types_with_results', $types_with_results);
			
			// Establish the first and last columns
			if (count($types_with_results) > 0) {
				$this->view->set('first', $types_with_results[0]);
				$this->view->set('last', $types_with_results[count($types_with_results)-1]);
			}
			
			// Set the column width
			$nothing_to_display = false;
			if (empty($types_with_results)) {
				$nothing_to_display = true;
			} else {
				$this->view->set('column_width', 100 / count($types_with_results));
			}
			$this->view->set('nothing_to_display', $nothing_to_display);
			
		} else {
			// Probably an ajax call
			$for = $this->_data['for'];
			$this->setTemplateName('ajax_table');
			$this->view->set('layout', 'blank');
			
			$this->view->set('title', ucfirst($for));
			$this->view->set('class', $item_types[$for]['class']);
			$this->view->set('for', $for);
			$this->view->set('items', $items[$for]);
		}
		
		// Fetch drill down list
		if (!isModuleAdmin()) {
			$rolesQuery = 'SELECT roleid FROM hasrole WHERE username = ' . $db->qstr(EGS::getUsername());
			$roles = $tags = $db->GetCol($rolesQuery);
			foreach ($roles as &$roleid) {
				$roleid = $db->qstr($roleid);
			}
			$query = 'SELECT t.name, count(*)
				FROM tags t
				JOIN tag_map tm ON (t.id = tm.tag_id)
				LEFT JOIN organisations org ON (org.id = tm.organisation_id)
					LEFT JOIN organisation_roles cr ON org.id = cr.organisation_id AND cr.read AND cr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN people p ON (p.id = tm.person_id)
					LEFT JOIN organisation_roles pcr ON p.organisation_id = pcr.organisation_id AND pcr.read AND pcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN opportunities opp ON (opp.id = tm.opportunity_id)
					LEFT JOIN organisation_roles oppcr ON opp.organisation_id = oppcr.organisation_id AND oppcr.read AND oppcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN tactile_activities act ON (act.id = tm.activity_id)
					LEFT JOIN organisation_roles actcr ON act.organisation_id = actcr.organisation_id AND actcr.read AND actcr.roleid IN ('.implode(', ', $roles).')
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (
					cr.roleid IS NOT NULL
					OR pcr.roleid IS NOT NULL
					OR oppcr.roleid IS NOT NULL
					OR actcr.roleid IS NOT NULL
					
					OR org.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR p.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR act.owner = ' . $db->qstr(EGS::getUsername()) . '
					
					OR org.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR p.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR act.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					
					OR (p.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (opp.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (act.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
				)
				AND hash IN
				(SELECT hash FROM tag_map tm
				JOIN tags t ON t.id=tm.tag_id 
				WHERE t.name IN (' . $tag_string . ')
				AND t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				GROUP BY hash HAVING (count(*) = ' . count($this->_data['tag']) . ')) GROUP BY t.name
				ORDER BY lower(t.name)';
		} else {
			$query = "SELECT t.name, count(*) FROM tags t
			JOIN tag_map tm ON t.id=tm.tag_id
			WHERE hash IN
				(SELECT hash FROM tag_map tm
				JOIN tags t ON t.id=tm.tag_id 
				WHERE t.name IN ($tag_string)
				AND t.usercompanyid = '".EGS::getCompanyId()."'
				GROUP BY hash HAVING (count(*) = '".count($this->_data['tag'])."')) GROUP BY t.name
				ORDER BY lower(t.name)";
		}
		$filter_by = $db->getAll($query);
		$this->view->set('filter_by', $filter_by);
		
		$is_import = is_array($this->_data['tag']) && count($this->_data['tag']) == 1 &&
			!empty($this->_data['tag'][0]) && preg_match('/^Import\d{8}/', $this->_data['tag'][0]); 
		$this->view->set('is_import', $is_import);
	}
	
	/**
	 * Confirmation page for the deletion of items (orgs, opps, etc.) by one or more tags
	 * Should be accessible by admins only
	 *
	 */
	public function delete_items() {
		// Permission to view this page?
		$user = CurrentlyLoggedInUser::Instance();
		if (!$user->isAdmin()) {
			sendTo('tags');
			return;
		}
	
		if (empty($this->_data['tag'])) {
			sendTo('tags');
			return;
		}
		
		$tag = new Omelette_Tag();
		$this->view->set('Tag', $tag);
		
		$db = DB::Instance();
		if(!is_array($this->_data['tag'])) {
			$this->_data['tag'] = array($this->_data['tag']);
		}
		$this->view->set('selected_tags', $this->_data['tag']);
		
		$tag_string = implode(', ', array_map(array($db, 'qstr'), $this->_data['tag']));
		$this->view->set('tag_string', $tag_string);
		
		$item_types = array(
			'organisations'	=> array(
				'model'		=> 'Organisation',
				'class'		=> 'company',
				'table'		=> 'organisations',
				'ref_key'	=> 'organisation_id'
			),
			'people'		=> array(
				'model'		=> 'Person',
				'class'		=> 'person',
				'table'		=> 'people',
				'ref_key'	=> 'person_id'
			),
			'opportunities'	=> array(
				'model'		=> 'Opportunity',
				'class'		=> 'opportunity',
				'table'		=> 'opportunities',
				'ref_key'	=> 'opportunity_id'
			),
			'activities'	=> array(
				'model'		=> 'Activity',
				'class'		=> 'activity',
				'table'		=> 'tactile_activities',
				'ref_key'	=> 'activity_id'
			)
		);
		
		if (isset($this->_data['for']) && !in_array($this->_data['for'], array_keys($item_types))) {
			sendTo('tags');
			return;
		}
		$for = $this->_data['for'];
		$this->view->set('for', $for);
		
		// Keep track of the queries
		$queries = array();
		
		// Count the objects we are trying to delete
		$taggable = DataObject::Construct($item_types[$for]['model']);
		$tagged = new TaggedItem($taggable);
		
		$search_query = $taggable->getQueryForTagSearch($tag_string, count($this->_data['tag']));
		$queries[$for][] = $search_query;
		$count_query_string = $search_query->countQuery('ti.id');
		
		$item_count = $db->getOne($count_query_string);
		$this->view->set('count', $item_count);
		
		// Check cascades
		$cascade_map = array(
			'organisations'	=> array(
				'people', 'opportunities', 'activities'
			),
			'people'		=> array(
				'opportunities', 'activities'
			),
			'opportunities'	=> array(
				'activities'
			),
			'activities'	=> array()
		);
		$will_also_delete = array();
		
		foreach ($cascade_map[$for] as $cascade) {
			// Select the parent
			$taggable = DataObject::Construct($item_types[$for]['model']);
			$select_query = $taggable->getQueryForTagSearch($tag_string, count($this->_data['tag']));
			$select_query->select_simple(array('ti.id'));
			
			// Use the above as a sub-query to select the children
			$count_query = new QueryBuilder($db, $item_types[$cascade]['model']);
			$count_query->from($item_types[$cascade]['table']);
			$count_query->sub_select($item_types[$for]['ref_key'], 'IN', $select_query);
			
			$count_query_string = $count_query->countQuery('ti.id');
			$result = $db->getOne($count_query_string);
			if ($result > 0) {
				$will_also_delete[$cascade]['count'] = $result;
				$queries[$cascade][] = $count_query;
			}
			
			// Do a second level of cascade checking
			foreach ($cascade_map[$cascade] as $cascade_b) {
				// Select the parent
				$select_query = $count_query;
				$select_query->select_simple(array('id'));
				
				// Use the above as a sub-query to select the children
				$count_query = new QueryBuilder($db, $item_types[$cascade_b]['model']);
				$count_query->from($item_types[$cascade_b]['table']);
				$count_query->sub_select($item_types[$cascade]['ref_key'], 'IN', $select_query);
				
				$count_query_string = $count_query->countQuery('ti.id');
				$result = $db->getOne($count_query_string);
				if ($result > 0) {
					$will_also_delete[$cascade]['children'][$cascade_b]['count'] = $result;
					$queries[$cascade_b][] = $count_query;
				}
			}
		}
		$this->view->set('will_also_delete', $will_also_delete);
		
		// Scan items to delete for items that can't be deleted
		$user_people = array();
		$account_orgs = array();
		foreach ($queries as $for => $query_set) {
			foreach ($query_set as $query) {
				switch ($for) {
					case 'people':
						// Check that we're not trying to delete a user's person
						$query->from($item_types[$for]['table'] . ' ti');
						$query->join('users u', 'u.person_id = ti.id');
						$query->select_simple(array('ti.firstname', 'ti.surname'));
						$query->where(new Constraint('u.username', 'IS NOT', 'NULL'));
						$query->group_by(array('ti.firstname', 'ti.surname'));
						$people = $db->getAll($query);
						foreach ($people as $person) {
							$name = $person['firstname'] . ' ' . $person['surname'];
							$user_people[$name] = $name;
						}
						break;
					case 'organisations':
						// Check that we're not trying to delete an account's company
						$query->from($item_types[$for]['table'] . ' ti');
						$query->join('tactile_accounts ta', 'ta.organisation_id = ti.id');
						$query->select_simple(array('ti.name', 'ta.company'));
						$query->where(new Constraint('ta.id', 'IS NOT', 'NULL'));
						$query->group_by(array('ta.company'));
						$orgs = $db->getAll($query);
						foreach ($orgs as $org) {
							$account_orgs[$org['company']] = $org['name']; 
						}
						break;
				}
			}
		}
		$this->view->set('user_people', $user_people);
		$this->view->set('account_orgs', $account_orgs);
	}
	
	/**
	 * Process the deletion of items by tag string
	 *
	 */
	public function process_delete_items() {
		$flash = Flash::Instance();
		
		// Permission to view this page?
		$user = CurrentlyLoggedInUser::Instance();
		if (!$user->isAdmin()) {
			$flash->addError("You don't have permission to view this page");
			sendTo('tags');
			return;
		}
		
		$item_types = array(
			'organisations'	=> array(
				'model'		=> 'Organisation',
				'class'		=> 'company'
			),
			'people'		=> array(
				'model'		=> 'Person',
				'class'		=> 'person'
			),
			'opportunities'	=> array(
				'model'		=> 'Opportunity',
				'class'		=> 'opportunity'
			),
			'activities'	=> array(
				'model'		=> 'Activity',
				'class'		=> 'activity'
			)
		);
		
		// Valid item type?
		if (isset($this->_data['for']) && !in_array($this->_data['for'], array_keys($item_types))) {
			$flash->addError("Invalid item type");
			sendTo('tags');
			return;
		}
		$for = $this->_data['for'];
		
		// Need a tag string
		if (empty($this->_data['tag'])) {
			$flash->addError("You must specify at least one tag");
			sendTo('tags');
			return;
		}
		
		if (!is_array($this->_data['tag'])) {
			$this->_data['tag'] = array($this->_data['tag']);
		}
		
		// Did they check the box?
		if (!isset($this->_data['confirm'])) {
			$flash->addError('Confirmation box not ticked. No action performed.');
			$params = array('for'=>$for);
			$i = 0;
			foreach ($this->_data['tag'] as $tag) {
				$index = 'tag['.$i.']';
				$i++;
				$params[$index] = $tag;
			}
			sendTo('tags', 'delete_items', null, $params);
			return;
		}
		
		$db = DB::Instance();
		$tag_string = implode(', ', array_map(array($db, 'qstr'), $this->_data['tag']));

		
		// Ok, should have everything we need now
		
		$taggable = DataObject::Construct($item_types[$for]['model']);
		$tagged = new TaggedItem($taggable);
		
		// Establish how many items we are going to delete
		$query = $taggable->getQueryForTagSearch($tag_string, count($this->_data['tag']));
		$query_string = $query->countQuery('ti.id');
		$count = $db->getOne($query_string);
		
		if ($count > 50) {
			// This'll take a while...
			$task = new DelayedTaggedItemDeletion();
			$task->setFor($for);
			$task->setTaggable($item_types[$for]['model']);
			$task->setTag($this->_data['tag']);
			$task->save();
			$params = array();
			foreach ($this->_data['tag'] as $i => $tag) {
				$params['tag['.$i.']'] = $tag;
			}
			$params['for'] = $for;
			sendTo('tags', 'deletion_requested', null, $params);
		} else {
			$success = $tagged->deleteAllByTags($this->_data['tag'], count($this->_data['tag']));
			if ($success) {
				$flash->addMessage('Deletion successful');
				foreach ($this->_data['tag'] as $name) {
					$t = new Omelette_Tag();
					if (FALSE !== ($t->loadBy('name', $name))) {
						Omelette_Tag::cleanOrphans($t);
					}
				}
			} else {
				$flash->addError('Deletion was unsuccessful');
			}
			sendTo('tags');
		}
	}
	
	/**
	 * Search for tags matching the supplied prefix - used with the autocomplete when adding tags to things
	 * 
	 **/
	public function search() {
		if(!isset($this->_data['name'])) {
			sendTo();
			return;
		}
		$name = $this->_data['name'];
		$tags = new Omelette_TagCollection();
		$sh = new SearchHandler($tags,false);
		$sh->extract();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('name','ILIKE',$name.'%'));
		$sh->addConstraintChain($cc);
		$sh->setLimit(10,0);
		$tags->load($sh);
		$this->view->set('type', 'tag');
		$this->view->set('field','name');
		$this->view->set('items',$tags);		
	}
	
	/**
	 * Display the rename / merge page
	 *
	 */
	public function rename() {
		if(empty($this->_data['old_tag'])) {
			sendTo('tags');
			return;
		}
		$tag = DataObject::Construct('Tag');
		$tag = $tag->loadBy('name', $this->_data['old_tag']);
		if ($tag === false) {
			Flash::Instance()->addError("Tag not found");
			sendTo('tags');
			return;
		}
		$this->view->set('old_tag', $this->_data['old_tag']);
	}

	/**
	 * Users land here if they try to rename a tag to an existing one
	 *
	 */
	public function merge() {
		if (empty($this->_data['old_tag']) || empty($this->_data['new_tag'])) {
			sendTo('tags');
			return;
		}
		$this->view->set('old_tag', $this->_data['old_tag']);
		$this->view->set('new_tag', $this->_data['new_tag']);
	}
	
	/**
	 * Rename a tag, or redirect users to the merge page if target tag already exists
	 *
	 */
	public function dorename() {
		if(empty($this->_data['old_tag'])) {
			Flash::Instance()->addError("Tag missing");
			sendTo('tags');
			return;
		}
		$tag = DataObject::Construct('Tag');
		$tag = $tag->loadBy('name', $this->_data['old_tag']);
		if($tag === false) {
			Flash::Instance()->addError("Tag not found");
			sendTo('tags');
			return;
		}
		if(empty($this->_data['new_tag'])) {
			Flash::Instance()->addError('New Tag cannot be empty');
			sendTo('tags', 'rename',null, array('old_tag' => urlencode($this->_data['old_tag'])));
			return;
		}
		if($this->_data['new_tag'] == $tag->name) {
			Flash::Instance()->addError('New Tag must be different to Old Tag');
			sendTo('tags', 'rename',null, array('old_tag' => urlencode($this->_data['old_tag'])));
			return;
		}
		
		$look_ahead_tag = DataObject::Construct('Tag');
		$look_ahead_tag = $look_ahead_tag->loadBy('name', $this->_data['new_tag']);
		if ($look_ahead_tag !== false && !isset($this->_data['confirm_merge'])) {
			// Tag exists, this is a merge attempt
			sendTo('tags', 'merge', null, array('old_tag'=>urlencode($tag->name), 'new_tag'=>urlencode($look_ahead_tag->name)));
			return;
		}

		$new_tag = Omelette_Tag::loadOrCreate($this->_data['new_tag']);
		if(false !== $tag->mergeWith($new_tag)) {
			if ($look_ahead_tag !== false) {
				Flash::Instance()->addMessage('Tags successfully merged');
			} else {
				Flash::Instance()->addMessage('Tag successfully renamed');
			}
			sendTo('tags','by_tag', null, array('tag' => urlencode($new_tag->name)));
			return;
		}
		else {
			Flash::Instance()->addError('Error Renaming');
			sendTo('tags', 'rename',null, array('old_tag' => $this->_data['old_tag']));
			return;
		}
	}
	
	/**
	 * Delete a tag
	 *
	 */
	public function delete() {
		if(empty($this->_data['tag'])) {
			Flash::Instance()->addError("Tag not found");
			sendTo('tags');
			return;
		}
		$tag = DataObject::Construct('Tag');
		$tag = $tag->loadBy('name', $this->_data['tag']);
		if($tag === false) {
			Flash::Instance()->addError("Tag not found");
			sendTo('tags');
			return;
		}
		if($this->is_post) {
			if(false !== $tag->delete()) {
				Flash::Instance()->addMessage('Tag deleted');
				sendTo('tags');
				return;
			}
			Flash::Instance()->addError('Error Deleting');
			sendTo('tags', 'by_tag', null, array('tag' => $this->_data['tag']));
			return;
		}
		else {
			$this->view->set('tag', $tag);
		}
	}

	/**
	 * Redirection for tag index action menu
	 *
	 */
	public function action_select() {
		if (!isset($this->_data['tag_action'])) {
			sendTo('tags');
		} else {
			if (!is_array($this->_data['tag'])) {
				$this->_data['tag'] = array($this->_data['tag']);
			}
			switch ($this->_data['tag_action']) {
				case 'delete':
					sendTo('tags', $this->_data['tag_action'], null, array('tag'=>current($this->_data['tag'])));
					break;
				case 'rename':
					sendTo('tags', $this->_data['tag_action'], null, array('old_tag'=>current($this->_data['tag'])));
					break;
				case 'delete_organisations':
				case 'delete_people':
				case 'delete_opportunities':
				case 'delete_activities':
					preg_match('/_(\w+)$/', $this->_data['tag_action'], $matches);
					$type = $matches[1];
					$params = array();
					foreach ($this->_data['tag'] as $i => $tag) {
						$params['tag['.$i.']'] = $tag;
					}
					$params['for'] = $type;
					sendTo('tags', 'delete_items', null, $params);
					break;
				default:
					sendTo('tags');
			}
		}
	}
	
	public function deletion_requested() {
		if (empty($this->_data['tag'])) {
			sendTo('tags');
			return;
		}
		if (empty($this->_data['for'])) {
			sendTo('tags');
			return;
		}
		
		if (!is_array($this->_data['tag'])) {
			$this->_data['tag'] = array($this->_data['tag']);
		}
		$this->view->set('selected_tags', $this->_data['tag']);
		$this->view->set('for', $this->_data['for']);
	}
	
	public function tag_everything() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		if (!isModuleAdmin()) {
			Flash::Instance()->addError('Only admins can access this feature');
			sendTo('tags');
			return;
		}
		if (empty($this->_data['tag'])) {
			Flash::Instance()->addError('Please enter a tag');
			sendTo('tags');
			return;
		}
		$tag = $this->_data['tag'];
		
		// Make sure the tag doesn't already exist
		$db = DB::Instance();
		$query = "SELECT t.name FROM tags t JOIN tag_map tm ON t.id = tm.tag_id WHERE usercompanyid = " .
			$db->qstr(EGS::getCompanyId()) . " AND t.name = " . $db->qstr($tag) . " GROUP BY t.name";
		$existing_tag = $db->getOne($query);
		if (!empty($existing_tag)) {
			Flash::Instance()->addError('The tag "' . $tag . '" already exists. Please choose one which does not.');
			sendTo('tags');
			return;
		}
		
		$db->startTrans();
		
		$classes = array('Organisation', 'Person', 'Opportunity', 'Activity');
		foreach ($classes as $class) {
			$ti = new TaggedItem(DataObject::Construct($class));
			switch ($class) {
				case 'Organisation':
					$query = "SELECT id FROM organisations WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) .
						' AND id != ' . $db->qstr(EGS::getCompanyId());
					break;
				case 'Person':
					$query = "SELECT id FROM people WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) .
						' AND id NOT IN (SELECT person_id FROM users WHERE username like ' . $db->qstr('%//'.Omelette::getUserSpace()) . ')';
					break;
				case 'Opportunity':
					$query = "SELECT id from opportunities WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId());
					break;
				case 'Activity':
					$query = "SELECT id from tactile_activities WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId());
					break;
				default:
					throw new Exception('Unexpected class in tag everything action!');
			}
			$ids = $db->getCol($query);
			if (!empty($ids)) {
				$ti->addTagsInBulk(array($tag), $ids);
			}
		}
		
		$db->completeTrans();
		sendTo('tags', 'by_tag', null, array('tag[0]' => $tag));
	}
	
}
