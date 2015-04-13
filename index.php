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

/* Global variables */

$Config = null;

$Routing = null;

$Application = null;

$Layout = null;

$Page = null;

$Parameters = null;

/* Execution of application */

try {
	$Config = new \Framework\Newnorth\Config('Config.ini');

	$Config->Initialize();

	$Routing = new \Framework\Newnorth\Routing('Routes.ini');

	$Routing->Initialize();

	$Application = new \Framework\Newnorth\Application();

	$Application->Initialize();
}
catch(\Framework\Newnorth\Exception $Exception) {
	\Framework\Newnorth\ErrorHandler::HandleError(
		$Exception->Type,
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		$Exception->Data,
		$Exception->getTrace()
	);
}
catch(\ErrorException $Exception) {
	\Framework\Newnorth\ErrorHandler::HandleError(
		'Error',
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		[],
		$Exception->getTrace()
	);
}
catch(\Exception $Exception) {
	\Framework\Newnorth\ErrorHandler::HandleError(
		'Unhandled exception',
		$Exception->getMessage(),
		$Exception->getFile(),
		$Exception->getLine(),
		[],
		$Exception->getTrace()
	);
}

$Application->Execute();
?>