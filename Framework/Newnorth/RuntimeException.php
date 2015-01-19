<?
namespace Framework\Newnorth;

class RuntimeException extends Exception {
	/* Magic methods */

	public function __construct($Message, $Data = []) {
		parent::__construct('Runtime exception', $Message, $Data);
	}
}
?>