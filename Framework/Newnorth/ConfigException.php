<?
namespace Framework\Newnorth;

class ConfigException extends Exception {
	/* Magic methods */

	public function __construct($Message, $Data = []) {
		parent::__construct('Config exception', $Message, $Data);
	}
}
?>