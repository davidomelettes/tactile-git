<?php

// Bootstrap
error_reporting(E_ALL);
define('FILE_ROOT', dirname(__FILE__).'/../../../../');
require_once(FILE_ROOT.'app/setup.php');

define('COMPLETE_TRANS', TRUE); // Set FALSE to autofail transaction
define('DB_DEBUG', FALSE);

// Logging
require_once('Zend/Log.php');
require_once('Zend/Log/Writer/Stream.php');
require_once('Zend/Log/Writer/Mock.php');
require_once('Zend/Log/Filter/Priority.php');

// Create logger
define('ZEND_LOG_LEVEL', Zend_Log::DEBUG); // DEBUG, INFO, NOTICE, WARN, ERR, CRIT, ALERT, EMERG
$log_writer = new Zend_Log_Writer_Stream('php://output');
$logger = new Zend_Log($log_writer);
$log_filter = new Zend_Log_Filter_Priority(ZEND_LOG_LEVEL);
$logger->addfilter($log_filter);


$logger->info("STARTING PROCESS...");


// Connect to databases
$db = NewADOConnection('pgsql');
$db->Connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db->debug = DB_DEBUG;
if (!$db) {
	$logger->err("CONNECTION FAILED");
	die();
}
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->startTrans();

// Get all companys with existing 'lead' tags and make exceptions
$tag_translations = array();
$query = "SELECT c.id as company_id, t.name as tag, c.name FROM company c JOIN tags t ON t.usercompanyid = c.id WHERE t.name ILIKE 'lead'";
$results = $db->getAll($query);
$logger->info(count($results) . " company(s) with existing 'lead' tags");
foreach ($results as $row) {
	$tag = 'Lead';
	$success = false;
	do {
		$tag = '_' . $tag;
		$query = "SELECT count(*) FROM tags WHERE usercompanyid = " . $db->qstr($row['company_id']) . " AND name ILIKE " . $db->qstr($tag);
		$one = $db->getOne($query);
		if (!$one) {
			$success = true;
		}
	} while (!$success);
	$tag_translations[$row['company_id']] = $tag;
	$logger->debug($row['name'] . ': ' . $tag);
}


// Get all accounts
$query = "SELECT * FROM tactile_accounts";
$results = $db->getAll($query);
foreach ($results as $row) {
	// Create tag
	$tag = isset($tag_translations[$row['company_id']]) ? $tag_translations[$row['company_id']] : 'Lead';
	$query = "INSERT INTO tags (name, usercompanyid) VALUES (".$db->qstr($tag).", ".$db->qstr($row['company_id']).")";
	if (FALSE === $db->execute($query)) {
		$logger->err('ERROR: ' . $db->ErrorMsg());
		$logger->err('QUERY: ' . $query);
		$db->FailTrans();
		$logger->err('TRANSACTION FAILED');
		die();
	}
	$query = "SELECT id FROM tags WHERE name = " . $db->qstr($tag) . " AND usercompanyid = " . $db->qstr($row['company_id']);
	$tag_id = $db->getOne($query);
	$logger->debug("Tag added for " . $row['company']);

	// Update tag map
	$query = "INSERT INTO tag_map (tag_id, company_id, hash) SELECT " .
		$db->qstr($tag_id) . ", id, 'c' || id FROM company WHERE accountnumber IS NULL AND usercompanyid = " .
		$db->qstr($row['company_id']);
	if (FALSE === $db->execute($query)) {
		$logger->err('ERROR: ' . $db->ErrorMsg());
		$logger->err('QUERY: ' . $query);
		$db->FailTrans();
		$logger->err('TRANSACTION FAILED');
		die();
	}
	$logger->debug('Rows affected: ' . $db->Affected_Rows());
}

$db->completeTrans(COMPLETE_TRANS);
$logger->info("PROCESS COMPLETE!");
