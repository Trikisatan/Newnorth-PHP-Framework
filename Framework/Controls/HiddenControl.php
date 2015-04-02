<?
namespace Framework\Controls;

class HiddenControl extends InputControl {
	/* Life cycle methods */

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