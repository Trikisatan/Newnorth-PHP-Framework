<?php
ob_start();

require('Framework/Halvsmekt/Halvsmekt.php');

session_start();

if(!isset($_COOKIE['Token'])) {
	$Token = md5(rand());
	setcookie('Token', $Token);
	$_COOKIE['Token'] = $Token;
	unset($Token);
}

try {
	$Layout = null;
	$Page = null;
	$Application = new \Framework\Halvsmekt\Application();
	$Application->Run();
}
catch(ErrorException $Exception) {
	ob_clean();
	echo $Exception->getMessage();
	exit();
}

ob_end_flush();
?>