<?php
/* Includes */
require('Framework/Newnorth/Newnorth.php');

/* Execution of application */
use \Framework\Newnorth\Application as Application;

session_start();

ob_start();

try {
	$Layout = null;
	$Page = null;
	$Application = new Application();
	$Application->Run();
}
catch(ErrorException $Exception) {
	Application::HandleError(
		'Runtime error',
		$Exception->getMessage(),
		array(
			'Details' => array(
				'Severity' => $Exception->getSeverity(),
				'File' => $Exception->getFile(),
				'Line' => $Exception->getLine(),
			),
			'StackTrace' => array_slice($Exception->getTrace(), 1)
		)
	);
}
catch(Exception $Exception) {
	Application::HandleError(
		'Unhandled exception',
		$Exception->getMessage(),
		array(
			'Details' => array(
				'Code' => $Exception->getCode(),
				'File' => $Exception->getFile(),
				'Line' => $Exception->getLine(),
			),
			'StackTrace' => $Exception->getTrace()
		)
	);
}

ob_end_flush();
?>