<?php
//FILE_ROOT from config
checkExtensions();
//set some defaults
date_default_timezone_set('Europe/London');	//probably needs to be configurable
bcscale(2);	//default to 2dp on BC operations, defaults to 0 otherwise

//the top-level dirs:
define('LIB_ROOT',FILE_ROOT.'lib/');		//this is for the standard classes
define('APP_ROOT',FILE_ROOT.'app/');		//this is for app-specific models, controllers and templates
define('USER_ROOT',FILE_ROOT.'user/');	//a folder allowing for certain things to be over-ridden
define('DATA_ROOT',FILE_ROOT.'data/');	//the folder that generated files get put in- NEEDS TO BE WRITABLE!
define('SIMPLE_TEST',FILE_ROOT.'simpletest/');
define('THEME_ROOT',FILE_ROOT.'themes/');
define('CORE_ROOT',FILE_ROOT.'egs/');
//things inside lib
define('CLASS_ROOT',LIB_ROOT.'classes/');

//things inside app
define('STANDARD_TPL_ROOT',APP_ROOT.'templates/');
define('CONTROLLER_ROOT',APP_ROOT.'controllers/');
define('MODEL_ROOT',APP_ROOT.'models/');

//Classes are in some sub-folders:
define('INTERFACE_ROOT',CLASS_ROOT.'interfaces/');
define('UTIL_ROOT',CLASS_ROOT.'utils/');
define('VALIDATOR_ROOT',CLASS_ROOT.'validators/');
define('FORMATTER_ROOT',CLASS_ROOT.'formatters/');
define('SEARCHES_ROOT',CLASS_ROOT.'searches/');
define('HANDLER_ROOT',CLASS_ROOT.'autohandlers/');
define('ROUTES_ROOT',CLASS_ROOT.'routes/');
define('IMPLEMENTATIONS_ROOT',CLASS_ROOT.'implementations/');

define('EGLET_ROOT',APP_ROOT.'eglets/');

//core_root is for front_end things
define('CORE_MODEL_ROOT',CORE_ROOT.'models/');
define('CORE_CONTROLLERS_ROOT',CORE_ROOT.'controllers/');


define('APP_IMPLEMENTATIONS_ROOT',APP_ROOT.'classes/implementations/');
define('APP_CLASS_ROOT',APP_ROOT.'classes/');


define('MAGPIE_CACHE_DIR', DATA_ROOT.'cache/');
require UTIL_ROOT.'AutoLoader.php';
$scan_dirs=array(APP_CLASS_ROOT,UTIL_ROOT,CORE_CONTROLLERS_ROOT,CONTROLLER_ROOT,CORE_MODEL_ROOT,MODEL_ROOT,CLASS_ROOT,INTERFACE_ROOT,APP_IMPLEMENTATIONS_ROOT,IMPLEMENTATIONS_ROOT,VALIDATOR_ROOT,FORMATTER_ROOT,SEARCHES_ROOT,HANDLER_ROOT,ROUTES_ROOT);
$autoloader=&AutoLoader::Instance();
foreach($scan_dirs as $path) {
	$autoloader->addPath($path);
}
spl_autoload_register(array($autoloader, 'load'));
//then require the base things, as these will probably be always needed
require CLASS_ROOT.'DataObject.php';
require CLASS_ROOT.'Controller.php';
require CLASS_ROOT.'ControllerFactory.php';
require CLASS_ROOT.'ActionFactory.php';
require CLASS_ROOT.'DataObjectCollection.php';
require CLASS_ROOT.'FieldValidator.php';
require CLASS_ROOT.'DataField.php';
require CLASS_ROOT.'View.php';
require CLASS_ROOT.'PageList.php';
//setup smarty
require LIB_ROOT.'smarty/Smarty.class.php';
require LIB_ROOT.'adodb/adodb.inc.php';
require LIB_ROOT.'cake_inflector/Inflector.php';
showtime('post-library-load');


if(!isset($theme)) {
	$theme='default';

}

//default layout (that can be over-written later)
//@todo: move the default somewhere else


//date format
//@todo move to user preferences
$dateFormat =  "d/m/Y";
$dateTimeFormat = "d/m/Y H:i";

EGS::setDateFormat($dateFormat);
define('DATE_FORMAT',$dateFormat);
EGS::setDateTimeFormat($dateTimeFormat);
define('DATE_TIME_FORMAT',$dateTimeFormat);
if(!defined('EGS_CURRENCY_SYMBOL')) {
define('EGS_CURRENCY_SYMBOL','£');
}
EGS::setCurrencySymbol(EGS_CURRENCY_SYMBOL);
function isLoggedIn() {
	//return true;
	return (isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==true);
}
function setLoggedIn() {
	$_SESSION['loggedin']=true;
}
function setupLoggedInUser() {
showtime('start-user');
	if(empty($_SESSION['username'])) {
		session_destroy();
		header("Location: /");
		exit;
	}
	
	define('EGS_USERNAME',$_SESSION['username']);
	EGS::setUsername($_SESSION['username']);
	if (isset($_GET['companyselector'])) {
		$access = new Usercompanyaccess();
		if ($access->loadBy(array('username','organisation_id'),array(EGS_USERNAME,$_GET['companyselector']))) {
			$user=new User();
			$user->update(EGS_USERNAME,'lastcompanylogin',$_GET['companyselector']);
			$_SESSION['EGS_COMPANY_ID'] = $_GET['companyselector'];
		}
	}
	if(isset($_SESSION['EGS_COMPANY_ID']) && ($_SESSION['EGS_COMPANY_ID'] != 'EGS_COMPANY_ID')) {
		define('EGS_COMPANY_ID',$_SESSION['EGS_COMPANY_ID']);
		EGS::setCompanyId($_SESSION['EGS_COMPANY_ID']);
		$_SESSION['EGS_COMPANY_ID']=EGS_COMPANY_ID;
	}
	else {
		if(!isset($user)) {
			$user=new User();
		}
		$user->load(EGS_USERNAME);
		$lcl=$user->lastcompanylogin;
		if(!empty($lcl))  {
			define('EGS_COMPANY_ID',$lcl);
		}
		else {
			$db = DB::Instance();
			$person=new Person();
			$person->load($user->person_id);
			$organisation_id=$person->organisation_id;
			$query = "select * from system_companies where company_id=$company_id";
			$result = $db->GetOne($query);
			if (!empty($result))
				define('EGS_COMPANY_ID',$company_id);
			else {
				$query = "select company_id from user_company_access where username='{$_SESSION['username']}'";
				if ($companies = $db->GetArray($query)) {
					define('EGS_COMPANY_ID',$companies[0]['company_id']);
				}
				else
					define('EGS_COMPANY_ID',$person->usercompanyid);
			}
			
		}
		$_SESSION['EGS_COMPANY_ID']=EGS_COMPANY_ID;
	}
	//UserPreferences::instance(EGS_USERNAME);
	
	if (defined('EGS_COMPANY_ID')) {
		$autoloader = &AutoLoader::Instance();
		define('USER_IMPLEMENTATIONS_ROOT',USER_ROOT.EGS_COMPANY_ID.'/classes/implementations/');
		$autoloader->addBefore(USER_IMPLEMENTATIONS_ROOT,APP_IMPLEMENTATIONS_ROOT);
	}
	if(function_exists('appSetupLoggedInUser')) {
		appSetupLoggedInUser();
	}
	$username=EGS_USERNAME;
	if(empty($username)) {
		$user->load(EGS_USERNAME);
	}
	showtime('end-user');
}
function getCurrentUser() {
	if(!isLoggedIn()) {
		return false;
	}
	static $user;
	$key = 'user_'.EGS_USERNAME.SERVER_ROOT.EGS_COMPANY_ID;
	if(!isset($user)) {
		if(!HAS_APC||false===($user=apc_fetch($key))) {
			$user = new User();
			$user->load(EGS_USERNAME);
			if(HAS_APC) {
				apc_store($key,serialize($user));
			}
		}
		elseif(HAS_APC) {
			$user = unserialize($user);
		}
	}
	return $user;
}

/**
 * Redirect to a new page (params depend on implementation...)
 * 
 * @param String $controller
 * @param String $action
 * @param String|Array $module
 * @param Array $params
 */
function sendTo() {
	global $injector;
	$redirector=$injector->instantiate('Redirection');
	$redirector->Redirect(func_get_args());
}
function sendBack($status=null) {
	if($status!==null&&isset($_GET['ajax'])) {
		die($status);
	}
	sendTo(
		$_SESSION['refererPage']['controller'],
		$_SESSION['refererPage']['action'],
		$_SESSION['refererPage']['module'],
		isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null
	);
}

function with(&$params,&$smarty) {
	$with=$smarty->get_template_vars('with');
	if(!is_array($with))
		return;
	foreach($with as $key=>$val) {
		if(empty($params[$key]))
			$params[$key]=$val;
	}

}

function fix_latin($data) {
	$cp1252_map = array(
		"/\x80/" => "\xE2\x82\xAC",  // EURO SIGN
		"/\x82/" => "\xE2\x80\x9A",  // SINGLE LOW-9 QUOTATION MARK
		"/\x83/" => "\xC6\x92",      // LATIN SMALL LETTER F WITH HOOK
		"/\x84/" => "\xE2\x80\x9E",  // DOUBLE LOW-9 QUOTATION MARK
		"/\x85/" => "\xE2\x80\xA6",  // HORIZONTAL ELLIPSIS
		"/\x86/" => "\xE2\x80\xA0",  // DAGGER
		"/\x87/" => "\xE2\x80\xA1",  // DOUBLE DAGGER
		"/\x88/" => "\xCB\x86",      // MODIFIER LETTER CIRCUMFLEX ACCENT
		"/\x89/" => "\xE2\x80\xB0",  // PER MILLE SIGN
		"/\x8A/" => "\xC5\xA0",      // LATIN CAPITAL LETTER S WITH CARON
		"/\x8B/" => "\xE2\x80\xB9",  // SINGLE LEFT-POINTING ANGLE QUOTATION MARK
		"/\x8C/" => "\xC5\x92",      // LATIN CAPITAL LIGATURE OE
		"/\x8E/" => "\xC5\xBD",      // LATIN CAPITAL LETTER Z WITH CARON
		"/\x91/" => "\x27",  // LEFT SINGLE QUOTATION MARK
		"/\x92/" => "\x27",  // RIGHT SINGLE QUOTATION MARK
		"/\x93/" => "\x22",  // LEFT DOUBLE QUOTATION MARK
		"/\x94/" => "\x22",  // RIGHT DOUBLE QUOTATION MARK
		"/\x95/" => "\xE2\x80\xA2",  // BULLET
		"/\x96/" => "\xE2\x80\x93",  // EN DASH
		"/\x97/" => "\xE2\x80\x94",  // EM DASH
		"/\x98/" => "\xCB\x9C",      // SMALL TILDE
		"/\x99/" => "\xE2\x84\xA2",  // TRADE MARK SIGN
		"/\x9A/" => "\xC5\xA1",      // LATIN SMALL LETTER S WITH CARON
		"/\x9B/" => "\xE2\x80\xBA",  // SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
		"/\x9C/" => "\xC5\x93",      // LATIN SMALL LIGATURE OE
		"/\x9E/" => "\xC5\xBE",      // LATIN SMALL LETTER Z WITH CARON
		"/\x9F/" => "\xC5\xB8"       // LATIN CAPITAL LETTER Y WITH DIAERESIS
	);
	$data = preg_replace(array_keys($cp1252_map), array_values($cp1252_map), $data);
	
	return $data;
}

function input_verify_utf8($data) {
	$data = preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', '', $data);
	if (mb_detect_encoding($data) == 'UTF-8') {
		return $data;
	}
	$data = fix_latin($data);
	if (preg_match('![\xC0-\xDF]([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\xE0-\xEF].{0,1}([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\xF0-\xF7].{0,2}([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\xF8-\xFB].{0,3}([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\xFC-\xFD].{0,4}([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\xFE-\xFE].{0,5}([\x00-\x7F]|$)!s', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('![\x00-\x7F][\x80-\xBF]!', $data)) {
		return utf8_encode($data);
	}
	if (preg_match('!\xFF!', $data)) {
		return utf8_encode($data);
	}
	return $data;
}

function h($string, $quote_style = ENT_NOQUOTES, $nl2br = true) {
	static $cache;
	if(is_object($string) && is_callable(array($string,'__toString'))) {
		$string = $string->__toString();
	}
	//if(utf8_encode(utf8_decode($string)) != $string) {
		$string = input_verify_utf8($string);
	//}
	if(!isset($cache[$quote_style][$string])) {
		$html_escaped = htmlspecialchars($string,$quote_style,'UTF-8');
		if (empty($html_escaped)) {
			$html_escaped = htmlspecialchars($string,$quote_style);
		}
		$cache[$quote_style][$string] = $nl2br ? nl2br($html_escaped) : $html_escaped;
	}
	return $cache[$quote_style][$string];
}

function u($string) {
	return urlencode(str_replace(' ','_',$string));
}

function un_u($string) {
	return str_replace('_',' ',urldecode($string));
}

function file_path_concat($path,$array) {
	$scanpath = $path;

	foreach($array as $dir)
	{
		$scanpath .= $dir."/";
	}
	return $scanpath;

}

function link_to($params,$data=false) {
	global $injector;
	try {
		$linkbuilder = $injector->Instantiate('LinkBuilding');
	}
	catch(Exception $e) {
		$linkbuilder=new EGSLinkBuilder();
	}
	return $linkbuilder->build($params,$data);
}

function prettify($word) {
	static $cache;
	static $translator;
	$key = (defined('EGS_COMPANY_ID')?EGS_COMPANY_ID:'').SERVER_ROOT.$word;
	if(!isset($cache[$word])) {
		if(!HAS_APC||false===($pretty=apc_fetch($key))) {
			if(!isset($translator)) {
				global $injector;
				$translator=$injector->instantiate('Translation');
			
			}
			$pretty= $translator->translate($word);
			if(HAS_APC&&$pretty!='EGS_HIDDEN_FIELD') {
				apc_store($key,$pretty);
			}
		}
		$cache[$word]=$pretty;
	}
	return $cache[$word];
}

function cssify($css) {
	return '<style type="text/css">'.$css.'</style>';
}

function pricify($number,$html=true) {
	if($html) {
		$symbol =h(EGS::getCurrencySymbol());
	}
	else {
		$symbol = EGS::getCurrencySymbol();
	}
	return $symbol.number_format($number,2);
}

function sizify($number) {
	if (!is_numeric($number))
		return $number;
	if ($number < 1024)
		return $number . ' B';
	if ($number < (1024*1024))
		return floor($number/1024) . ' KB';
	return number_format($number/(1024*1024),2). ' MB';
}

function isModuleAdmin($name=null) {
	$router= RouteParser::Instance();
	if (isset($name)) {
		$module = $name;
	}
	else {
		$module=$router->dispatch('module');
	}
	global $injector;
	try {
		$checker = $injector->instantiate('ModuleAdminChecking');
		return $checker->isModuleAdmin(EGS::getUsername(),$module);
	}
	catch(PhemtoException $e) {
		//@todo make EGS implement ModuleAdminChecking
	
		if(isset($_SESSION['module_admins'])) {
			$cache = $_SESSION['module_admins'];
		}
		else {
			$cache=array();
		}
		if(!isset($cache[$module])) {
			$access= AccessObject::Instance();
			
			$db = DB::Instance();
			if(count($access->roles)>0) {
				$roles_string='';
				foreach ($access->roles as $role) {
					$roles_string.=$role.',';
				}
				$roles_string=rtrim($roles_string,',');
				$query = 'SELECT module_name FROM module_admins WHERE role_id IN ('.$roles_string.') AND module_name='.$db->qstr($module);
				$module = $db->GetOne($query);
			}
			else {
				$module=false;
			}
			if($module!==false) {
				$cache[$module]=true;
			}
			else {
				foreach ($access->tree as $treenode) {
					if ($treenode['name'] == 'egs')
						$cache[$module]=true;
				}
				$cache[$module]=false;
			}
		}
		$_SESSION['module_admins'][$module]=$cache[$module];
		return $cache[$module];
	}
}

function checkPermission() {
	global $access;
	global $modules;
	global $controllername;
	global $action;
	
	$continue = false;
	if(!$access->hasPermission($modules, $controllername, $action) && !$continue) {
	
		$flash = Flash::Instance();
		$flash->clear();
		$access->save();
		$flash->addError("You do not have access to the requested action.");
		$flash->save();
		$count = count($modules);

		if(strtolower($action) == 'index') {

			if($controllername !== 'IndexController') {
				sendTo('','index', $modules);
			}
			else {
				if($count <= 1) {
					sendTo('','index','dashboard');
				}
				// The x = 1; $x < $count is not a coding error is is to get all modulues but the last one
				for($x = 1; $x < $count; $x++)
				{
					$mod[] = $modules[$x-1];
				}
				sendTo('','index', $mod);
			}
		}
		else {
			sendTo(substr('controller','',strtolower($controllername)),'index',$modules);
		}

	}
	
}
/**
 * Will return an array of all the values between $min and $max, with separation $step
 * e.g. getRange(0,1,0.2) will return [0.0,0.2,0.4,0.6,0.8,1.0]
 * - will maintain the precision of the most precise argument
* @see maxdp()
 */
function getRange($min,$max,$step,$keys=false,$value_prefix='',$value_suffix='',$signed=false,$ignore_zero=false) {
	$values=array();
	$dp=maxdp($min,$max,$step);
		
	for($i=$min;$i<=$max;$i+=$step) {
		if($ignore_zero&&$i==0) {
			continue;
		}
		$value=sprintf('%01.'.$dp.'f',$i);
		if($signed&&floatval($value)>0) {
			$value='+'.$value;
		}
		if($keys) {
			$values[$value]=$value_prefix.$value.$value_suffix;
		}
		else {
			$values[]=$value;
		}
	}
	return $values;
}

/**
 *  Returns the maximum number of decimal places found in the supplied arguments
 *e.g. maxdp(0.6,1.2,1.23); will return 2
 */
function maxdp() {
	$dp=0;
	$args=func_get_args();
	foreach($args as $arg) {
		if(strrpos($arg,'.')!==false&&(strlen(strval($arg))-strrpos(strval($arg),'.'))-1>$dp)
		$dp=strlen(strval($arg))-strrpos(strval($arg),'.')-1;
	}
	//echo $dp;
	return $dp;
}

function to_working_days($time,$suffix=true) {
	$time = explode(':',$time);
	$hours = $time[0];
	$minutes = $time[1];
	
	$day_length = SystemCompanySettings::DAY_LENGTH;
	$hours = $hours + ($minutes/60);
	
	$days = $hours / $day_length;
	$suffix_text = ($suffix)?' days':'';
	return $days.$suffix_text;
}
function coalesce() {
	$args = func_get_args();
	foreach($args as $arg) {
		if($arg!==null) {
			return $arg;
		}
	}
}
function decorate($object,$decorator) {
	if(class_exists($decorator)) {
		$decorator = new $decorator($object);
		return $decorator;
	}
	throw new Exception('Class not found: '.$decorator);
}
function date_strip($date_string) {
	list($date,$time) = explode(' ',$date_string);
	list($time,) = explode('.',$time);
	list($h,$m,) = explode(':',$time);
	return $date.' '.$h.':'.$m;
}
function overdue($date) {
	$o_date=$date;
	$date = fix_date($date);
	$t_date = strtotime($date);
	if($t_date<time()) {
		$return = '<em class="overdue_date">'.$o_date.'</em> ';
		return $return;
	}
	return $o_date;
}
function month_to_string($number) {
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	return $months[$number-1];
	//return date('F',($number%12-1)*(60*60*24*31));
}
function month_to_short_string($number) {
	$months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	return $months[$number-1];
	//return date('F',($number%12-1)*(60*60*24*31));
}
function trunc($num,$precision=0) {
	$mult = pow(10,$precision);
	return floor($num*$mult)/$mult;
}
function fix_date($date) {

	$format = format_for_strptime(EGS::getDateFormat());
	$date_array = strptime($date,$format);
	if($date_array===false) {
		return false;
	}
	$month = sprintf('%02d',$date_array['tm_mon']+1);
	
	$year = $date_array['tm_year']+1900;
	if($year < 100) {
		$year+=($year>70)?1900:2000;
	}
	$day = sprintf('%02d',$date_array['tm_mday']);
	
	$date = $year.'-'.$month.'-'.$day;
	return $date;
}
function format_for_strptime($format) {
	$format = '%'.str_replace(array('/',' ',':i'),array('/%',' %',':%M'),$format);
	return $format;
}
function un_fix_date($date) {
	return date(EGS::getDateFormat(),strtotime($date));
}
function last_day($month, $year) {
    // Use mktime to create a timestamp one month into the future, but one
    //  day less.  Also make the time for almost midnight, so it can be
    //  used as an 'end of month' boundary
    return mktime(23, 59, 59, $month + 1, 0, $year);
}

/**
 *  $trans_date is expected to be a 'fixed_date', i.e. yyyy-mm-dd
 */
function calc_due_date($trans_date,$basis,$days=0,$months=0) {
	
	if($basis=='I'||$basis=='Invoice') {
		$time = strtotime($trans_date);
	}
	else if($basis=='M'||$basis=='Month'){
		$month = date('m',strtotime($trans_date));
		$year = date('Y',strtotime($trans_date));
		$month_end = last_day($month+$months,$year);
		$time = $month_end;
	}
	else {
		throw new Exception('calc_due_date only understands Invoice and Month type payment term logic');
	}
	$time = strtotime('+ '.$days.' days',$time);
	return date('Y-m-d',$time);
}

function calc_tax_percentage($rate_id,$status_id,$amount) {
	global $injector;
	$tax_calc = $injector->instantiate('TaxCalculation');
	return $tax_calc->calc_percentage($rate_id,$status_id,$amount);
}

function logtime($msg='') {
	$starttime=START_TIME;
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime= $mtime;
	$fp=fopen('/tmp/timelog5','a+');
	fwrite($fp,$msg.($endtime-$starttime)."\n");
	fclose($fp);
	echo $msg.($endtime-$starttime).'<br>';
}

function checkExtensions() {
	$extensions = array('pgsql','json');
	$fail=false;
	foreach($extensions as $ext) {
		if(!extension_loaded($ext)) {
			echo "Missing extension: $ext<br>\n";
			$fail=true;
		}
	}
	if($fail) {
		die("Missing Extensions, see above");
	}

	$check_for = array('apc','memcache','imagick');
	foreach($check_for as $ext) {
		if(extension_loaded($ext)) {
			define('HAS_'.strtoupper($ext),true);
		}
		else {
			define('HAS_'.strtoupper($ext),false);
		}
	}
	return true;
}
function getFields_apc($tablename) {
	static $cache;
	$key = DB_NAME.$tablename;
	if(!isset($cache[$tablename])) {
		if(false===($fields=apc_fetch($key))) {
			$fields=getFields_none($tablename);	
			apc_store($key,serialize($fields));
		}
		else {
			$fields = unserialize($fields);
		}
		$cache[$tablename]=$fields;
	}
	return $cache[$tablename];
}
function getFields_file($tablename) {
	static $cache;
	if(!isset($cache[$tablename])) {
		$path = DATA_ROOT.'tmp/fields_'.$tablename;
		if(!file_exists($path)) {
			$fields=getFields_none($tablename);	
			$fp=fopen($path,'w+');
			fwrite($fp,serialize($fields));
			fclose($fp);
		}
		else {
			$fields = unserialize(file_get_contents($path));
		}
		$cache[$tablename] = $fields;
	}
	return $cache[$tablename];
}
function getFields_static($tablename) {
	static $cache;
	if(!isset($cache[$tablename])) {
		$fields=getFields_none($tablename);	
		$cache[$tablename]=$fields;
	}
	return $cache[$tablename];
	
}
function getFields_none($tablename) {
	$db=&DB::Instance();
	$fields = $db->MetaColumns($tablename,false);
	if($fields===false) {
		throw new Exception("Error getting metadata for " . $tablename . ":" . $db->ErrorMsg());
	}
	
	$return=array();
	return $fields;
}
function getFields($tablename) {
	if(defined('PRODUCTION')&&PRODUCTION&&HAS_APC) {
		$method = 'apc';
	}
	else if(defined('PRODUCTION')&&PRODUCTION) {
		$method='file';
	}
	else {
		$method='static';
	}
	$func = 'getFields_'.$method;
	$fields= $func($tablename);
	
	// Lets clean up any messed up defaults
	foreach ($fields as $name => $values) {
		if (isset($values->default_value)) {
			if (strpos($values->default_value, '::')) {
				$bits = explode('::', $values->default_value);
				$values->default_value = $bits[0];		
			}
		}
	}
	
	$return=array();
	foreach($fields as $field) {
		$return[$field->name]=clone $field;
	}
	return $return;
}
function showtime($msg='',$return=false) {
	return;
	static $prev=0;
	$msg=empty($msg)?$msg:$msg.': ';
	global $starttime;
	$time = microtime(true);
	$time = ($time - $starttime);

	$diff=$time-$prev;
	$prev=$time;
	if($return) {
		//return $msg."		".str_pad($time,20)."			($diff)\n<br>";
	}
//	echo str_pad($msg,30,'-')."			".str_pad($time,20)."			(".($diff*1).")\n<br>";
}
?>
