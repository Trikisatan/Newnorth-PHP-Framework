<?
/* Dependencies */

require('Framework/Newnorth/Newnorth.php');

require('Framework/HTML/HTML.php');

require('Framework/MySQL/MySQL.php');

/* Framework methods */

function A() {
	return $GLOBALS['Application'];
}

function C($Key = null, $DefaultValue = null) {
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

function DM($Alias) {
	return $GLOBALS['Application']->GetDataManager($Alias);
}

function L() {
	return $GLOBALS['Layout'];
}

function P() {
	return $GLOBALS['Page'];
}

/* Execution of application */

\Framework\Newnorth\Initialize(['Config.ini']);

\Framework\Newnorth\Run();
?>