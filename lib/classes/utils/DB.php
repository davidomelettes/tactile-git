<?php

class DB {
	protected $db;
	private $metaColStore=array();
	
	protected static $instance;
	
	public function __construct() {
		$this->db=NewADOConnection('pgsql');
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		$dbname = DB_NAME;
		$this->db->Connect(DB_HOST,DB_USER,DB_PASSWORD,$dbname);

		// Turn on SQL Logging if requested
		if(defined('LOG_SQL') && LOG_SQL) $this->db->LogSQL();

		$this->db->metaColumnsSQL = "SELECT a.attname, t.typname, CASE WHEN a.attlen=-1 THEN (t.typtypmod-4) ELSE a.attlen END AS attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum
	FROM pg_class c, pg_attribute a, pg_type t
	WHERE relkind in ('r','v') AND (c.relname='%s' or c.relname=lower('%s')) and a.attname not like '....%%'
	AND a.attnum>0 AND a.atttypid=t.oid AND a.attrelid=c.oid order by a.attnum";
//		$this->db->debug=true;
	}

	/**
	 * Returns the database connection
	 * 
	 * @return ADOConnection
	 */
	public static function &Instance() {
		if(self::$instance===null) {
			global $injector;
			try {
				self::$instance = $injector->instantiate('DB');
			}
			catch(PhemtoException $e) {
				self::$instance=new DB();	
			}			
		}
		return self::$instance;
	}
	
	public static function clear() {
		self::$instance = null;
	}
	
	public static function debug($debug=true) {
		$db = self::Instance();
		$db->debug=$debug;
	}
	
	public function get_last_insert_id() {
		$query = 'SELECT lastval()';
		return $this->GetOne($query);
	}
	
	function __call($func,$args) {
		if(is_callable(array($this->db,$func))) {
			return call_user_func_array(array($this->db,$func),$args);
		}
	}
	
	function __set($key,$var) {
		$this->db->$key=$var;
	}
	
	function __get($key) {
		return $this->db->$key;
	}
}
?>
