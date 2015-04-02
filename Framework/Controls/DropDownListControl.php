<?
namespace Framework\Controls;

class DropDownListControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['UseJavaScript'] = isset($this->_Parameters['UseJavaScript']) ? $this->_Parameters['UseJavaScript'] == true : false;

		$this->_Parameters['IsDisabled'] = isset($this->_Parameters['IsDisabled']) ? $this->_Parameters['IsDisabled'] == true : false;

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

	public function AddOption($Value, $Text) {
		if(isset($this->_Parameters['Options'])) {
			$this->_Parameters['Options'][$Value] = $Text;
		}
		else {
			$this->_Parameters['Options'] = [$Value => $Text];
		}
	}
}
?>