<?php
namespace Halvsmekt\Controls;

class DropDownListControl extends \Framework\Halvsmekt\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $ClassName = '';
	public $Label = '';
	public $Value = '';
	public $OnChange = '';
	public $Options = array();
	
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
	public function AddOption($Value, $Text) {
		$this->Options[] = array(
			'Value' => $Value,
			'Text' => $Text
		);
	}
}
?>