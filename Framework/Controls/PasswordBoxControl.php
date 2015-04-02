<?
namespace Framework\Controls;

class PasswordBoxControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['UseJavaScript'] = isset($this->_Parameters['UseJavaScript']) ? $this->_Parameters['UseJavaScript'] == true : false;

		if(isset($this->_Parameters['Validators'])) {
			$this->ParseParameters_Validators($this->_Parameters['Validators']);
		}
	}

	/* Instance methods */

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>