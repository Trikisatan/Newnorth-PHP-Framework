<?
namespace Framework\HTML;

class Element extends AElement {
	/* Instance variables */

	public $Name;

	public $Attributes;

	public $Children;

	/* Constructor */

	public function __construct($Name) {
		$this->Name = $Name;

		$this->Attributes = new \Framework\HTML\Attributes();

		$this->Children = new \Framework\HTML\Elements();
	}

	/* Magic methods */

	public function __toString() {
		if(0 < count($this->Attributes->Items)) {
			if(0 < count($this->Children->Items)) {
				return '<'.$this->Name.' '.$this->Attributes->__toString().'>'.$this->Children->__toString().'</'.$this->Name.'>';
			}
			else {
				return '<'.$this->Name.' '.$this->Attributes->__toString().'></'.$this->Name.'>';
			}
		}
		else if(0 < count($this->Children->Items)) {
			return '<'.$this->Name.'>'.$this->Children->__toString().'</'.$this->Name.'>';
		}
		else {
			return '<'.$this->Name.'></'.$this->Name.'>';
		}
	}

	/* Instance methods */

	public function CreateAttribute($Key, $Value) {
		$this->Attributes->Create($Key, $Value);

		return $this;
	}

	public function PrependChild(\Framework\HTML\AElement $Element) {
		$Element->Parent = $this;

		$this->Children->Prepend($Element);

		return $this;
	}

	public function PrependHtml($Html) {
		$Element = new \Framework\HTML\HtmlElement($Html);

		$Element->Parent = $this;

		$this->Children->Prepend($Element);

		return $this;
	}

	public function PrependText($Text) {
		$Element = new \Framework\HTML\TextElement($Text);

		$Element->Parent = $this;

		$this->Children->Prepend($Element);

		return $this;
	}

	public function AppendChild(\Framework\HTML\AElement $Element) {
		$Element->Parent = $this;

		$this->Children->Append($Element);

		return $this;
	}

	public function AppendHtml($Html) {
		$Element = new \Framework\HTML\HtmlElement($Html);

		$Element->Parent = $this;

		$this->Children->Append($Element);

		return $this;
	}

	public function AppendText($Text) {
		$Element = new \Framework\HTML\TextElement($Text);

		$Element->Parent = $this;

		$this->Children->Append($Element);

		return $this;
	}
}
?>