<?php
namespace Framework\Controls;

require_once('HiddenFieldControl.php');

class TokenControl extends HiddenFieldControl {
	/* Events */
	public function Initialize() {
		if(!isset($this->Name[0])) {
			$this->Name = 'Token';
		}

		if(!isset($this->Value[0])) {
			$this->Value = GetToken();
		}
	}
}
?>