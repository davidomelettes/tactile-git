<?php
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
/**
 *
 * @author gj
 */
class EGSCLIApplication extends EGSApplication {
	
	/**
	 * An instance of Zend_Log to send messages to
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	/**
	 * For now, only CLI apps are getting logging. Will probably end up in EGSApp at some point
	 * 
	 * @param Phemto $injector
	 * @param Zend_Log $log
	 */
	public function __construct($injector, $config = array(), Zend_Log $logger = null) {
		parent::__construct($injector);
		if($logger !== null) {
			$this->logger = $logger;
		}
		else {
			$this->logger = self::getDefaultLogger(get_class($this),$config);
		}
		$this->logger->addPriority('CONF', 9);
		$this->config = $config;
		$this->logger->info('Beginning '.get_class($this));
		$this->logger->conf(print_r($config,true));
		$this->checkConfig();
		
	}
	
	protected function injectDependencies() {
		$this->injector->register('Phemto');
		//Prettifier basically does uc_words on things, but knows some exceptions (acronyms)
		$this->injector->register('Prettifier');
		//We do redirects differently to EGS, as we have pretty URLs
		$this->injector->register('DummyRedirectHandler');
		//CLI, so no session
		$this->injector->register('NonSessionFlash');
		//we don't really want html, so we'll use the basic CLIView
		$this->injector->register('CLIView');
	}
	
	/**
	 * Returns an instance of Zend_Log setup with an output writer for warnings only,
	 *  and a file-write for everything both with a custom format that includes the task name
	 *
	 * @return Zend_Log
	 */
	public static function getDefaultLogger($name,$config = array()) {
		$console_writer = new Zend_Log_Writer_Stream('php://output');
		$file_writer = new Zend_Log_Writer_Stream(DATA_ROOT.'/application.log');		
		
		$log_config = isset($config['log']) ? $config['log'] : array();
		$console_level = isset($log_config['console'])?$log_config['console']:Zend_Log::WARN;
		$file_level = isset($log_config['file'])?$log_config['file']:Zend_Log::INFO;
		
		//if(!isset($config[]))
		$console_writer->addFilter(new Zend_Log_Filter_Priority($console_level));
		$file_writer->addFilter(new Zend_Log_Filter_Priority($file_level));		
		
		$logger = new Zend_Log();
		$logger->addWriter($console_writer);
		$logger->addWriter($file_writer);
		
		$logger->setEventItem('taskName',$name);
		
		//want to include the the taskName in the log messages
		$format = '%timestamp% [%taskName%] %priorityName% : %message%'.PHP_EOL;
		$formatter = new Zend_Log_Formatter_Simple($format);
		
		$file_writer->setFormatter($formatter);
		$console_writer->setFormatter($formatter);
		
		return $logger;
	}
	
	protected function checkConfig() {
		return true;
	}
	
}
?>