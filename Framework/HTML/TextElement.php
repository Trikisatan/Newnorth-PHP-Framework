<?
namespace Framework\HTML;

class TextElement extends AElement {
	/* Instance variables */

	public $Text;

	/* Constructor */

	public function __construct($Text) {
		$this->Text = $Text;
	}

	/* Magic methods */

	public function __toString() {
		return htmlspecialchars($this->Text);
	}
}
?>