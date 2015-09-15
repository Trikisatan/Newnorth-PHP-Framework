<?
namespace Framework;

class DropDownListControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Directory = $GLOBALS['Config']->Files['Controls'].'Framework/';

		$this->_Namespace = '\\Framework\\';

		$this->_Name = 'DropDownListControl';

		parent::__construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters);
	}

	/* Life cycle methods */

	public function Initialize() {
		if(method_exists($this->_Parent, 'SetControlId_'.$this->_Alias)) {
			$this->_Parameters['Id'] = $this->_Parent->{'SetControlId_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlName_'.$this->_Alias)) {
			$this->_Parameters['Name'] = $this->_Parent->{'SetControlName_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlOptions_'.$this->_Alias)) {
			$this->_Parameters['Options'] = $this->_Parent->{'SetControlOptions_'.$this->_Alias}($this);
		}

		parent::Initialize();
	}

	/* Validator methods */

	public function GetValueValidator($Parameters) {
		if(isset($this->_Parameters['Options'])) {
			$Value = $_GET[$this->_Parameters['Name']];

			foreach($this->_Parameters['Options'] as $Option) {
				if($Option['Value'] === $Value) {
					return true;
				}
			}

			return false;
		}
		else {
			return false;
		}
	}

	public function PostValueValidator($Parameters) {
		if(isset($this->_Parameters['Options'])) {
			$Value = $_POST[$this->_Parameters['Name']];

			foreach($this->_Parameters['Options'] as $Option) {
				if($Option['Value'] === $Value) {
					return true;
				}
			}

			return false;
		}
		else {
			return false;
		}
	}

	/* Instance methods */

	public function CreateOption($Text, $Value) {
		$this->_Parameters['Options'][] = [
			'Text' => $Text,
			'Value' => $Value,
		];
	}

	public function DeleteOptionByText($Text) {
		$Index = $this->IndexOfOptionByText($Text);

		if($Index === -1) {
			return false;
		}
		else {
			array_splice($this->_Parameters['Options'], $Index, 1);

			return true;
		}
	}

	public function DeleteOptionByValue($Value) {
		$Index = $this->IndexOfOptionByValue($Value);

		if($Index === -1) {
			return false;
		}
		else {
			array_splice($this->_Parameters['Options'], $Index, 1);

			return true;
		}
	}

	public function IndexOfOptionByText($Text) {
		for($I = 0; $I < count($this->_Parameters['Options']); ++$I) {
			if($this->_Parameters['Options'][$I]['Text'] === $Text) {
				return $I;
			}
		}

		return -1;
	}

	public function IndexOfOptionByValue($Value) {
		for($I = 0; $I < count($this->_Parameters['Options']); ++$I) {
			if($this->_Parameters['Options'][$I]['Value'] === $Value) {
				return $I;
			}
		}

		return -1;
	}

	/* Instance methods */

	public function CreateElement($Name, $Attributes, $OptionalAttributes, $Parameters, $Html) {
		$Element = new \Framework\HTML\Element($Name);

		foreach($Attributes as $Key => $Value) {
			$Element->CreateAttribute(
				$Key,
				$Value
			);
		}

		foreach($OptionalAttributes as $Alias => $Key) {
			if(isset($Parameters[$Alias])) {
				$Element->CreateAttribute(
					$Key,
					$Parameters[$Alias]
				);
			}
		}

		if($Html !== null) {
			$Element->AppendHtml($Html);
		}

		return $Element;
	}
}
?>