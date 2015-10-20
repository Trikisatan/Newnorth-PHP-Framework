<?
/* Dependencies */

require('Framework/Newnorth/Newnorth.php');

require('Framework/HTML/HTML.php');

require('Framework/MySQL/MySQL.php');

/* Framework methods */

function DM($Alias) {
	return $GLOBALS['Application']->GetDataManager($Alias);
}

/* Execution of application */

\Framework\Newnorth\Initialize();

\Framework\Newnorth\Run();
?>