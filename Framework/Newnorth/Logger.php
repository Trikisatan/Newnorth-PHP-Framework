<?
namespace Framework\Newnorth;

class Logger {
	/* Static methods */

	public static function Log($Data) {
		file_put_contents(Config('Logger/File'), json_encode($Data)."\n", FILE_APPEND);
	}
}
?>