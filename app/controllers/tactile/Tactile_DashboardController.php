<?php
class Tactile_DashboardController extends Controller {
	
	public function index() {
		$this->_loadGraph();
		$this->_loadDropboxEmails();
		$this->_loadActivitiesOverview();
		$this->_loadOpportunitiesOverview();
		
		UsageWarningHelper::displayUsageWarning($this->view);
		
		$restriction = 'notes_emails_acts';
		if (!empty($this->_data['view'])) {
			switch ($this->_data['view']) {
				case 'notes_emails':
					$restriction = 'notes_emails';
					Omelette_Magic::saveChoice('dashboard_timeline_restriction', $restriction, EGS::getUsername());
					break;
				case 'custom':
					$restriction = $this->_data['view'];
					Omelette_Magic::saveChoice('dashboard_timeline_restriction', $restriction, EGS::getUsername());
					break;
				default:
				case 'notes_emails_acts':
					$restriction = $this->_data['view'];
					Omelette_Magic::saveChoice('dashboard_timeline_restriction', $restriction, EGS::getUsername());
					break;
			}
		} else {
			$restriction = Omelette_Magic::getValue('dashboard_timeline_restriction', EGS::getUsername(), 'notes_emails_acts');
		}
		$this->view->set('restriction', $restriction);
		
		switch ($restriction) {
			case 'custom':
				$this->_loadCustomTimeline();
				break;
			case 'notes_emails_acts':
				$this->_loadNotesEmailsActivities();
				break;
			case 'notes_emails':
			default:
				$this->_loadNotesEmails();
				break;
		}
		
		$this->view->set('timeline_rss', CurrentlyLoggedInUser::Instance()->getTimelineFeedAddress());
		$this->view->set('timeline_view', Omelette_Magic::getValue('timeline_view', EGS::getUsername(), 'list'));
	}
	
	public function welcome() {
	}
	
	/**
	 * Loads the pipeline graph if no preference has been stated
	 */
	protected function _loadGraph() {
		require_once('Charts/Tactile.php');
		$chart = new Charts_Tactile();
		$graph_method = Charts_Tactile::getDashboardGraphMethod();

		if (!is_callable(array($chart, $graph_method))) {
			$graph_method = 'pipeline';
		}
		$this->view->set('graph_url', '/graphs/'.$graph_method);
		/*if (is_callable(array($chart, $graph_method)) && FALSE !== call_user_func(array($chart, $graph_method))) {
			$this->view->set('graph_title', $chart->getTitle());
			$current_user = CurrentlyLoggedInUser::Instance();
			$graph = $chart->getGraph();
			
			// If the graph has data, the welcome message is displayed, 
			if (!$graph->hasData()
				&& !Omelette_Magic::getAsBoolean('hide_welcome_message', $current_user->getRawUsername())
				&& Omelette_Magic::getAsBoolean('show_sample_graph', $current_user->getRawUsername(), 't', 't')) {
				// Show a sample image instead
			} else {
				// Dashboard-specific styles
				$graph
					->addAxisStyle(array(0, '', 9))
					->addAxisStyle(array(1, '', 9));
				$this->view->set('graph', $graph);
			}
			
			$this->view->set('graph_url', $chart->getUrl());
		}*/
	}
	
	protected function _loadDropboxEmails() {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM emails WHERE owner = " . $db->qstr($user->getRawUsername()) .
			" AND organisation_id IS NULL AND person_id IS NULL AND opportunity_id IS NULL";
		$count = $db->getOne($sql);
		
		$this->view->set('unassigned_emails', $count);
	}
	
	/**
	 * Shows the 10 most recently added notes and emails that aren't marked as private by somebody else
	 * @return void
	 */
	protected function _loadNotesEmails() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$timeline->addType('note');
		$timeline->addType('email');
		
		$timeline->load($cc);
		$this->view->set('activity_timeline', $timeline);
	}
	
	/**
	 * Shows the 10 most recently added notes, emails, and activities that aren't marked as private by somebody else
	 * @return void
	 */
	protected function _loadNotesEmailsActivities() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('completed_activity');
		
		$timeline->load($cc);
		$this->view->set('activity_timeline', $timeline);
	}
	
	protected function _loadCustomTimeline() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$cc_mine = new ConstraintChain();
		$cc_mine->add(new Constraint('owner', '=', EGS::getUsername()));
		$cc_mine->add(new Constraint('assigned_to', '=', EGS::getUsername()), 'OR');
		
		$timeline_prefs = TimelinePreference::getAll(EGS::getUsername());
		foreach ($timeline_prefs as $item => $types) {
			foreach ($types as $type => $value) {
				switch ($value) {
					// Don't bother doing anything unless the value is 'all' or 'mine'
					case 'all':
					case 'mine':
						switch ($item) {
							case 'activities':
								switch ($type) {
									case 'new':
										$timeline->addType('new_activity', 'all' == $value);
										break;
									case 'completed':
										$timeline->addType('completed_activity', 'all' == $value);
										break;
									case 'overdue':
										$timeline->addType('overdue_activity', 'all' == $value);
										break;
								}
								break;
								
							case 'opportunities':
								$timeline->addType('opportunity', 'all' == $value);
								break;
								
							case 'notes':
								$timeline->addType('note', 'all' == $value);
								break;
								
							case 'emails':
								$timeline->addType('email', 'all' == $value);
								break;
								
							case 'files':
								$timeline->addType('s3file', 'all' == $value);
								break;
						}
						break;
				}
			}
		}
		
		$timeline->load($cc, 1, true);
		$this->view->set('activity_timeline', $timeline);
	}
	
	/**
	 * Loads activities assigned to the current user, and filters into 3 categories
	 * @return void
	 */
	protected function _loadAssignedActivities() {
		/*$overdue_activities = new Tactile_ActivityCollection();
		$sh = new SearchHandler($overdue_activities, false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('act.completed', 'is', 'NULL'));
		$cc->add(new Constraint('act.assigned_to', '=', EGS::getUsername()));
		$cc->add(new Constraint('act.overdue', '=', 'true'));
		$sh->addConstraintChain($cc);
		$sh->setOrderBy('act.due', 'ASC');
		$sh->perpage = 30;
		$overdue_activities->load($sh);
		$this->view->set('overdue_activities', $overdue_activities);
		
		$due_activities = new Tactile_ActivityCollection();
		$sh = new SearchHandler($due_activities, false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('act.completed', 'is', 'NULL'));
		$cc->add(new Constraint('act.assigned_to', '=', EGS::getUsername()));
		$cc->add(new Constraint('act.overdue', '=', 'false'));
		$sh->addConstraintChain($cc);
		$sh->setOrderBy('act.due', 'ASC');
		$sh->perpage = 30;
		$due_activities->load($sh);
		$this->view->set('due_activities', $due_activities);
		
		//$this->view->set('overdue_activities',new OverdueFilter($activities));
		$this->view->set('due_activities',new TodayFilter($due_activities));
		$this->view->set('upcoming_activities',new LimitIterator(new FutureFilter($due_activities),0,10));
		$this->view->set('later_activities',new LaterFilter($due_activities));
		*/
	}
	
	protected function _loadActivitiesOverview() {
		$this->view->set('overdue_activities', $this->_getOverdueActivities());
		$this->view->set('todays_activities', $this->_getTodaysActivities());
		$this->view->set('later_activities', $this->_getLaterActivities());
	}
	
	protected function _getOverdueActivities() {
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM tactile_activities a, users u WHERE a.assigned_to = " . $db->qstr(EGS::getUsername()) .
			' AND ((a.time IS NULL AND a.date < now()::date) OR (a.time IS NOT NULL AND (a.date + a."time") < now()))
			AND NOT later AND a.completed IS NULL AND a.assigned_to = u.username';
		/*$sql = "SELECT count(*) FROM tactile_activities a, users u WHERE a.assigned_to = " . $db->qstr(EGS::getUsername()) .
			' AND ((a.time IS NULL AND a.date < now()::date) OR (a.time IS NOT NULL AND (a.date + a."time") < timezone(u.timezone::text, now()::timestamp without time zone)))
			AND NOT later AND a.completed IS NULL AND a.assigned_to = u.username';*/
		$count = $db->getOne($sql);
		
		return $count;
	}
	
	protected function _getTodaysActivities() {
		// TODO: Fix date to be time function and check TZ work
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM tactile_activities a, users u WHERE a.assigned_to = " . $db->qstr(EGS::getUsername()) .
			' AND ((a.time IS NULL AND a.date = now()::date) OR (a.time IS NOT NULL AND (a.date + a."time")::date = now()::date)) 
			AND NOT later AND a.completed IS NULL AND a.assigned_to = u.username';
		$count = $db->getOne($sql);

		return $count;
	}
	
	protected function _getLaterActivities() {
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM tactile_activities WHERE assigned_to = " . $db->qstr(EGS::getUsername()) .
			" AND later AND completed IS NULL";
		$count = $db->getOne($sql);
		
		return $count;
	}
	
	protected function _loadOpportunitiesOverview() {
		$this->view->set('open_opportunities', $this->_getOpenOpportunities());
		$this->view->set('won_opportunities', $this->_getWonOpportunities());
		$this->view->set('lost_opportunities', $this->_getLostOpportunities());
	}
	
	protected function _getOpenOpportunities() {
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM opportunities o LEFT JOIN opportunitystatus os ON o.status_id = os.id 
			WHERE NOT archived AND assigned_to = " . $db->qstr(EGS::getUsername()) . " AND os.open";
		$count = $db->getOne($sql);
		
		return $count;
	}
	
	protected function _getWonOpportunities() {
		// TODO: Restrict to "recent"
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM opportunities o LEFT JOIN opportunitystatus os ON o.status_id = os.id LEFT JOIN users u ON u.username = " . $db->qstr(EGS::getUsername()) . "
			WHERE NOT archived AND assigned_to = " . $db->qstr(EGS::getUsername()) . " AND os.won";
		$count = $db->getOne($sql);
		
		return $count;
	}
	
	protected function _getLostOpportunities() {
		// TODO: Restrict to "recent"
		$db = DB::Instance();
		$sql = "SELECT count(*) FROM opportunities o LEFT JOIN opportunitystatus os ON o.status_id = os.id LEFT JOIN users u ON u.username = " . $db->qstr(EGS::getUsername()) . "
			WHERE NOT archived AND assigned_to = " . $db->qstr(EGS::getUsername()) . " AND NOT os.won AND NOT os.open";
		$count = $db->getOne($sql);
		
		return $count;
	}
}
