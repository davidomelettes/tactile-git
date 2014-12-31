<?php
define('DB_NAME','tactile');
define('DB_USER','tactile');
define('DB_HOST','10.110.63.49');
define('DB_PASSWORD','QJ1mb0b#');
define('SERVER_ROOT','http://'.$_SERVER['HTTP_HOST']);
define('PRODUCTION',true);


define('SECPAY_MID','senoki01');
define('SECPAY_DIGEST','cd371beab414deba');
define('SECPAY_REMOTE','402909d37798f9f1');
define('SECPAY_VPN_PASSWORD','4ca6d4ad4c818748');
define('TACTILE_DROPBOX_WEBSITE', 'dropbox@5njde3coyb2i.team.mail.tactilecrm.com');
define('TACTILE_DROPBOX', 'dropbox@5njde3coyb2i.team.mail.tactilecrm.com');
//this wants to be either 'true', 'false' or 'live' - 'false' doesn't mean live!

//this is currently only used from the http part, cli uses the value in tasks_config:
define('SECPAY_TEST_STATUS','live');

define('S3_ACCESS_KEY','12CWNKWQR36VMW116MG2');
define('S3_SECRET','xoIVna7FrPmaKCpbQNaAIQVOpCfDMMbpbIOQGVSq');
define('S3_DEFAULT_BUCKET', 'tactile_test');
define('S3_PUBLIC_BUCKET', 'tactile_public');
define('TACTILE_GDATA_CONTACTS_PROCESSOR_URL', 'https://google.tactilecrm.com/');
define('GOOGLE_API_KEY', 'ABQIAAAA8j3ThjOT-ioi1DBEUXw8WxTSAEHeTknPD1pMnv6ga5vMlK091hS7v7Zoec8-XGyIw0XWS4nYDtHo4Q');

define('NOTIFICATIONS_TO', 'support@tactilecrm.com');
define('DEBUG_EMAIL_ADDRESS', 'support@tactilecrm.com');

define('RECAPTCHA_PRIVATE_KEY', '6LegwroSAAAAAGaXKo4PdFmsukG1B5aT4IdYMAzj');
define('RECAPTCHA_PUBLIC_KEY', '6LegwroSAAAAAByUGrKRSxoEg2LkfN_pXo2JRy35');
define('TACTILE_API_DOMAIN', 'team');
define('TACTILE_API_KEY', 'e9d2665b2ed9991a01ece6b5c6f265ab89499b52');
