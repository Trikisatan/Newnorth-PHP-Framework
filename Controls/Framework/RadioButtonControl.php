<?
namespace Framework;

class RadioButtonControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		$this->_Directory = $GLOBALS['Config']->Files['Controls'].'Framework/';

		$this->_Namespace = '\\Framework\\';

		$this->_Name = 'RadioButtonControl';

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

		if(method_exists($this->_Parent, 'SetControlIsChecked_'.$this->_Alias)) {
			$this->_Parameters['IsChecked'] = $this->_Parent->{'SetControlIsChecked_'.$this->_Alias}($this);
		}

		parent::Initialize();
	}

	/* Validator methods */

	public function GetIsCheckedValidator($Parameters) {
		return isset($_POST[$this->_Parameters['Name']]);
	}

	public function GetIsNotCheckedValidator($Parameters) {
		return !isset($_POST[$this->_Parameters['Name']]);
	}

	public function PostIsCheckedValidator($Parameters) {
		return isset($_POST[$this->_Parameters['Name']]);
	}

	public function PostIsNotCheckedValidator($Parameters) {
		return !isset($_POST[$this->_Parameters['Name']]);
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