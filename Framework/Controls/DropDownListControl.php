<?php
namespace Framework\Controls;

class DropDownListControl extends \Framework\Newnorth\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $ClassName = '';
	public $Label = '';
	public $Value = '';
	public $OnChange = '';
	public $Options = array();
	public $Validation = array();
	public $ErrorMessages = array();

	/* Actions */
	public function Initialize() {

	}
	public function Load() {

	}
	public function Execute() {

	}

	/* Methods */
	public function AutoFill($Value) {
		if($Value === null) {
			return;
		}

		$this->Value = $Value;
	}
	public function AddOption($Value, $Text) {
		$this->Options[$Value] = $Text;
	}
}
?>