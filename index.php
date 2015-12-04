<?
/* Dependencies */

require('Framework/Newnorth/Newnorth.php');

require('Framework/HTML/HTML.php');

require('Framework/MySQL/MySQL.php');

/* Framework methods */

function A() {
	return $GLOBALS['Application'];
}

function L() {
	return $GLOBALS['Layout'];
}

function P() {
	return $GLOBALS['Page'];
}

function Config($Key, $DefaultValue = null) {
	if(isset($GLOBALS['Config']->Data[$Key])) {
		return $GLOBALS['Config']->Data[$Key];
	}
	else {
		return $DefaultValue;
	}
}

function Config»Exist($Key) {
	return isset($GLOBALS['Config']->Data[$Key]);
}

function Config»DoesNotExist($Key) {
	return !isset($GLOBALS['Config']->Data[$Key]);
}

function Config»IsEmpty($Key) {
	return !isset($GLOBALS['Config']->Data[$Key][0]);
}

function Config»IsNotEmpty($Key) {
	return isset($GLOBALS['Config']->Data[$Key][0]);
}

function DataManager($Alias) {
	return $GLOBALS['Application']->GetDataManager($Alias);
}

function CreateUrl($Path = '', array $Parameters = [], $QueryString = '') {
	return \Framework\Newnorth\Router::CreateUrl($Path, $Parameters, $QueryString);
}

/* Execution of application */

\Framework\Newnorth\Initialize(['Config.json']);

\Framework\Newnorth\Run();
?>