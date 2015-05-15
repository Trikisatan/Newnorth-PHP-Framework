<?
namespace Framework\Controls;

class RadioButtonControl extends \Framework\Newnorth\Control {
	/* Life cycle methods */

	public function PostExecute() {
		parent::PostExecute();

		if(method_exists($this->_Parent, 'SetControlValue_'.$this->_Alias)) {
			$this->_Parent->{'SetControlValue_'.$this->_Alias}($this);
		}

		if(method_exists($this->_Parent, 'SetControlIsChecked_'.$this->_Alias)) {
			$this->_Parent->{'SetControlIsChecked_'.$this->_Alias}($this);
		}
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
}
?>