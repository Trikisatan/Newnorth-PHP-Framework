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
		if(method_exists($this->_Parent, 'SetControl»Id»'.$this->_Alias)) {
			$this->_Parameters['Id'] = $this->_Parent->{'SetControl»Id»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Name»'.$this->_Alias)) {
			$this->_Parameters['Name'] = $this->_Parent->{'SetControl»Name»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Value»'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControl»Value»'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControl»Readonly»'.$this->_Alias)) {
			$this->_Parameters['Readonly'] = $this->_Parent->{'SetControl»Readonly»'.$this->_Alias}($this);
		}

		parent::Initialize();
	}

	/* Pre formatter methods */

	public function PreFormatters»GetTrim($Parameters) {
		$_GET[$this->_Parameters['Name']] = trim($_GET[$this->_Parameters['Name']]);
	}

	public function PreFormatters»PostTrim($Parameters) {
		$_POST[$this->_Parameters['Name']] = trim($_POST[$this->_Parameters['Name']]);
	}

	/* Validator methods */

	public function Validators»GetDateTime($Parameters) {
		return strptime($_GET[$this->_Parameters['Name']], $Parameters['Format']) !== false;
	}

	public function Validators»GetIsBetweenExclusive($Parameters) {
		return $Parameters['Min'] < $_GET[$this->_Parameters['Name']] && $_GET[$this->_Parameters['Name']] < $Parameters['Max'];
	}

	public function Validators»GetIsBetweenInclusive($Parameters) {
		return $Parameters['Min'] <= $_GET[$this->_Parameters['Name']] && $_GET[$this->_Parameters['Name']] <= $Parameters['Max'];
	}

	public function Validators»GetIsEMailAddress($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_GET[$this->_Parameters['Name']]);
	}

	public function Validators»GetIsFloat($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_GET[$this->_Parameters['Name']])) {
				return true;
			}
		}

		if(isset($Parameters['AllowEmpty']) && $Parameters['AllowEmpty']) {
			if(!isset($_GET[$this->_Parameters['Name']][0])) {
				return true;
			}
		}

		return is_numeric($_GET[$this->_Parameters['Name']]);
	}

	public function Validators»GetIsInt($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_GET[$this->_Parameters['Name']])) {
				return true;
			}
		}

		if(isset($Parameters['AllowEmpty']) && $Parameters['AllowEmpty']) {
			if(!isset($_GET[$this->_Parameters['Name']][0])) {
				return true;
			}
		}

		return ctype_digit($_GET[$this->_Parameters['Name']]);
	}

	public function Validators»GetMaxLength($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function Validators»GetMinLength($Parameters) {
		return isset($_GET[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function Validators»GetNotEmpty($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_GET[$this->_Parameters['Name']])) {
				return true;
			}
		}

		return isset($_GET[$this->_Parameters['Name']][0]);
	}

	public function Validators»GetUrl($Parameters) {
		return 0 < preg_match('/^'.preg_quote($Parameters['Protocol'], '/').'.+$/', $_GET[$this->_Parameters['Name']]);
	}

	public function Validators»PostDateTime($Parameters) {
		return strptime($_POST[$this->_Parameters['Name']], $Parameters['Format']) !== false;
	}

	public function Validators»PostIsBetweenExclusive($Parameters) {
		return $Parameters['Min'] < $_POST[$this->_Parameters['Name']] && $_POST[$this->_Parameters['Name']] < $Parameters['Max'];
	}

	public function Validators»PostIsBetweenInclusive($Parameters) {
		return $Parameters['Min'] <= $_POST[$this->_Parameters['Name']] && $_POST[$this->_Parameters['Name']] <= $Parameters['Max'];
	}

	public function Validators»PostIsEMailAddress($Parameters) {
		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+)?$/', $_POST[$this->_Parameters['Name']]);
	}

	public function Validators»PostIsFloat($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_POST[$this->_Parameters['Name']])) {
				return true;
			}
		}

		if(isset($Parameters['AllowEmpty']) && $Parameters['AllowEmpty']) {
			if(!isset($_POST[$this->_Parameters['Name']][0])) {
				return true;
			}
		}

		return is_numeric($_POST[$this->_Parameters['Name']]);
	}

	public function Validators»PostIsInt($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_POST[$this->_Parameters['Name']])) {
				return true;
			}
		}

		if(isset($Parameters['AllowEmpty']) && $Parameters['AllowEmpty']) {
			if(!isset($_POST[$this->_Parameters['Name']][0])) {
				return true;
			}
		}

		return ctype_digit($_POST[$this->_Parameters['Name']]);
	}

	public function Validators»PostMaxLength($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Max']]);
	}

	public function Validators»PostMinLength($Parameters) {
		return isset($_POST[$this->_Parameters['Name']][$Parameters['Min']]);
	}

	public function Validators»PostNotEmpty($Parameters) {
		if(isset($Parameters['AllowUnset']) && $Parameters['AllowUnset']) {
			if(!isset($_POST[$this->_Parameters['Name']])) {
				return true;
			}
		}

		return isset($_POST[$this->_Parameters['Name']][0]);
	}

	public function Validators»PostUrl($Parameters) {
		return 0 < preg_match('/^'.preg_quote($Parameters['Protocol'], '/').'.+$/', $_POST[$this->_Parameters['Name']]);
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