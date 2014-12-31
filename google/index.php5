<?php
require_once '../conf/config.php';
if(!isset($_GET['token'])) {
	exit;
}
//header("Location: ".$_GET['return_url'].'?token='.$_GET['token']);
header("Location: ".str_replace('google',$_GET['return_url'],SERVER_ROOT).'/import/google/?token='.urlencode($_GET['token']));
exit;
?>
