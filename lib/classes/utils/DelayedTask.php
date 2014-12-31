<?php
require_once LIB_ROOT.'spyc/spyc.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

/**
 * Base class for tasks which are delayed. Handles loading and saving of the YAML
 * @abstract
 * @author gj
 * @package Tasks
 */
abstract class DelayedTask {
	/**
	 * Contains the data to be serialized
	 * @var Array $data
	 */
	protected $data = array();
	
	/**
	 * The path of the job-file after it is 'locked'
	 * @var String $job_file
	 */
	protected $job_file;
	
	/**
	 * Whether or not saving should include constants such as username,companyid etc.
	 * @access protected
	 * @var Boolean
	 */
	protected $use_constants = true;
	
	/**
	 * An instance of Phemto, used for handling dependencies
	 *
	 * @var Phemto
	 */
	protected $injector;
	
	/**
	 * An instance of Zend_Log, used for logging things
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	protected $restored = false;
	
	/**
	 * A DelayedTaskStorage instance
	 *
	 * @var DelayedTaskStorage
	 */
	protected $storage;
	
	/**
	 * A DelayedTaskStorage instance to use for saving and loading
	 *
	 * @var DelayedTaskStorage
	 */
	protected static $default_storage;
	
	/**
	 * Constructor - specify whether or not constants (username etc.) should be included when saving
	 * @param Boolean $use_constants
	 * @param Zend_Log $logger
	 */
	public function __construct($use_constants=true,Zend_Log $logger = null) {
		$this->use_constants = $use_constants;
		if(is_null($logger)) {
			$logger = new Zend_Log();
			$logger->addWriter(new Zend_Log_Writer_Null());	
		}
		$this->logger = $logger;
		$this->logger->setEventItem('taskName',get_class($this));

		$this->setupStorage();
	}
	
	public static function setDefaultStorage(DelayedTaskStorage $storage) {
		self::$default_storage = $storage;
	}
	
	protected function setupStorage() {
		if(!is_null(self::$default_storage)) {
			$this->storage = self::$default_storage;
		}
		else {
			$this->storage = new DelayedTaskYAMLStorage();
			DelayedTaskYAMLStorage::$task_folder = DATA_ROOT.'jobs/';
		}
	}
	
	/**
	 * Given the path of a job-file, works out what class to use
	 * @todo maybe shouldn't parse the YAML here and when the Task is loaded?
	 * 
	 * @param String $filename
	 * @param Zend_Log $logger
	 * @return DelayedTask
	 */
	public static function Factory($filename,Zend_Log $logger = null) {
		$data = Spyc::YAMLLoad($filename);
		if(class_exists($data['task_type'])) {
			return new $data['task_type'](false,$logger);
		} else {
			throw new Exception('Failed to Factory() DelayedTask! ' . $filename . ' ' . print_r($data,1));
		}
	}
	
	/**
	 * Take a previously saved job-file and grab the data
	 * @param String $job_file The filepath of the job-file (the file-contents will also work)
	 */
	public function load($job_file, $lock=true) {
		$this->logger->info('Loading '.$job_file);

		$this->data = $this->storage->read($job_file, $lock);
		
		if(isset($this->data['EGS_COMPANY_ID'])) {
			EGS::setCompanyId($this->data['EGS_COMPANY_ID']);
			$this->logger->debug('EGS_COMPANY_ID='.EGS::getCompanyId());
		}
		if(isset($this->data['EGS_USERNAME'])) {
			EGS::setUsername($this->data['EGS_USERNAME']);
			require_once 'Zend/Auth.php';
			require_once 'Zend/Auth/Storage/NonPersistent.php';
			$auth = Zend_Auth::getInstance();
			$auth->setStorage(new Zend_Auth_Storage_NonPersistent());
			$auth->getStorage()->write(EGS::getUsername());
			$this->logger->debug('EGS_USERNAME='.EGS::getUsername());
		}
		if(isset($this->data['USER_SPACE'])) {
			Omelette::setUserSpace($this->data['USER_SPACE']);
			$this->logger->debug('USER_SPACE='.Omelette::getUserSpace());
		}
	}
	
	/**
	 * Save the task to a YAML file
	 * 
	 * Includes the setting of 'type'
	 * @return void
	 */
	public function save() {
		$this->data['task_type'] = get_class($this);
		$this->data['iteration'] = 1;
		
		if($this->use_constants) {
			$this->data['EGS_USERNAME'] = EGS::getUsername();
			$this->data['EGS_COMPANY_ID'] = EGS::getCompanyId();
			$this->data['USER_SPACE'] = Omelette::getUserSpace();
		}
		$this->storage->write($this->data);
	}
	
	/**
	 * Useful for when tasks are being run straight away, rather than with a delay
	 * @return void
	 */
	public function saveAndLoad() {
		$this->save();
		$this->load();
	}
	
	function setInjector($injector) {
		$this->injector = $injector;
	}
	
	/**
	 * Carry out the task, called after loading
	 */
	abstract public function execute();
	
	final public function remove() {
		$this->storage->remove();
	}
	
	/**
	 * Removes the job_file from the queue
	 * 
	 * execute() should call this
	 */
	protected function cleanup() {
		$this->remove();
		$_SESSION = array();
	}
	
	/**
	 * Puts the job file back into the folder, so task can be run again
	 * @return void
	 */
	public function restore() {
		$this->storage->unlock();
	}
		
}
