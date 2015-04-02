<?
namespace Framework\Controls;

class ValueIntervalBoxControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['UseJavaScript'] = isset($this->_Parameters['UseJavaScript']) ? $this->_Parameters['UseJavaScript'] == true : false;

		$this->_Parameters['LowerIsDisabled'] = isset($this->_Parameters['LowerIsDisabled']) ? $this->_Parameters['LowerIsDisabled'] == true : false;

		$this->_Parameters['LowerIsReadOnly'] = isset($this->_Parameters['LowerIsReadOnly']) ? $this->_Parameters['LowerIsReadOnly'] == true : false;

		$this->_Parameters['UpperIsDisabled'] = isset($this->_Parameters['UpperIsDisabled']) ? $this->_Parameters['UpperIsDisabled'] == true : false;

		$this->_Parameters['UpperIsReadOnly'] = isset($this->_Parameters['UpperIsReadOnly']) ? $this->_Parameters['UpperIsReadOnly'] == true : false;

		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}

	public function SetValue() {
		if(method_exists($this->_Parent, $this->_Alias.'_SetLowerValue')) {
			$this->_Parent->{$this->_Alias.'_SetLowerValue'}($this);
		}

		if(method_exists($this->_Parent, $this->_Alias.'_SetUpperValue')) {
			$this->_Parent->{$this->_Alias.'_SetUpperValue'}($this);
		}
	}

	/* Instance methods */

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>