<?
namespace Framework\HTML;

class Attributes {
	/* Instance variables */

	public $Items = [];

	public $ItemsByKey = [];

	/* Magic methods */

	public function __toString() {
		if(0 < count($this->Items)) {
			$HTML = $this->Items[0]->__toString();

			for($I = 1; $I < count($this->Items); ++$I) {
				$HTML .= ' '.$this->Items[$I]->__toString();
			}

			return $HTML;
		}
		else {
			return '';
		}
	}

	/* Instance methods */

	public function Create($Key, $Value) {
		$Attribute = new \Framework\HTML\Attribute($Key, $Value);

		$this->Items[] = $Attribute;

		$this->ItemsByKey[$Key] = $Attribute;

		return $this;
	}
}
?>