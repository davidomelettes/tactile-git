<?php
/**
 * Responsible for autoloading models
 * @author gj
 * @see __autoload()
 */
class AutoLoader {
	/**
	 * An array containing the paths to look in for classes
	 * @access protected
	 * @var $paths
	 */
	protected $paths=array();
	
	protected static $autoloader;
	
	/**
	 * Takes an array of paths to start with
	 * @constructor
	 * @param Array $paths
	 */
	function __construct($paths=array()) {
		$this->paths=$paths;
	}
	
	/**
	 * Add a path to the end of the list of those to search through
	 * @param String $path
	 * @return void
	 */
	function addPath($path) {
		$this->paths=array_merge(array($path),$this->paths);		
	}
	
	/**
	 * Add a path to the list of those to search through, before one known to already be present
	 * 
	 * Useful for ensuring priority
	 * @param String $apath	The new path
	 * @param String $before The path to add it before
	 */
	function addBefore($apath,$before) {
		$temppaths = array();
		foreach($this->paths as $path) {
			if ($path <> $before)
				$temppaths[] = $path;
			else {
				$temppaths[] = $apath;
				$temppaths[] = $path;
			}
		}
		$this->paths = $temppaths;
	}
	
	/**
	 * Searches known locations for the given classname
	 * @todo Probably cache lookups, search+match is expensive
	 * @param String $classname
	 * @return void
	 */
	function load($classname) {
	
		$classname=preg_replace('[^a-zA-Z0-9_-]','',$classname);
		foreach($this->paths as $path) {
			if(file_exists($path.$classname.'.php')) {
				require $path.$classname.'.php';
				return;
			}
		}
	}

	/**
	 * Returns an instance of AutoLoader
	 * @return AutoLoader
	 */
	static function &Instance() {
		if(self::$autoloader==null) {
			self::$autoloader=new AutoLoader();
		}
		return self::$autoloader;
	}
	
}
?>
