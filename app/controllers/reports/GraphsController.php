<?php

require_once('Charts/Tactile.php');

class GraphsController extends Controller {

	/**
	 * Constructor
	 *
	 * @param String $module
	 * @param View $view
	 */	
	public function __construct($module,$view) {
		parent::__construct($module,$view);
	}

	
	private function _setPickerRange($user = null) {
		$db = DB::Instance();
		
		$start_year = $db->getOne(
			"SELECT
				to_char(MIN(o.enddate), 'YYYY')
			FROM
				opportunities o,
				opportunitystatus s 
			WHERE o.status_id = s.id 
			AND s.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
			AND s.open = false
			AND s.won = true"
		);
		
		if (is_null($start_year)) {
			$this->view->set('start_year', date('Y'));
		} else {
			$this->view->set('start_year', $start_year);
		}
		
		$this->view->set('end_year', date('Y'));
	}
	
	private function _parseDate($save_as = null) {
		$user = CurrentlyLoggedInUser::Instance();
		$timezone = $user->getTimezoneString();
		$date_format = EGS::getDateFormat();
		
		if (!empty($this->_data['date']) && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $this->_data['date'], $matches)) {
			if ($date_format === 'd/m/Y') {
				$date_string = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
			} else {
				// American format
				$date_string = "{$matches[3]}-{$matches[1]}-{$matches[2]}";
			}
			
			$date = strtotime($date_string . ' ' . $timezone);
		} else {
			if (is_null($save_as)) {
				$date = strtotime(date('Y-m-').'01 ' . $timezone);
			} else {
				$date = Omelette_Magic::getValue($save_as.'_report_date', EGS::getUsername(), strtotime(date('Y-m-').'01 ' . $timezone));
			}
		}
		if (!is_null($save_as)) {
			Omelette_Magic::saveChoice($save_as.'_report_date', $date, EGS::getUsername());
		}
		$this->view->set('chart_date', date($date_format, (int)$date));
		
		return (int)$date;
	}
	
	private function _parseUser($save_as = null) {
		if ((!CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() || CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) && CurrentlyLoggedInUser::Instance()->isAdmin()) {
			$this->view->set('show_user_box', true);
			
			$cc = new ConstraintChain();
			$cc->add(new Constraint('enabled', '=', 'true'));
			
			$user = new User();
			$users = $user->getAll($cc);
			unset($user);
			$user_list = array(
				'!' => 'All Active Users',
				'*' => 'All Users (including disabled)'
			);
			
			foreach ($users as $user) {
				$x = split('//', $user);
				$user_list[$x[0]] = $x[0];
			}
			
			// Add groups
			$db = DB::Instance();
			$groups = $db->getCol(
				"SELECT name " .
				"FROM roles r " .
				"JOIN hasrole hr ON hr.roleid = r.id AND hr.username IN (SELECT username FROM users WHERE username LIKE ".$db->qstr('%//'.Omelette::getUserspace())." AND enabled) " .
				"WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " " .
				"AND r.name NOT LIKE '%//%' GROUP BY name"
			);
			if (is_array($groups) && !empty($groups)) {
				foreach ($groups as $group) {
					$user_list['@'.$group] = $group;
				}
			}

			$this->view->set('user_list', $user_list);
			
			if (isset($this->_data['user']) && in_array($this->_data['user'], $user_list)) {
				$user = $this->_data['user'] . '//' . CurrentlyLoggedInUser::Instance()->getAccount()->site_address;
				$this->view->set('selected_user', $this->_data['user']);
			} elseif (isset($this->_data['user']) && ($this->_data['user'] == '*' || $this->_data['user'] == '!')) {
				$user = $this->_data['user'];
				$this->view->set('selected_user', $this->_data['user']);
			} elseif (isset($this->_data['user']) && preg_match('/^@(.+)$/', $this->_data['user'], $matches)) {
				$user = $this->_data['user'];
				$this->view->set('selected_user', $this->_data['user']);
			} else {
				if ($save_as != null) {
					$user = Omelette_Magic::getValue($save_as.'_report_user', EGS::getUsername(), EGS::getUsername());
				} else {
					$user = EGS::getUsername();
				}
				
				$x = split('//', $user);
				$this->view->set('selected_user', $x[0]);
			}
		} else {
			$user = EGS::getUsername();
		}
		
		if($save_as != null) {
			Omelette_Magic::saveChoice($save_as.'_report_user', $user, EGS::getUsername());	
		}
		return $user;
	}
	
	public function index() {
		$graph_method = Omelette_Magic::getValue('dashboard_graph', EGS::getUsername(), 'sample');
		//$graph_method = Charts_Tactile::getDashboardGraphMethod();
		$chart = new Charts_Tactile();
		if (is_callable(array($chart, $graph_method)) && FALSE !== call_user_func(array($chart, $graph_method))) {
			if ($this->view->is_json) {
				$chart->$graph_method();
				$this->view->set('graph_name', $chart->getTitle());
				$this->view->set('graph_src', $chart->getGraph()->outputSrc(300, 170));
			} else {
				sendTo(preg_replace('/^\//', '', $chart->getUrl()));
			}
		} else {
			if ($this->view->is_json) {
				$this->view->set('graph_name', 'Sample Graph');
				$this->view->set('graph_src', '/graphics/tactile/sample_graph.png');
			} else {
				sendTo('graphs/pipeline');
			}
		}
	}

	
	public function pin_to_dashboard() {
		$flash = Flash::Instance();
		if (!isset($this->_data['chart_method'])) {
			$flash->addError('Missing graph preference parameter');
			sendTo();
			return;
		}
		$method = $this->_data['chart_method'];
		$valid_methods = array(
			'pipeline', 'salesHistory',
			'oppsBySourceQty', 'oppsBySourceCost',
			'oppsByTypeQty', 'oppsByTypeCost',
			'oppsByStatusQty'
		);

		if (!in_array($method, $valid_methods)) {
			$flash->addError('Invalid graph preference parameter');
			sendTo();
			return;
		}
		
		if (FALSE === Omelette_Magic::saveChoice('dashboard_graph', $method, EGS::getUsername())) {
			$flash->addError('Error saving graph preference');
		} else {
			//$flash->addMessage('Graph preference saved');
		}
		sendTo();
	}
	
	
	public function sales_history() {
		$this->_setPickerRange();
		$date = $this->_parseDate();
		$user = $this->_parseUser();
		
		$this->view->set('pinned', ('salesHistory' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->salesHistory($date, $user)->getGraph();
		$this->view->set('sales_history', $graph);
		
		$data = $chart->getData();
		$this->view->set('sales_history_data', $data);
	}
	
	
	public function pipeline() {
		Omelette_Magic::saveChoice(
			'show_sample_graph', 
			false, 
			CurrentlyLoggedInUser::Instance()->getRawUsername()
		);
		
		$user = $this->_parseUser('pipeline');
		Omelette_Magic::saveChoice('report_user', $user, EGS::getUsername());	
		
		$this->view->set('pinned', ('pipeline' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->pipeline($user)->getGraph();
		$this->view->set('pipeline', $graph);
		
		$data = $chart->getData();
		$this->view->set('pipeline_data', $data);
	}
	
	
	public function opps_by_status_qty() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}
		
		$user = $this->_parseUser();
		
		$this->view->set('end_year', date('Y'));
		
		$this->view->set('pinned', ('oppsByStatusQty' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->oppsByStatusQty($user)->getGraph();
		$this->view->set('opps_by_status_qty', $graph);
		
		$data = $chart->getData();
		$this->view->set('opps_by_status_qty_data', $data);
	}
	
	
	public function opps_by_type_qty() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}
		
		$user = $this->_parseUser();
		
		$this->view->set('pinned', ('oppsByTypeQty' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->oppsByTypeQty($user)->getGraph();
		$this->view->set('opps_by_type_qty', $graph);
		
		$data = $chart->getData();
		$this->view->set('opps_by_type_qty_data', $data);
	}
	
	
	public function opps_by_type_cost() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}
		
		$user = $this->_parseUser();
		
		$this->view->set('pinned', ('oppsByTypeCost' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->oppsByTypeCost($user)->getGraph();
		$this->view->set('opps_by_type_cost', $graph);
		
		$data = $chart->getData();
		$this->view->set('opps_by_type_cost_data', $data);
	}
	
	
	public function opps_by_source_qty() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}
		
		$user = $this->_parseUser();
		
		$this->view->set('pinned', ('oppsBySourceQty' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->oppsBySourceQty($user)->getGraph();
		$this->view->set('opps_by_source_qty', $graph);
		
		$data = $chart->getData();
		$this->view->set('opps_by_source_qty_data', $data);
	}

	
	public function opps_by_source_cost() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
		}
		
		$user = $this->_parseUser();
		
		$this->view->set('pinned', ('oppsBySourceCost' == Charts_Tactile::getDashboardGraphMethod()));
		
		$chart = new Charts_Tactile();
		$graph = $chart->oppsBySourceCost($user)->getGraph();
		$this->view->set('opps_by_source_cost', $graph);
		
		$data = $chart->getData();
		$this->view->set('opps_by_source_cost_data', $data);
	}
	
	public function pipeline_report() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}

		$user = $this->_parseUser('pipe_line');

		$include_old = Omelette_Magic::getValue('include_old_pipeline_report', EGS::getUsername());

		if(isset($this->_data['include_old']) && $this->_data['include_old'] == "yes") {
			$include_old = 'yes';
			Omelette_Magic::saveChoice('include_old_pipeline_report', $include_old, EGS::getUsername());
		} else if (isset($this->_data['include_old'])) {
			$include_old = 'no';
			Omelette_Magic::saveChoice('include_old_pipeline_report', $include_old, EGS::getUsername());
		}

		$chart = new Charts_Tactile();		
		$chart->pipelineReport($user, $include_old);
		$data = $chart->getData();
		
		$this->view->set('pipeline_report_data', $data);
		$this->view->set('include_old', $include_old);
	}

	public function sales_report() {
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free() && !CurrentlyLoggedInUser::Instance()->getAccount()->in_trial()) {
			if(CurrentlyLoggedInUser::Instance()->isAdmin()) {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Upgrade now to instantly enable them.');
				sendTo('account/change_plan');
			} else {
				Flash::Instance()->addError('The report you requested is only available on paid plans. Contact your account admin to get them enabled.');
				sendTo('graphs/sales_history');
			}
			return;
		}
		
		$user = $this->_parseUser('sales_report');
		$date = $this->_parseDate('sales_report');

		$report_length = !empty($this->_data['report_length']) ? $this->_data['report_length'] : '90 days';
		switch ($report_length) {
			case '21 days':
			case '90 days':
			case '9 months':
			case '12 months':
				break;
			default:
				$report_length = '90 days';
		}
		$this->view->set('report_length', $report_length);
		
		$chart = new Charts_Tactile();
		$chart->salesReport($date, $user, $report_length);
		$data = $chart->getData();
		
		$this->view->set('sales_report_data', $data);
		
	}
	
}
