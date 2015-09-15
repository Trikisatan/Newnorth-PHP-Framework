<?
namespace Framework\HTML;

class HtmlElement extends AElement {
	/* Instance variables */

	public $Html;

	/* Constructor */

	public function __construct($Html) {
		$this->Html = $Html;
	}

	/* Magic methods */

	public function __toString() {
		return $this->Html;
	}
}
?>