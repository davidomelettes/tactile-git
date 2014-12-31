<?php

require_once('Charts/Google/Bar.php');
require_once('Charts/Google/Line.php');
require_once('Charts/Google/Pie.php');


/**
 * Fetches the data and builds the graph objects for Tactile's charts
 *
 * @author de
 */
class Charts_Tactile {
	
	protected $_title = '';
	protected $_data;
	protected $_graph;
	protected $_url = '';
	
	static public function getDashboardGraphMethod() {
		$graph_method = Omelette_Magic::getValue('dashboard_graph', EGS::getUsername(), 'pipeline');
		
		$free_graphs = array('salesHistory', 'pipeline');
		if (CurrentlyLoggedInUser::Instance()->getAccount()->getPlan()->is_free()) {
			if (!in_array($graph_method, $free_graphs)) {
				$graph_method = 'pipeline';
			}
		}
		
		return $graph_method;
	}

	/**
	 * Set the graph colour based on the theme
	 *
	 * @return string
	 */
	public function getChartColour() {
		$theme = Tactile_AccountMagic::getValue('theme', 'green');
		switch ($theme) {
			case 'red':
				$colour = array('a40404', 'CC7777');
				break;
			case 'blue':
				$colour = array('1C336E','4671D5');
				break;
			case 'grey':
				$colour = array('343434', '999999');
				break;
			case 'orange':
				$colour = array('DD8800', 'ff9c00');
				break;
			case 'purple':
				$colour = array('570094', '8100db');
				break;
			case 'custom':
				$pri = preg_replace('/^#/','', Tactile_AccountMagic::getValue('theme_custom_primary', '#105F15'));
				$sec = preg_replace('/^#/','', Tactile_AccountMagic::getValue('theme_custom_secondary', '#569C30'));
				$colour = array($pri, $sec);
				break;
			case 'green':
			default:
				$colour = array('105F15', '569C30');
		}
		return $colour;
	}
	
	/**
	 * Establish the next greatest multiple, of the closest power of ten, as our ceiling
	 * e.g. (0, 1, 7) => 10, (100, 101, 0) => 200, (800, 600, 2999) => 3000 
	 *
	 * @param array $data Array of numerical values
	 */
	protected function _getCeiling($data) {
		$max = (count($data) > 0) ? max($data) : 0;
		$mag = ($max==0) ? 0: floor(log($max, 10));
		$ceil = ($mag == 0) ? 1000 : pow(10, $mag);
		while ($ceil < $max) {
			$ceil += $ceil;
		}
		
		return $ceil;
	}
	
	
	/**
	 * Given a maximum value and a number of segments, return the intermittant values
	 * e.g. (5, 1000) => (0, 200, 400, 600, 800, 1000)
	 * 
	 * @param array $data Array of values to calculate ceiling and segments with
	 * @param int $segments Number of segments
	 * @param int $ceiling Axis maximum
	 * @return array Array of axis label values
	 */
	protected function _segmentAxis($data, $segments=5, $ceiling=null) {
		if (is_null($ceiling)) {
			$ceiling = $this->_getCeiling($data);
		}
		
		$labels = array();
		for ($i = $segments; $i+1 > 0; $i--) {
			$labels[] = $ceiling - (($ceiling / $segments) * $i);
		}
		
		return $labels;
	}
	
	
	/**
	 * Reduce the length of labels by substituting zeroes for letters
	 * e.g. 70000 => 70K, 8000000 => 8M 
	 *
	 * @param array $data Array of numerical axis labels
	 * @param bool $hide_zero Set TRUE to remove '0' from the returned set of axis labels (Google's line charts don't space labels on the orgin very well)
	 */
	protected function _exponentShorthands($labels, $hide_zero=FALSE) {
		$max = (count($labels) > 0) ? max($labels) : 0;
		$mag = round(log($max, 10));
		
		$shorthands = array(9=>'B', 6=>'M', 3=>'K');
		foreach ($shorthands as $exp => $letter) {
			if ($mag > $exp) {
				foreach ($labels as &$num) {
					$num /= pow(10, $exp);
					if ($num != 0) {
						$num .= $letter;
					}
				}
				break; // Don't process any more shorthands
			}
		}
		if ($hide_zero) {
			if (FALSE !== ($key = array_search(0, $labels))) {
				$labels[$key] = '';
			}
		}
		
		return $labels;
	}
	
	
	/**
	 * Remove points from result set where value == 0
	 * Useful for pie charts mostly
	 *
	 * @param array $data Associative array of label => value
	 * @return array
	 */
	protected function _ditchZeroResults($data) {
		$output = array();
		foreach ($data as $k => $v) {
			if ($v != 0) {
				$output[$k] = $v;
			}
		}
		return $output;
	}
	
	
	public function getTitle() {
		return $this->_title;
	}
	
	public function getData() {
		return $this->_data;
	}
	
	public function getGraph() {
		return $this->_graph;
	}
	
	public function getUrl() {
		return $this->_url;
	}
	
	public function graphSample() {
		$data = array(0.5, 0.3, 0.2);
		$labels = array('x', 'y', 'z');
		$pie = new Charts_Google_Pie();
		$pie->addDataSet($data)
			->addFill(array('bg', 's', 'ffffff00'))
			->setPieLabels($labels)
			->setColours($this->getChartColour());
		
		$this->_title = 'My Sales History';
		$this->_data = $data;
		$this->_graph = $pie;
		
		return $pie;
	}
	
	/**
	 * Tactile-themed Google pie chart
	 *
	 * @param array $rows Data to graph
	 * @return Charts_Google_Pie
	 */
	public function graphPie($rows) {
		$rows = $this->_ditchZeroResults($rows);

		$data = array_values($rows);
		$pie_labels = array_keys($rows);
		
		// Normalise the data
		$total = array_sum($data);
		foreach ($data as &$datum) {
			$datum = round($datum / $total, 3) * 100;
		}
		
		$pie = new Charts_Google_Pie();
		$pie->addDataSet($data)
			->addFill(array('bg', 's', 'ffffff00'))
			->setColours($this->getChartColour())
			->setPieLabels($pie_labels);
		
		if (empty($data)) {
			$pie->setTitle('[No Data]');
		}
			
		return $pie;
	}
	
	/**
	 * Tactile-themed Google line chart
	 *
	 * @param array $rows
	 * @return Charts_Google_line
	 */
	public function graphSalesHistory($rows) {
		$ceil = $this->_getCeiling($rows);
		
		// Create the labels for the y-axis
		$y_labels = $this->_segmentAxis($rows);
		$y_labels = $this->_exponentShorthands($y_labels, TRUE);
		
		// Create the labels for the x-axis and plot the values from our data
		$x_labels = array_keys($rows);
		$data = array_values($rows);
		foreach ($x_labels as $k=>&$label) {
			$label = date("M", strtotime($label));
		}
		
		// Normalise the data as a percentage
		foreach ($data as &$val) {
			$val = ($val / $ceil) * 100;
		}
		
		$line = new Charts_Google_Line();
		$line
			->addDataSet($data)
			->setColours($this->getChartColour())
			->addLineStyle(array('2','1','0'))
			->addAxis('x', $x_labels)
			->addAxis('y', $y_labels)
			->setGrid(array(9.1, 20, 2, 2))
			->addFill(array('c', 'ls', 90, 'f5f5f5', 0.2, 'ffffff', 0.2))
			->addFill(array('bg', 's', 'ffffff00'))
			->addMarkers(array('square', $this->getChartColour(), 0, -1, 6, -1));
			
		return $line;
	}
	
	
	/**
	 * Tactile-themed Google bar chart
	 *
	 * @param array $rows
	 * @return Charts_Google_Bar
	 */
	public function graphPipeline($rows) {
		$weighted = array();
		$unweighted = array();

		foreach($rows AS $label => $val) {
			$weighted[] = $val['weightedcost'];
			$unweighted[] = $val['pipelinecost'];
		}

		$ceil = $this->_getCeiling($unweighted);

		$y_labels = array_reverse(array_keys($rows));
		$x_labels = $this->_segmentAxis($unweighted);
		$x_labels = $this->_exponentShorthands($x_labels);
		
		// Normalise the data as a percentage
		foreach ($weighted as $label => &$val) {
			$val = ($val / $ceil) * 100;
		}

		foreach ($unweighted as $label => &$val) {
			$val = (($val / $ceil) * 100) - $weighted[$label];
		}

		$bar = new Charts_Google_Bar();
		$bar->addDataSet($weighted)
			->addDataSet($unweighted)
			->setColours($this->getChartColour())
			->addAxis('x', $x_labels)
			->addAxis('y', $y_labels)
			->setAutoSizeColumns()
			->setGrid(array(20, 0, 2, 2))
			->addFill(array('c', 'ls', 0, 'f5f5f5', 0.2, 'ffffff', 0.2))
			->addFill(array('bg', 's', 'ffffff00'));
		
		return $bar;
	}
	
	
	public function salesHistory($date=null, $user=null) {
		$db = DB::Instance();
		
		if (is_null($user)) $user = EGS::getUsername();
		
		$now = time();
		if (!is_null($date)) {
			$start_date = date("Y-m-01 00:00:00", strtotime('-11 months', $date));
			$end_date = date("Y-m-01 00:00:00", strtotime('+1 month', $date));
		} else {
			$start_date = date("Y-m-01 00:00:00", strtotime('-11 months', $now));
			$end_date = date("Y-m-01 00:00:00", strtotime('+1 month', $now));
		}
		$start_month = date('n', strtotime($start_date));
		$before_year = date('Y', strtotime($start_date));
		$after_year = date('Y', strtotime($end_date));
		
		$query="SELECT
					extract('month' FROM o.enddate) AS month,
					sum(o.cost) AS total
				FROM
					opportunities o,
					opportunitystatus s 
				WHERE
					o.status_id = s.id 
					AND s.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
					AND s.open = false
					AND s.won = true ";
					
		if ($user == '*') {
			// Do nothing for all users ever
		} elseif ($user == '!') {
			// All active
			$query .= "AND o.assigned_to NOT IN (
					SELECT u.username FROM users u
					LEFT JOIN people p ON p.id = u.person_id
					WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
				)";
		} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
			// This is a group
			$group = $matches[1];
			$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
					"AND username IN (" .
					"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
					" AND r.name = ".$db->qstr($group)."))";
					
		} else {
			$query .= "AND o.assigned_to = ".$db->qstr($user);
		}
		
		$query .= " AND o.enddate >= ".$db->qstr($start_date)."
					AND o.enddate < ".$db->qstr($end_date)."
				GROUP BY 
					extract('month' FROM o.enddate), 
					extract('year' FROM o.enddate) 
				ORDER BY 
					extract('year' FROM o.enddate), 
					extract('month' FROM o.enddate)";

		$rows = $db->GetAssoc($query);
		
		// Fill out any missing rows
		for ($i = $start_month, $j = 0; $j < 12; $j++) {
			$rows[$i] = (isset($rows[$i])) ? $rows[$i] : 0;
			$i = ($i == 12) ? 1 : ($i + 1);
		}
		
		// Rename keys as dates
		$data = array();
		foreach ($rows as $k => $v) {
			$k = ($k >= $start_month) ? sprintf("%d-%02d-01", $before_year, $k) : sprintf("%d-%02d-01", $after_year, $k);
			$data[$k] = $v;
		}
		uksort($data, array($this, 'cmp_dates'));
		
		$this->_title = 'My Sales History';
		$this->_data = $data;
		$this->_graph = $this->graphSalesHistory($this->_data);
		$this->_url = '/graphs/sales_history';
		
		return $this;
	}
	
	public function cmp_dates($a, $b) {
		$a_time = strtotime($a);
		$b_time = strtotime($b);
		if ($a_time === $b_time) {
			return 0;
		}
		return ($a_time > $b_time ? 1 : -1);
	}
	
	
	public function pipeline($user=null) {
		if (is_null($user)) $user = EGS::getUsername();
	
		$db = DB::Instance();
		$query="SELECT
					s.name,
					COALESCE(sum(o.weightedcost),0) AS weightedcost,
					COALESCE(sum(o.cost),0) AS pipelinecost 
				FROM
					opportunitystatus s
					LEFT OUTER JOIN (
						SELECT (
							o.cost*(round(cast(o.probability AS numeric)/100, 2))) AS weightedcost,
							o.cost AS cost,
							o.status_id,
							o.usercompanyid,
							o.enddate,
							o.assigned_to
						FROM opportunities o) o ON (
						s.id = o.status_id
						AND o.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
						AND (s.open
							OR (
		                		extract('month' FROM o.enddate) = extract('month' FROM now())
								AND extract('year' FROM o.enddate) = extract('year' FROM now())
							)
						) ";
		
		if ($user == '*') {
			// Do nothing for all users ever
		} elseif ($user == '!') {
			// All active
			$query .= "AND o.assigned_to NOT IN (
					SELECT u.username FROM users u
					LEFT JOIN people p ON p.id = u.person_id
					WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
				)";
		} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
			// This is a group
			$group = $matches[1];
			$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
					"AND username IN (" .
					"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
					" AND r.name = ".$db->qstr($group)."))";
					
		} else {
			$query .= "AND o.assigned_to = ".$db->qstr($user);
		}

		
		
				$query .= " )
				WHERE
					s.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					s.name,
					s.position 
				ORDER BY
					s.position ASC";
		$rows = $db->GetAssoc($query);

		$this->_title = 'My Pipeline';
		$this->_data = $rows;
		$this->_graph = $this->graphPipeline($this->_data);
		$this->_url = '/graphs/pipeline';
		
		return $this;
	}
	
	
	public function oppsByStatusQty($user = null) {
		if (is_null($user)) $user = EGS::getUsername();
		$db = DB::Instance();
		$query="SELECT
					s.name,
					COALESCE(count(o.id),0) AS count
				FROM
					opportunitystatus s
				LEFT OUTER JOIN opportunities o ON (
					s.id=o.status_id AND o.usercompanyid=".$db->qstr(EGS::getCompanyId())."
					AND (s.open OR(
                	extract('month' FROM o.enddate)=extract('month' FROM now())
					AND extract('year' FROM o.enddate)=extract('year' FROM now())
							)) ";

					if ($user == '*') {
						// Do nothing for all users ever
					} elseif ($user == '!') {
						// All active
						$query .= "AND o.assigned_to NOT IN (
								SELECT u.username FROM users u
								LEFT JOIN people p ON p.id = u.person_id
								WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
							)";
					} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
						// This is a group
						$group = $matches[1];
						$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
								"AND username IN (" .
								"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
								" AND r.name = ".$db->qstr($group)."))";
					} else {
						$query .= "AND o.assigned_to = ".$db->qstr($user);
					}

					$query .= ")
				WHERE
					s.usercompanyid=".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					s.name,
					s.position 
				ORDER BY
					s.position ASC";
		$rows = $db->GetAssoc($query);
		
		$this->_title = 'Opportunities by Status (Quantity)';
		$this->_data = $rows;
		$this->_graph = $this->graphPie($this->_data);
		$this->_url = '/graphs/opps_by_status_qty';
		
		return $this;
	}
	
	
	public function oppsBySourceQty($user = null) {
		if (is_null($user)) $user = EGS::getUsername();
		
		$db = DB::Instance();
		$query="SELECT
					source.name,
					COALESCE(count(o.id),0) AS count
				FROM
					opportunitystatus s
				LEFT OUTER JOIN opportunities o ON (
					s.id=o.status_id AND o.usercompanyid=".$db->qstr(EGS::getCompanyId())."
					and (s.open OR(
                	extract('month' FROM o.enddate)=extract('month' FROM now())
					AND extract('year' FROM o.enddate)=extract('year' FROM now())
				)) ";
				
		if ($user == '*') {
			// Do nothing for all users ever
		} elseif ($user == '!') {
			// All active
			$query .= "AND o.assigned_to NOT IN (
					SELECT u.username FROM users u
					LEFT JOIN people p ON p.id = u.person_id
					WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
				)";
		} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
			// This is a group
			$group = $matches[1];
			$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
					"AND username IN (" .
					"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
					" AND r.name = ".$db->qstr($group)."))";
					
		} else {
			$query .= "AND o.assigned_to = ".$db->qstr($user);
		}

		$query .= ")
				JOIN
					opportunitysource source ON source.id = o.source_id 
				WHERE
					s.usercompanyid=".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					source.name
				ORDER BY
					count DESC";
		$rows = $db->GetAssoc($query);
		
		$this->_title = 'Opportunities by Source (Quantity)';
		$this->_data = $rows;
		$this->_graph = $this->graphPie($this->_data);
		$this->_url = '/graphs/opps_by_source_qty';
		
		return $this;
	}
	
	
	public function oppsBySourceCost($user = null) {
		if (is_null($user)) $user = EGS::getUsername();
		
		$db = DB::Instance();
		$query="SELECT
					source.name,
					COALESCE(sum(o.cost),0) AS pipelinecost
				FROM
					opportunitystatus s
				LEFT OUTER JOIN opportunities o ON (
					s.id=o.status_id AND o.usercompanyid=".$db->qstr(EGS::getCompanyId())."
					and (s.open OR(
                	extract('month' FROM o.enddate)=extract('month' FROM now())
					AND extract('year' FROM o.enddate)=extract('year' FROM now())
							)) ";

					if ($user == '*') {
						// Do nothing for all users ever
					} elseif ($user == '!') {
						// All active
						$query .= "AND o.assigned_to NOT IN (
								SELECT u.username FROM users u
								LEFT JOIN people p ON p.id = u.person_id
								WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
							)";
					} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
						// This is a group
						$group = $matches[1];
						$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
								"AND username IN (" .
								"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
								" AND r.name = ".$db->qstr($group)."))";
					} else {
						$query .= "AND o.assigned_to = ".$db->qstr($user);
					}

					$query .= ")
				JOIN
					opportunitysource source ON source.id = o.source_id 
				WHERE
					s.usercompanyid=".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					source.name
				ORDER BY
					pipelinecost DESC";
		$rows = $db->GetAssoc($query);
		
		$this->_title = 'Opportunities by Source (Cost)';
		$this->_data = $rows;
		$this->_graph = $this->graphPie($this->_data);
		$this->_url = '/graphs/opps_by_source_cost';
		
		return $this;
	}
	
	
	public function oppsByTypeQty($user = null) {
		if (is_null($user)) $user = EGS::getUsername();
		
		$db = DB::Instance();
		$query="SELECT
					t.name,
					COALESCE(count(o.id),0) AS count
				FROM
					opportunitystatus s
				LEFT OUTER JOIN opportunities o ON (
					s.id=o.status_id AND o.usercompanyid=".$db->qstr(EGS::getCompanyId())."
					and (s.open OR(
                	extract('month' FROM o.enddate)=extract('month' FROM now())
					AND extract('year' FROM o.enddate)=extract('year' FROM now())
							)) ";

					if ($user == '*') {
						// Do nothing for all users ever
					} elseif ($user == '!') {
						// All active
						$query .= "AND o.assigned_to NOT IN (
								SELECT u.username FROM users u
								LEFT JOIN people p ON p.id = u.person_id
								WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
							)";
					} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
						// This is a group
						$group = $matches[1];
						$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
								"AND username IN (" .
								"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
								" AND r.name = ".$db->qstr($group)."))";
								
					} else {
						$query .= "AND o.assigned_to = ".$db->qstr($user);
					}

					$query .= ")
				JOIN
					opportunitytype t ON t.id = o.type_id 
				WHERE
					s.usercompanyid=".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					t.name
				ORDER BY
					count DESC";
		$rows = $db->GetAssoc($query);
		
		$this->_title = 'Opportunities by Type (Quantity)';
		$this->_data = $rows;
		$this->_graph = $this->graphPie($this->_data);
		$this->_url = '/graphs/opps_by_type_qty';
		
		return $this;
	}
	
	
	public function oppsByTypeCost($user = null) {
		if (is_null($user)) $user = EGS::getUsername();
		$db = DB::Instance();
		$query="SELECT
					t.name,
					COALESCE(sum(o.cost),0) AS pipelinecost
				FROM
					opportunitystatus s
				LEFT OUTER JOIN opportunities o ON (
					s.id=o.status_id AND o.usercompanyid=".$db->qstr(EGS::getCompanyId())."
					and (s.open OR(
                	extract('month' FROM o.enddate)=extract('month' FROM now())
					AND extract('year' FROM o.enddate)=extract('year' FROM now())
							)) ";

					if ($user == '*') {
						// Do nothing for all users ever
					} elseif ($user == '!') {
						// All active
						$query .= "AND o.assigned_to NOT IN (
								SELECT u.username FROM users u
								LEFT JOIN people p ON p.id = u.person_id
								WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
							)";
					} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
						// This is a group
						$group = $matches[1];
						$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
								"AND username IN (" .
								"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
								" AND r.name = ".$db->qstr($group)."))";
					} else {
						$query .= "AND o.assigned_to = ".$db->qstr($user);
					}

					$query .= ")
				JOIN
					opportunitytype t ON t.id = o.type_id 
				WHERE
					s.usercompanyid=".$db->qstr(EGS::getCompanyId())."
				GROUP BY
					t.name
				ORDER BY
					pipelinecost DESC";
		$rows = $db->GetAssoc($query);
		
		$this->_title = 'Opportunities by Type (Cost)';
		$this->_data = $rows;
		$this->_graph = $this->graphPie($this->_data);
		$this->_url = '/graphs/opps_by_type_cost';
		
		return $this;
	}
	
	public function pipelineReport($user = null, $include_old = 'no') {
		if (is_null($user)) $user = EGS::getUsername();
		$db = DB::Instance();
		$query="
				SELECT
					o.id,
					c.name AS organisation,
					c.id AS organisation_id,
					o.id AS opportunity_id,
					o.name AS opportunity,
					o.enddate,
					o.probability || '%' AS probability,
					CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',now())) AND (date_trunc('month', o.enddate) < date_trunc('month', now() + interval '1 month')) THEN cost ELSE 0 END AS thirtydays,
					CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',now() + interval '1 month')) AND (date_trunc('month', o.enddate) < date_trunc('month', now() + interval '2 months')) THEN cost ELSE 0 END AS sixtydays,
					CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',now() + interval '2 months')) AND (date_trunc('month', o.enddate) < date_trunc('month', now() + interval '3 months')) THEN cost ELSE 0 END AS ninetydays,
					CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',now() + interval '3 months')) THEN cost ELSE 0 END AS ninetydaysplus
				FROM
					opportunities o LEFT JOIN
					organisations c ON
					o.organisation_id=c.id,
					opportunitystatus s
				WHERE
					o.status_id=s.id AND
					s.open = true AND
					o.cost > 0 AND
					o.usercompanyid=".$db->qstr(EGS::getCompanyId());
		
		if ($include_old == 'no') {
			$query .= " o.enddate >= date_trunc('month', now()) AND ";
		}
		if ($user == '*') {
			// Do nothing for all users ever
		} elseif ($user == '!') {
			// All active
			$query .= "AND o.assigned_to NOT IN (
						SELECT u.username FROM users u
						LEFT JOIN people p ON p.id = u.person_id
						WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
					)";
		} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
			// This is a group
			$group = $matches[1];
			$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
					"AND username IN (" .
					"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
					" AND r.name = ".$db->qstr($group)."))";
					
		} else {
			$query .= "AND o.assigned_to = ".$db->qstr($user);
		}
		
			$query .= "
				GROUP BY 
					c.name,
					o.name,
					o.enddate,
					o.cost,
					o.probability,
					c.id,
					o.id
				ORDER BY lower(c.name)
			";

		$rows = $db->GetAssoc($query);

		$this->_title = 'Pipeline Report';
		$this->_data = $rows;
		$this->_url = '/graphs/pipeline_reports';
		
		return $this;
	}
	
	public function salesReport($date = null, $user = null, $range = '90 days') {
		if (is_null($user)) $user = EGS::getUsername();
		if (is_null($date)) $date = time();

		$date = date('c', $date);

		$db = DB::Instance();
		$query = "SELECT
					o.id,
					c.name AS organisation,
					c.id AS organisation_id,
					o.id AS opportunity_id,
					o.name AS opportunity,
					o.enddate,";
		switch ($range) {
			case '21 days':
				$query .= "CASE WHEN (o.enddate >= ".$db->qstr($date)."::date) AND (o.enddate < ".$db->qstr($date)."::date + interval '7 days') THEN cost ELSE 0 END AS interval_one,
						CASE WHEN (o.enddate >= ".$db->qstr($date)."::date + interval '7 days') AND (o.enddate < ".$db->qstr($date)."::date + interval '14 days') THEN cost ELSE 0 END AS interval_two,
						CASE WHEN (o.enddate >= ".$db->qstr($date)."::date + interval '14 days') AND (o.enddate < ".$db->qstr($date)."::date + interval '21 days') THEN cost ELSE 0 END AS interval_three";
				break;
			case '9 months':
				$query .= "CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',".$db->qstr($date)."::date)) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '3 month')) THEN cost ELSE 0 END AS interval_one,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '3 month')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '6 months')) THEN cost ELSE 0 END AS interval_two,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '6 months')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '9 months')) THEN cost ELSE 0 END AS interval_three";
				break;
			case '12 months':
				$query .= "CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',".$db->qstr($date)."::date)) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '4 month')) THEN cost ELSE 0 END AS interval_one,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '4 month')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '8 months')) THEN cost ELSE 0 END AS interval_two,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '8 months')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '12 months')) THEN cost ELSE 0 END AS interval_three";
				break;
			case '90 days':
			default:
				$range = '90 days';
				$query .= "CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month',".$db->qstr($date)."::date)) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '1 month')) THEN cost ELSE 0 END AS interval_one,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '1 month')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '2 months')) THEN cost ELSE 0 END AS interval_two,
						CASE WHEN (date_trunc('month', o.enddate) >= date_trunc('month', ".$db->qstr($date)."::date + interval '2 months')) AND (date_trunc('month', o.enddate) < date_trunc('month', ".$db->qstr($date)."::date + interval '3 months')) THEN cost ELSE 0 END AS interval_three";
		}
		$query .= "
				FROM
					opportunities o LEFT JOIN
					organisations c ON
					o.organisation_id=c.id,
					opportunitystatus s
				WHERE
					o.status_id=s.id AND
					s.won = true AND
					o.cost > 0 AND
					o.enddate >= date_trunc('month', ".$db->qstr($date)."::date) AND
					o.enddate < date_trunc('month', ".$db->qstr($date)."::date) + interval ".$db->qstr($range)." AND
					o.usercompanyid=".$db->qstr(EGS::getCompanyId());
			
		if ($user == '*') {
			// Do nothing for all users ever
		} elseif ($user == '!') {
			// All active
			$query .= "AND o.assigned_to NOT IN (
						SELECT u.username FROM users u
						LEFT JOIN people p ON p.id = u.person_id
						WHERE u.enabled IS FALSE AND p.usercompanyid = ".$db->qstr(EGS::getCompanyId())."
					)";
		} elseif (preg_match('/^@(.+)$/', $user, $matches)) {
			// This is a group
			$group = $matches[1];
			$query .= "AND o.assigned_to IN (SELECT username FROM users u WHERE u.enabled " .
					"AND username IN (" .
					"SELECT hr.username FROM hasrole hr JOIN roles r ON hr.roleid = r.id AND r.usercompanyid = ".$db->qstr(EGS::getCompanyId())." " .
					" AND r.name = ".$db->qstr($group)."))"; 
			
		} else {
			$query .= "AND o.assigned_to = ".$db->qstr($user);
		}
		
		$query .= "
			GROUP BY 
				c.name,
				o.name,
				o.enddate,
				o.cost,
				o.probability,
				c.id,
				o.id
			ORDER BY lower(c.name)
		";

		$rows = $db->GetAssoc($query);

		$this->_title = 'Pipeline Report';
		$this->_data = $rows;
		$this->_url = '/graphs/pipeline_reports';
		
		return $this;
	}
}
