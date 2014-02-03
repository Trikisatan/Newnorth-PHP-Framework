<?php
require('Application.php');

require('Validators.php');

require('Action.php');
require('Actions.php');
require('Layout.php');
require('Page.php');
require('Route.php');
require('Translations.php');
require('Control.php');
require('Controls.php');
require('DataManager.php');
require('DataType.php');
require('MySql/Connection.php');
require('MySql/Result.php');

function ConfigError($Message, $Data = array()) {
	echo '<b>Config error</b><br />';
	echo $Message, '<br />';

	foreach($Data as $Key => $Value) {
		echo '<b>', $Key, ':</b> ', $Value, '<br />';
	}

	exit();
}
function ErrorHandler($Severity, $Message, $File, $Line, $Variables) {
	$Message =
		'<b>Runtime error</b><br />'.
		$Message.'<br />'.
		'<b>Severity:</b> '.$Severity.'<br />'.
		'<b>File:</b> '.$File.'<br />'.
		'<b>Line:</b> '.$Line.'<br />'.
		'<b>Variables</b><br />';

	foreach($Variables as $Key => $Value) {
		$Message .=
			'<b>'.$Key.':</b> '.$Value.'<br />';
	}

	throw new ErrorException($Message, 0, $Severity, $File, $Line);
}
function GenerateUrl($Route = null, $Parameters = array()) {
	return \Framework\Halvsmekt\Application::GenerateUrl($Route, $Parameters);
}
function GetConnection($Name) {
	return \Framework\Halvsmekt\Application::GetConnection($Name);
}
function GetDataManager($Name) {
	return \Framework\Halvsmekt\Application::GetDataManager($Name);
}
function ParseIniFile($Path) {
	try {
		$Data = @parse_ini_file($Path, true);
	}
	catch(Exception $Exception) {
		return false;
	}

	if($Data === false) {
		return false;
	}

	foreach($Data as $SectionKey => $SectionValue) {
		if(is_array($SectionValue)) {
			foreach($SectionValue as $Key => $Value) {
				$Keys = explode('_', $Key);

				if(1 < count($Keys)) {
					$Var = &$SectionValue[$Keys[0]];

					for($I = 1; $I < count($Keys); ++$I) {
						$Var = &$Var[$Keys[$I]];
					}

					$Var = $Value;
				}
			}

			$Data[$SectionKey] = $SectionValue;
		}
	}

	return $Data;
}

set_error_handler("ErrorHandler");
?>