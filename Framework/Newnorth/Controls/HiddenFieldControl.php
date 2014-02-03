<?php
namespace Halvsmekt\Controls;

class HiddenFieldControl extends \Framework\Halvsmekt\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $Value = '';
	
	/* Actions */
	public function Initialize() {
		
	}
	public function Load() {
		
	}
	public function Execute() {
		
	}
	
	/* Methods */
	public function AutoFill($Value) {
		if($Value === null)
			return;
		$this->Value = $Value;
	}
}
?>