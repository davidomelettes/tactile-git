<?php
define('START_TIME', microtime(true));
set_include_path(FILE_ROOT.'lib');
if(file_exists(FILE_ROOT.'conf/local.php')) {
	require_once FILE_ROOT.'conf/local.php';
}
else {
	require_once FILE_ROOT.'conf/config.php';
}
require_once FILE_ROOT.'omelette/setup.php';
$al = AutoLoader::Instance();
$al->addPath(FILE_ROOT.'app/classes/');
require_once FILE_ROOT.'app/routes.php';
define('APP_NAME','Tactile');
$al->addPath(FILE_ROOT.'app/models/');

// This is for generic emails
define('TACTILE_EMAIL_FROM','support@tactilecrm.com');
define('TACTILE_EMAIL_FROM_NO_REPLY','no-reply@tactilecrm.com');
define('TACTILE_EMAIL_NAME','The Tactile CRM Team');

// This is for 'real' person emails
define('TACTILE_EMAIL_SENDER', 'George Step');
define('TACTILE_EMAIL_ADDRESS', 'george@tactilecrm.com');

define('TRACKING_HOST', 'images.omelett.es');

Log_Writer_Mail::$email_from = TACTILE_EMAIL_FROM;

set_include_path(get_include_path().PATH_SEPARATOR.str_replace('public/../','',APP_CLASS_ROOT));

if (defined('PRODUCTION') && PRODUCTION == true) {
	define('OMELETTES_CM_API_KEY', '631cc52a3ed14b21cac26b0b46807028');
	define('OMELETTES_CM_LIST_ID', '58ba2364c8dc21b1fa3c9e9ed17569fe');

	define('XERO_INVOICING_PROVIDER_KEY', 'ZTDJMMRMNGE1MMUYNDFHYZK4NTG3MG');
	define('XERO_INVOICING_CUSTOMER_KEY', 'ZTLLOGJIYWY3NGYYNGUYYJGYMGM4MT');
} else {
	define('OMELETTES_CM_API_KEY', 'b88e7158f71fee7151e0363f076fb2ac');
	define('OMELETTES_CM_LIST_ID', 'de417cb5a11d0db6720633bae25a9b1e');

	define('XERO_INVOICING_PROVIDER_KEY', 'ZTDJMMRMNGE1MMUYNDFHYZK4NTG3MG');
	define('XERO_INVOICING_CUSTOMER_KEY', 'NDQ2ZTK2MDMYZGRMNDHJYJG5MJNJY2');
}



define('SHOEBOXED_TOKEN', 'l20lpio2ke8fj2l0kmxn28f0s0ka2sa');
define('SHOEBOXED_APPNAME', 'Tactile CRM');
define('SHOEBOXED_APPURL', SERVER_ROOT.'/import/upload');

define('IMAGE_THUMBNAILER_WIDTH', 30);
define('IMAGE_THUMBNAILER_HEIGHT', 30);

$openid_fs_dir = "/tmp/_php_consumer_test";
if (!file_exists($openid_fs_dir) && !mkdir($openid_fs_dir)) {
	throw new Exception('Failed to access OpenID storage directory: ' . $openid_fs_dir);
}
define('OPENID_FILESTORE_DIRECTORY', $openid_fs_dir);

define('OAUTH_CONSUMER_KEY', '796406897711.apps.googleusercontent.com');
define('OAUTH_CONSUMER_KEY_SECRET', 'neV7v85dufwCMf266Yk7aw97');
define('GOOGLE_APPS_APPLICATION_ID', '796406897711');



// Final resort for errors not caught elsewhere
function tactile_error_handler($errNo, $errStr, $errFile, $errLine) {
	$show_error_message = false;
	switch ($errNo) {
		case E_STRICT: //2048
		case E_NOTICE: //8
		case E_WARNING: //2
			// Unlikely to be application-breaking, so don't do anything
			break;
		case E_RECOVERABLE_ERROR: //4096
		//case E_USER_ERROR: //256
		case E_ERROR: //1
			$show_error_message = true;
			break;
	}
    
    if ($show_error_message) {
		echo <<<ERRORHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>An Error Has Occurred | Tactile CRM</title>
		<style type="text/css">
			html, body, h1, p, ol, li, pre { margin: 0; padding: 0; }
			body { font-family: Verdana, Sans-serif; }
			#container { width: 800px; margin: 2em auto; }
			h1 { line-height: 50px; background: #fff url(/graphics/tactile/tactile_medium.png) top left no-repeat; padding-left: 60px; }
			h1, p, ol, pre { margin-bottom: 1em; }
			ol { list-style-position: inside; }
			#xd { border: 1px solid #333; padding: 1em 1em 0 1em; }
			pre { font: monospace; max-height: 600px; overflow: auto; border: 1px dashed #ccc; padding: 0.5em; }
		</style>
	</head>
	<body>
		<div id="container">
			<h1>Oops! An Error Has Occurred</h1>
			<p>Tactile CRM has encountered a problem and is unable to deliver the page you expected.
			Details of this error have been sent to our technical team.</p>
			<p>We recommend trying the following things:</p>
			<ol>
				<li>Refresh the page</li>
				<li>Use your browser's 'back' button to return to where you came from</li>
				<li>If the problem persists, contact our support team via <a href="mailto:support@tactilecrm.com">support@tactilecrm.com</a></li>
			</ol>
ERRORHTML;
		if (ini_get('display_errors') === '1') {
			echo '<div id="xd"><p><strong>display_errors is on</strong></p><pre>'.$errFile.' ('.$errLine.')</pre><pre>'.$errStr.'</pre><pre>';
			debug_print_backtrace();echo "</pre></div>";
		}
		echo <<<ERRORHTML
		</div>
	</body>
</html>
		
ERRORHTML;
		exit(1);
    }
    
	return true;
}

set_error_handler('tactile_error_handler');

