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

function Config($Key = null, $DefaultValue = null) {
	if($Key === null) {
		return $GLOBALS['Config'];
	}
	else if(isset($GLOBALS['Config']->Data[$Key])) {
		return $GLOBALS['Config']->Data[$Key];
	}
	else {
		return $DefaultValue;
	}
}

function DataManager($Alias) {
	return $GLOBALS['Application']->GetDataManager($Alias);
}

function CreateUrl($Path = '', array $Parameters = [], $QueryString = '') {
	return \Framework\Newnorth\Router::CreateUrl($Path, $Parameters, $QueryString);
}

/* Execution of application */

\Framework\Newnorth\Initialize(['Config.ini']);

\Framework\Newnorth\Run();
?>