<?
namespace Framework\Newnorth;

class Exception extends \Exception {
	/* Variables */

	public $Type;

	public $Data;

	/* Magic methods */

	public function __construct($Type, $Message, $Data = []) {
		parent::__construct($Message);

		$this->Type = $Type;

		$this->Data = $Data;
	}
}
?>