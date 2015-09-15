<?
namespace Framework\HTML;

class Attribute {
	/* Instance variables */

	public $Key;

	public $Value;

	/* Constructor */

	public function __construct($Key, $Value) {
		$this->Key = $Key;

		$this->Value = $Value;
	}

	/* Magic methods */

	public function __toString() {
		if($this->Value === null) {
			return $this->Key;
		}
		else {
			return $this->Key.'="'.htmlspecialchars($this->Value, ENT_QUOTES).'"';
		}
	}
}
?>