<?
namespace Framework;

class TextBoxControl extends \Framework\Newnorth\Control {
	/* Magic methods */

	public function __construct($Parent, $Directory, $Namespace, $Name, $Alias, $Parameters) {
		if($this->_Directory === null) {
			$this->_Directory = $GLOBALS['Config']->Files['Controls'].'Framework/';
		}

		if($this->_Namespace === null) {
			$this->_Namespace = '\\Framework\\';
		}

		if($this->_Name === null) {
			$this->_Name = 'TextBoxControl';
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

		parent::Initialize();
	}

	/* Validator methods */

	public function GetIsBetweenExclusiveValidator($Parameters) {
		return $Parameters['Min'] < $_GET[$this->_Parameters['Name']] && $_GET[$this->_Parameters['Name']] < $Parameters['Max'];
	}

	public function GetIsBetweenInclusiveValidator($Parameters) {
		return $Parameters['Min'] <= $_GET[$this->_Parameters['Name']] && $_GET[$this->_Parameters['Name']] <= $Parameters['Max'];
	}

	public function GetIsEMailAddressValidator($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_GET[$this->_Parameters['Name']]);
	}

	public function GetIsFloatValidator($Parameters) {
		$Value = $_GET[$this->_Parameters['Name']];

		if($Parameters['AllowEmpty']) {
			if(isset($Value[0])) {
				return is_numeric($Value);
			}
			else {
				return true;
			}
		}
		else {
			return is_numeric($_POST[$this->_Parameters['Name']]);
		}
	}

	public function GetIsIntValidator($Parameters) {
		$Value = $_GET[$this->_Parameters['Name']];

		if($Parameters['AllowEmpty']) {
			if(isset($Value[0])) {
				return ctype_digit($Value);
			}
			else {
				return true;
			}
		}
		else {
			return ctype_digit($Value);
		}
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

	public function PostIsBetweenExclusiveValidator($Parameters) {
		return $Parameters['Min'] < $_POST[$this->_Parameters['Name']] && $_POST[$this->_Parameters['Name']] < $Parameters['Max'];
	}

	public function PostIsBetweenInclusiveValidator($Parameters) {
		return $Parameters['Min'] <= $_POST[$this->_Parameters['Name']] && $_POST[$this->_Parameters['Name']] <= $Parameters['Max'];
	}

	public function PostIsEMailAddressValidator($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_POST[$this->_Parameters['Name']]);
	}

	public function PostIsFloatValidator($Parameters) {
		$Value = $_POST[$this->_Parameters['Name']];

		if($Parameters['AllowEmpty']) {

			if(isset($Value[0])) {
				return is_numeric($Value);
			}
			else {
				return true;
			}
		}
		else {
			return is_numeric($Value);
		}
	}

	public function PostIsIntValidator($Parameters) {
		$Value = $_POST[$this->_Parameters['Name']];

		if($Parameters['AllowEmpty']) {
			if(isset($Value[0])) {
				return ctype_digit($Value);
			}
			else {
				return true;
			}
		}
		else {
			return is_numeric($Value);
		}
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