<?
/* Dependencies */

require('Framework/Newnorth/Newnorth.php');

require('Framework/HTML/HTML.php');

require('Framework/MySQL/MySQL.php');

/* Framework methods */

function A() {
	return $GLOBALS['Application'];
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

\Framework\Newnorth\Initialize();

\Framework\Newnorth\Run();
?>