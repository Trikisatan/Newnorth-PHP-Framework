<?
class TextBoxControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}
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

		if($this->_Parameters['AllowEmpty']) {
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

		if($this->_Parameters['AllowEmpty']) {
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

		if($this->_Parameters['AllowEmpty']) {

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

		if($this->_Parameters['AllowEmpty']) {
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
}
?>