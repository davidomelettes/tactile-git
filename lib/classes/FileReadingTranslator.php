<?php
require_once LIB_ROOT.'spyc/spyc.php';
if(isset($_GET['module'])) {
define('MODULE',$_GET['module']);
}
else
define('MODULE','dashboard');
/**
 * Responsible for translating words according to system-company-specific 'translations'
 * Designed for differences in terminology as opposed to foreign-language
 * @author gj
 */
class FileReadingTranslator extends Prettifier implements Translation {
	
	/**
	 * An array of already-looked-up strings for quick re-use
	 * @access protected
	 * @static Array $strings
	 */
	protected static $strings=array();

	/**
	 * Takes a string, looks to see if a system-wide or company-specific translation exists, else falls back to Prettifier
	 * 
	 * @param String $string
	 * @return String
	 */
	function translate($string) {
		if(empty(self::$strings[MODULE])) {
			if(defined('EGS_COMPANY_ID')) {
				$file_path=FILE_ROOT.'user/'.EGS_COMPANY_ID.'/labels/'.MODULE.'/labels.yml';
			}
			if (!defined('EGS_COMPANY_ID')||!file_exists($file_path)) {
				$file_path=FILE_ROOT.'user/labels/'.MODULE.'/labels.yml';
			}
			$array=Spyc::YAMLLoad($file_path);
			self::$strings[MODULE]=$array;	
		}
		if(isset(self::$strings[MODULE][strtolower($string)])) {
			return self::$strings[MODULE][strtolower($string)];
		}
		if(empty(self::$strings['global'])) {
			if(defined('EGS_COMPANY_ID')) {
				$file_path=FILE_ROOT.'user/'.EGS_COMPANY_ID.'/labels/global/labels.yml';
			}
			if (!defined('EGS_COMPANY_ID')||!file_exists($file_path)) {
				$file_path=FILE_ROOT.'user/labels/global/labels.yml';
			}
			$array=Spyc::YAMLLoad($file_path);
			self::$strings['global']=$array;	
		}
		if(isset(self::$strings['global'][strtolower($string)])) {
			return self::$strings['global'][strtolower($string)];
		}
		
		return parent::translate($string);
	}
}
?>
