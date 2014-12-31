<?php
/**
 * @package Tasks
 */
//error_reporting(E_ALL);
define('FILE_ROOT',dirname(__FILE__).'/../../../');
require FILE_ROOT.'app/setup.php';
$injector=new Phemto();
if(file_exists(FILE_ROOT.'app/cli_setup.php')) {
	require FILE_ROOT.'app/cli_setup.php';
}
$injector->register('NonSessionFlash');
require_once LIB_ROOT.'spyc/spyc.php';
if(file_exists(FILE_ROOT.'conf/task_dependencies.yml')) {
	$dependencies = Spyc::YAMLLoad(FILE_ROOT.'conf/task_dependencies.yml');
	foreach($dependencies as $classname) {
		$injector->register($classname);
	}
}


$config = Spyc::YAMLLoad(FILE_ROOT.'conf/tasks_config.yml');
if(isset($config['taskrunner'])) {
	$config = $config['taskrunner'];
}
else {
	$config = array();
}

class TaskRunner extends EGSCLIApplication {
	
	public function go() {
		/*grab all the task files*/
		$files = new DirectoryIterator(DATA_ROOT.'jobs');
		$files = new DotFilter($files);
		$i=0;
		foreach($files as $file) {
			$i++;
			try {
				$task = DelayedTask::Factory($file->getPathName(),$this->logger);
				$task->load($file->getPathName());
				$task->execute();
			}
			catch(Exception $e) {
				echo $e->getMessage()."\n";
				echo $e->getTraceAsString();
				$task->restore();
			}
		}
		$this->logger->info($i.' Tasks were executed');
	}
	
}
$runner = new TaskRunner($injector,$config);
$runner->go();
?>