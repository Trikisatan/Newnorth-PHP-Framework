<?
namespace Framework\Controls;

use \Framework\Newnorth\Control;

class TokenControl extends Control {
	/* Life cycle methods */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Methods */

	public function ParseParameters() {
		$this->_Parameters['Name'] = isset($this->_Parameters['Name']) ? $this->_Parameters['Name'] : 'Token';
		$this->_Parameters['Value'] = isset($this->_Parameters['Value']) ? $this->_Parameters['Value'] : $_SESSION['Token'];
	}

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>