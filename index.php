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

use \Framework\Newnorth\Exception;
use \Framework\Newnorth\Application;

try {
	$Layout = null;

	$Page = null;

	$Application = new Application();

	$Application->Run();
}
catch(Exception $Exception) {
	Application::HandleError(
		$Exception->Type,
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		$Exception->Data,
		$Exception->getTrace()
	);
}
catch(\ErrorException $Exception) {
	Application::HandleError(
		'Error',
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		[],
		$Exception->getTrace()
	);
}
catch(\Exception $Exception) {
	Application::HandleError(
		'Unhandled exception',
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		[],
		$Exception->getTrace()
	);
}
?>