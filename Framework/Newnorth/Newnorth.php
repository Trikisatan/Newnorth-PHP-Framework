<?php
require('Application.php');
require('Layout.php');
require('Page.php');
require('Validators.php');
require('Action.php');
require('Actions.php');
require('EMail.php');
require('HtmlRenderer.php');
require('Route.php');
require('Translations.php');
require('Control.php');
require('Controls.php');
require('DataManager.php');
require('DataType.php');

use \Framework\Newnorth\Application;
use \Framework\Newnorth\Layout;
use \Framework\Newnorth\Page;

/* Error handling */
function ConfigError($Message, $Data = array()) {
	Application::HandleError(
		'Configuration error',
		$Message,
		array(
			'Data' => $Data,
		)
	);
}

function ErrorCatcher($Severity, $Message, $File, $Line, $Variables) {
	throw new ErrorException($Message, 0, $Severity, $File, $Line);
}

set_error_handler('ErrorCatcher');

function GetLocale() {
	return Application::GetLocale();
}

function GetConnection($Name) {
	return Application::GetConnection($Name);
}

function GetDataManager($Name) {
	return Application::GetDataManager($Name);
}

function GetToken() {
	return Application::GetToken();
}

function GenerateUrl(array $Parameters) {
	return Application::GenerateUrl($Parameters);
}

function Redirect($Location) {
	if(is_array($Location)) {
		header('Location: '.Application::GenerateUrl($Location));
	}
	else {
		header('Location: '.$Location);
	}

	exit();
}

/* String methods.
/* * * * */

function String_StartsWith($Haystack, $Needle) {
	return substr($Haystack, 0, strlen($Needle)) === $Needle;
}

function String_EndsWith($Haystack, $Needle) {
	return substr($Haystack, -strlen($Needle)) === $Needle;
}

/* Miscellaneous methods.
/* * * * */

function ParseIniFile($Path, $Split = true) {
	try {
		$Data = @parse_ini_file($Path, true);
	}
	catch(Exception $Exception) {
		return false;
	}

	if($Data === false) {
		return false;
	}

	if($Split) {
		foreach($Data as $SectionKey => &$SectionValue) {
			if(is_array($SectionValue)) {
				foreach($SectionValue as $Key => $Value) {
					$Keys = explode('_', $Key);

					if(1 < count($Keys)) {
						unset($SectionValue[$Key]);

						$Var = &$SectionValue[$Keys[0]];

						for($I = 1; $I < count($Keys); ++$I) {
							$Var = &$Var[$Keys[$I]];
						}

						$Var = $Value;
					}
				}
			}
		}
	}

	return $Data;
}
?>