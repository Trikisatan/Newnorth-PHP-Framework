<?php
namespace Framework\Controls;

class PasswordBoxControl extends \Framework\Newnorth\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $ClassName = '';
	public $Label = '';
	public $Value = '';
	public $Placeholder = '';
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
}
?>