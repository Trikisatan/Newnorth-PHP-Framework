<?php
require('Framework/Newnorth/Newnorth.php');

session_start();

ob_start();

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