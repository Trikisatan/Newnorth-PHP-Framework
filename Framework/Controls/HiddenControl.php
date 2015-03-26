<?
namespace Framework\Controls;

use \Framework\Newnorth\Control;

class HiddenControl extends Control {
	/* Life cycle methods */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Instance methods */

	public function ParseParameters() {
		
	}

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>