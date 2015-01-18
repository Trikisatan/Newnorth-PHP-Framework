<?
namespace Framework\Controls;

use \Framework\Newnorth\Control;

class HiddenControl extends Control {
	/* Events */

	public function Initialize() {

	}

	public function Load() {

	}

	public function Execute() {

	}

	/* Methods */

	public function ParseParameters() {
		
	}

	public function AutoFill($Value) {
		$this->_Parameters['Value'] = $Value;
	}
}
?>