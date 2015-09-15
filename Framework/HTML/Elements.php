<?
namespace Framework\HTML;

class Elements {
	/* Instance variables */

	public $Items = [];

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

	public function Prepend(\Framework\HTML\AElement $Element) {
		array_unshift($this->Items, $Element);

		return $this;
	}

	public function Append(\Framework\HTML\AElement $Element) {
		array_push($this->Items, $Element);

		return $this;
	}
}
?>