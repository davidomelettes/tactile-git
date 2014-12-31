<?php

abstract class Exporter {
	const MAX_LOOPS = 10000; // @1000 per loop = 10,000,000 results
	protected $_order = 'id';
	protected $_formatter = null;
	
	public function setFormatter($formatter) {
		$this->_formatter = $formatter;
	}
	
	public function getFormatter() {
		if ($this->_formatter === null) {
			$this->_formatter = new ArrayExportFormatter();
		}
		return $this->_formatter;
	}
	
	public function setUserCompanyId($id) {
		$this->usercompanyid = $id;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setOrder($order) {
		$this->_order = $order;
	}
	
	public function getAll() {
		return $this->getBy();
	}
	
	public function outputRows(QueryBuilder $qb, $rowsPerCycle = 1000) {
		$db = DB::Instance();
		
		$db->BeginTrans();
		$total = $this->getCount($qb);
		$fetched = 0;
		$loops = 0;
		
		// Until we have fetch rows equal to the count, or exceeded the maximum number of loops...
		while ($fetched < $total && $loops < self::MAX_LOOPS) {
			$loops++;
			
			// Set the query LIMIT and OFFSET
			$qb->limit($rowsPerCycle, $fetched);
			
			// Fetch more rows
			$rows = $db->getArray($qb->__toString());
			$rowsThisQuery = count($rows);
			if ($rows === FALSE) {
				throw new Exception("Bad query: ".$qb->__toString()."\n".$db->ErrorMsg());
			}
			if (count($rows) < 1) {
				break;
			}
			
			// Add tag/cf data to fetched rows
			$rows = $this->_addTags($rows);
			$rows = $this->_addCustomFields($rows);
			$this->getFormatter()->output($rows);
			
			// Update the total fetched
			$fetched += $rowsThisQuery;
		}
		$db->CompleteTrans();
	}
	
	abstract public function getCount(QueryBuilder $qb);
	
	abstract public function getBy($key = null, $value = null);
	
	abstract public function getByTag($tags);
	
	abstract protected function _addTags($rows);
	
	abstract protected function _addCustomFields($rows);
}
