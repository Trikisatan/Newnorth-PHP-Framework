<?
namespace Framework\Controls;

class CheckBoxControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['IsChecked'] = isset($this->_Parameters['IsChecked']) ? $this->_Parameters['IsChecked'] === true || $this->_Parameters['IsChecked'] === '1' : false;

		$this->_Parameters['IsDisabled'] = isset($this->_Parameters['IsDisabled']) ? $this->_Parameters['IsDisabled'] === true : false;

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
		$this->_Parameters['IsChecked'] = ($Value === '1');
	}
}
?>