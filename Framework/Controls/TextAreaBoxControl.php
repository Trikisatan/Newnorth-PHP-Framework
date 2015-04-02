<?
namespace Framework\Controls;

class TextAreaBoxControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['IsDisabled'] = isset($this->_Parameters['IsDisabled']) ? $this->_Parameters['IsDisabled'] === true : false;

		$this->_Parameters['IsReadOnly'] = isset($this->_Parameters['IsReadOnly']) ? $this->_Parameters['IsReadOnly'] === true : false;

		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}

	public function SetValue() {
		if(method_exists($this->_Parent, $this->_Alias.'_SetValue')) {
			$this->_Parent->{$this->_Alias.'_SetValue'}($this);
		}
	}

	/* Instance methods */

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>