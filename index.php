<?
session_start();

ob_start();

/* Error handler */

function ErrorHandler($Severity, $Message, $File, $Line, $Variables) {
	if(error_reporting() !== 0) {
		throw new ErrorException($Message, 0, $Severity, $File, $Line);
	}
}

set_error_handler('ErrorHandler');

/* Includes */

require('Framework/Newnorth/Newnorth.php');

/* Execution of application */

use \Framework\Newnorth\Application as Application;

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
		[
			'File' => $Exception->getFile(),
			'Line' => $Exception->getLine(),
		],
		$Exception->getTrace()
	);
}
catch(Exception $Exception) {
	Application::HandleError(
		'Exception',
		$Exception->getMessage(),
		[
			'File' => $Exception->getFile(),
			'Line' => $Exception->getLine(),
		],
		$Exception->getTrace()
	);
}
?>