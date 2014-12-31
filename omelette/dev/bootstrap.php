<?php
define('FILE_ROOT','/mnt/websites/tactile/');
$_SERVER['HTTP_HOST']='commandline';
require '../../conf/config.php';
require '../../lib/setup.php';
require LIB_ROOT.'spyc/spyc.php';
$dbname=DB_NAME;
`psql $dbname < "../sql/1.sql"`;
$db=DB::Instance();
$imports = Spyc::YAMLLoad('imports.yml');
foreach($imports as $file) {
	$query = 'DELETE FROM '.$file;
	echo "Deleting from $file\n";
	$db->Execute($query);
	$filename=$file.'.sql';
	
	`psql $dbname < $filename`;
	echo "Imported $file \n\n";
		
}
	
$setup = Spyc::YAMLLoad('bootstrap.yml');

$db->StartTrans();
$total = count($setup);
$i=0;
foreach($setup as $command) {
	$i++;
	echo "Running: {$command[name]}...($i of $total)\n";
		$query='';
switch($command['type']) {

		case 'sql': {
			$query = $command['query'];
			$db->Execute($query);
			break;
		}
		case 'import': {
			$dbname=DB_NAME;
			$file = 
			`psql $dbname < $file`;
		}
		case 'insert': {
			$data = $command['insert'];
			$table = $command['table'];
			$db->AutoExecute($table,$data,'INSERT');
			break;
		}
		case 'update': {
			$data = $command['update'];
			$table = $command['table'];
			$where = $command['where'];
			if(!is_string($where)) {
				throw new Exception('Unable to handle non-string \'where\' component (yet)');
			}
			$db->AutoExecute($table,$data,'UPDATE',$where);
			break;
		}
		default: {
			throw new Exception('I don\'t know what to do with: '.$command['type']);
		}
	}
	if(!$db->hasFailedTrans()) {
		echo "Completed!\n\n";
	}
	else {
		echo "Failed!\n";
		echo $db->ErrorMsg();
		if(!empty($query)) {
			echo "\n".$query."\n";
		}
		break;
	}
		
}
echo "\n";
//$db->FailTrans();
$db->CompleteTrans();
?>
