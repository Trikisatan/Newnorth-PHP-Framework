<?
namespace Framework;

class EMailAddressBoxControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		if($this->_Directory === null) {
			$this->_Directory = $GLOBALS['Config']->Files['Controls'].'Framework/';
		}

		if($this->_Namespace === null) {
			$this->_Namespace = '\\Framework\\';
		}

		if($this->_Name === null) {
			$this->_Name = 'EMailAddressBoxControl';
		}

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

		if(method_exists($this->_Parent, 'SetControlReadonly_'.$this->_Alias)) {
			$this->_Parameters['Readonly'] = $this->_Parent->{'SetControlReadonly_'.$this->_Alias}($this);
		}

		parent::Initialize();
	}

	/* Validator methods */

	public function GetIsEMailAddressValidator($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_GET[$this->_Parameters['Name']]);
	}

	public function GetMaxLengthValidator($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function GetMinLengthValidator($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function GetNotEmptyValidator($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][0]);
	}

	public function PostIsEMailAddressValidator($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_POST[$this->_Parameters['Name']]);
	}

	public function PostMaxLengthValidator($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function PostMinLengthValidator($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function PostNotEmptyValidator($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][0]);
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