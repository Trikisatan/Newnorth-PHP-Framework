<?php
namespace Framework\Controls;

class CheckBoxControl extends \Framework\Newnorth\Control {
	/* Variables */
	public $Id = '';
	public $Name = '';
	public $ClassName = '';
	public $Label = '';
	public $Value = '';
	public $IsChecked = false;
	public $ErrorMessages = array();

	/* Actions */
	public function Initialize() {
		$this->IsChecked = ($this->IsChecked === true || $this->IsChecked === '1');
	}
	public function Load() {

	}
	public function Execute() {

	}

	/* Methods */
	public function AutoFill() {
		$this->IsChecked = ($Value !== null);
	}
}
?>