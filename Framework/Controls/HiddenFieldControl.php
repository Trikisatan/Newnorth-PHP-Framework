<?php
namespace Framework\Controls;

class HiddenFieldControl extends \Framework\Newnorth\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $Value = '';

	/* Events */
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