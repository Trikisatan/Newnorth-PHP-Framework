<?
namespace Framework\Controls;

class TokenControl extends InputControl {
	/* Life cycle methods */

	public function ParseParameters() {
		$this->_Parameters['Name'] = isset($this->_Parameters['Name']) ? $this->_Parameters['Name'] : 'Token';

		$this->_Parameters['Value'] = isset($this->_Parameters['Value']) ? $this->_Parameters['Value'] : $_SESSION['Token'];
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