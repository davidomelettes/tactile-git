<?php

class QueryBuilder {
	
	private $doname;
	private $model;
	private $fields;
	private $field_array = array();
	
	private $order_string = '';
	private $limit_string = '';
	private $where_string = '';
	private $join_string = '';
	private $group_by_string = '';
	private $having_string = '';
	
	private $unions = array();
	private $union_string = '';
	private $union_order_string = '';
	private $union_limit_string = '';
	
	private $distinct = false;
	
	public function __construct($db, $do = null) {
		$this->db = $db;
		if (isset($do)) {
			if ($do instanceof DataObject) {
				$this->doname = get_class($do);
				$this->model = $do;
			} else {
				$this->doname = $do;
			}
		}
	}
	
	public function setDistinct($distinct=true) {
		$this->distinct=$distinct;
	}
	
	private function getDO() {
		if (empty($this->model)) {
			$this->model = new $this->doname;
		}
		return $this->model;
	}
	
	/**
	 * Add fields to the select part of the query
	 *
	 * @param Array $fields
	 * @return QueryBuilder
	 */
	public function select($fields) {
		$this->field_array = $fields;
		$this->fields = '';
		if (!empty($this->doname)) {
			$do = $this->getDO();
		}
		if (is_array($fields) && count($fields) > 0) {
			foreach($fields as $fieldname => $field) {
				if (isset($do)) {
					$nfield = $do->getField($fieldname);
					$this->fields .= $fieldname . ',';
				}
			}
			if (count($fields) == 2) {
				$this->fields .= '\'blanking\' as blanking';
			}
			$this->fields = substr($this->fields, 0, -1);
		} else {
			if (isset($do) && $do->isField($do->idField)) {
				$this->fields = $do->idField . ', *';
			} else {
				$this->fields = '*';
			}
		}
		if ($this->fields == ', *') {
			$this->fields = '*';
		}
		$this->select_string = 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . $this->fields;
		return $this;
	}
	
	/**
	 * Makes the query a DELETE query
	 *
	 * @return QueryBuilder
	 */
	public function delete() {
		$this->select_string = 'DELETE';
		return $this;
	}
	
	/**
	 * Makes this query use another query to sub-select
	 *
	 * @param string $field
	 * @param string $operator
	 * @param QueryBuilder $query
	 * @return QueryBuilder
	 */
	public function sub_select($field, $operator, $query) {
		$this->where_string = 'WHERE ' . $field . ' ' . $operator . ' (' . $query->__toString() . ')';
		return $this;
	}
	
	/**
	 * A simpler way of adding fields, by sending an array of fieldnames. No checks done as to whether they exist
	 *
	 * @param Array $fields
	 * @param Boolean $distinct
	 * @return QueryBuilder
	 */
	public function select_simple($fields, $distinct = false) {
		$field_string = '';
		if (!is_array($fields)) {
			throw new Exception("Expected array of fields!");
		}
		foreach($fields as $key => $value) {
			$field_string .= $value;
			if (is_string($key)) {
				$field_string .= ' AS '.$key;
			}
			$field_string .= ',';
			$this->field_array[$value] = $value;
		}
		$field_string = rtrim($field_string,',');
		$this->select_string = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') . $field_string;
		if ($distinct) {
			$this->distinct = true;
		}
		return $this;
	}
	
	/**
	 * Add a table to the 'from' part of the query
	 *
	 * @param String $tablename
	 * @return QueryBuilder
	 */
	public function from($tablename) {
		$this->tablename=$tablename;
		$this->from_string='FROM '.$tablename;
		return $this;
	}
	
	/**
	 * Add a left join to the query, $on can be a string or a constraint(chain)
	 *
	 * @param String $table
	 * @param String|Constraint|ConstraintChain $on
	 * @return QueryBuilder
	 */
	public function left_join($table,$on) {
		if ($on instanceof Constraint || $on instanceof ConstraintChain) {
			$on = $on->__toString();
		}
		$join = 'LEFT JOIN ' . $table . ' ON (' . $on . ') ';
		$this->join_string .= $join;
		return $this;
	}
	
	/**
	 * Add a join to the query, $on can be a string or a constraint(chain)
	 *
	 * @param String $table
	 * @param String|Constraint|ConstraintChain $on
	 * @return QueryBuilder
	 */
	public function join($table,$on) {
		if ($on instanceof Constraint || $on instanceof ConstraintChain) {
			$on = $on->__toString();
		}
		$join = 'JOIN ' . $table . ' ON (' . $on . ') ';
		$this->join_string .= $join;
		return $this;
	}

	/**
	 * Add an array of fields to the group by part of the query
	 *
	 * @param Array $fields
	 * @return QueryBuilder
	 */
	public function group_by($extra_fields = array()) {
		$this->group_by_string = 'GROUP BY ' . implode(',', array_merge($this->field_array, $extra_fields)). ' ';
		return $this;
	}
	
	/**
	 * Add one or more constraints to the 'having' part of the query
	 *
	 * @param Constraint|ConstraintChain $constraints
	 * @return QueryBuilder
	 */
	public function having($constraints) {
		if ($constraints instanceof Constraint || $constraints instanceof ConstraintChain) {
			$constraints = $constraints->__toString();
		}
		$this->having_string = 'HAVING '.$constraints;
		return $this;
	}
	
	/**
	 * Add one or more constraints to the query
	 *
	 * @param Constraint|ConstraintChain $constraints
	 * @return QueryBuilder
	 */
	public function where($constraints,$clear=false) {
		if (!is_callable(array('__toString',$constraints))) {
			$constraintString = $constraints;
		} else {
			$constraintString = $constraints->__toString();
		}
		if (!empty($constraintString)) {
			if($clear || empty($this->where_string)) {
				$this->where_string = 'WHERE '.$constraintString;
			} else {
				$this->where_string .= ' AND '.$constraintString;
			}
		} else {
			$this->where_string = '';
		}
		return $this;
	}

	/**
	 * Set the 'ORDER BY' part of the query.
	 * 
	 * Takes either a string for each argument, or an array for each argument (mixture not advised)
	 * @param Array|String $orderby
	 * @param Array|String $orderdir
	 * 
	 * @return QueryBuilder
	 */
	public function orderby($orderby, $orderdir) {
		if (!is_array($orderby)) {
			$orderby = array($orderby);
		}
		if (!is_array($orderdir)) {
			$orderdir = array($orderdir);
		}
		$orderstring = '';
		foreach ($orderby as $i => $fieldname) {
			if (count($this->field_array) > 0) {
				if (!empty($fieldname) && !isset($this->field_array[$fieldname]) &&
					!in_array('*', $this->field_array)) {
					$this->select_string .= ',' . $fieldname;
				}
			}
			if (!empty($fieldname)) {
				$orderstring .= $fieldname . ' ' . (!empty($orderdir[$i]) ? $orderdir[$i] : 'ASC') . ', ';
			}
		}
		if (!empty($orderstring)){
			$orderstring = substr($orderstring, 0, -2);
			$this->order_string = 'ORDER BY ' . $orderstring;
		} else {
			$this->order_string = '';
		}
		return $this;
	}
	
	public function customorderby($string) {
		$this->order_string = 'ORDER BY ' . $string;
	}

	public function limit($limit,$offset=0) {
		if (!empty($limit)) {
			$this->limit_string = 'LIMIT ' . $limit . ' ';
			
			if (!empty($offset)) {
				$this->limit_string .= 'OFFSET ' . $offset;
			}
		}
		return $this;
	}

	public function __toString() {
		$string = '';
		if (!empty($this->union_string)) {
			$string .= '(';
		}
		$string .=	$this->select_string . ' ' .
					$this->from_string . ' ' .
					$this->join_string . ' ' .
					$this->where_string . ' ' .
					$this->group_by_string . ' ' .
					$this->having_string . ' ' .
					$this->order_string . ' ' .
					$this->limit_string;
		if (!empty($this->union_string)) {
			$string .= ') ';
			$string .=	$this->union_string . ' ' .
						$this->union_order_string . ' ' .
						$this->union_limit_string;
		}
		return $string;
	}
	
	public function countQuery($id_field = 'id') {
		$string = '';
		if (!empty($this->union_string)) {
			$string .= 'SELECT count(*) FROM ((' . $this->select_string . ' ';
		} else {
			if ($this->distinct) {
				$string .= 'SELECT COUNT(DISTINCT ' . $id_field . ')';
			} else {
				$string .= 'SELECT count(*) ';
			}
		}
		$string .=	$this->from_string . ' ' .
					$this->join_string . ' ' .
					$this->where_string;
		if (!empty($this->union_string)) {
			$string .= ') ';
			foreach ($this->unions as $qb) {
				$string .= 'UNION (' . $qb->unionCount() . ')';
			}
			$string .= ') AS count';
		}
		return $string;
	}
	
	public function unionCount() {
		$string = '';
		$string .=	$this->select_string . ' ' .
					$this->from_string . ' ' .
					$this->join_string . ' ' .
					$this->where_string;
		return $string;
	}
	
	public function union(QueryBuilder $qb, $all=false) {
		$this->unions[] = $qb;
		$this->union_string .= 'UNION ' . ($all ? 'ALL ' : '') . '(' . $qb->__toString() . ') ';
		return $this;
	}
	
	public function union_order($orderby, $orderdir) {
		if (!is_array($orderby)) {
			$orderby = array($orderby);
		}
		if (!is_array($orderdir)) {
			$orderdir = array($orderdir);
		}
		$orderstring = '';
		foreach ($orderby as $i => $fieldname) {
			if (!empty($fieldname)) {
				$orderstring .= $fieldname . ' ' . (!empty($orderdir[$i]) ? $orderdir[$i] : 'ASC') . ', ';
			}
		}
		if (!empty($orderstring)){
			$orderstring = substr($orderstring, 0, -2);
			$this->union_order_string = 'ORDER BY ' . $orderstring;
		}
		return $this;
	}
	
	public function union_limit($limit, $offset=0) {
		if (!empty($limit)) {
			$this->union_limit_string = 'LIMIT ' . $limit . ' ';
			
			if (!empty($offset)) {
				$this->union_limit_string .= 'OFFSET ' . $offset;
			}
		}
		return $this;
	}
}
