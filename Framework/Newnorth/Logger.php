<?
namespace Framework\Newnorth;

class Logger {
	/* Static methods */

	public static function ErrorLog($Data) {
		file_put_contents($GLOBALS['Config']->Files['ErrorLog'], json_encode($Data)."\n", FILE_APPEND);
	}
}
?>