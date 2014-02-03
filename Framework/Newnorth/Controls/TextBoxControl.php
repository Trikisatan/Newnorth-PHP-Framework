<?php
namespace Halvsmekt\Controls;

class TextBoxControl extends \Framework\Halvsmekt\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $ClassName = '';
	public $Label = '';
	public $Value = '';
	public $Placeholder = '';
	public $MaxLength = 0;
	public $ErrorMessages = array();
	
	/* Actions */
	public function Initialize() {
		// Typecast the MaxLength-value.
		$this->MaxLength = (int)$this->MaxLength;
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