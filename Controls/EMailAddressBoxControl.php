<?
class EMailAddressBoxControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parameters['Value'] = $this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}
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
}
?>