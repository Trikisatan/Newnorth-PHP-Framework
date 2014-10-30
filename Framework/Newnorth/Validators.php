<?php
namespace Framework\Newnorth;

class Validators {
	/* Methods */
	public function Token($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return $_POST[$Subject->Name] === GetToken();
		}
		else if($Subject !== null) {
			return $Subject === GetToken();
		}
		else {
			return $_POST['Token'] === GetToken();
		}
	}
	public function DropDownList($Control) {
		for($I = 0; $I < count($Control->Options); $I++) {
			if($Control->Options[$I]['Value'] === $_POST[$Control->Name]) {
				return true;
			}
		}

		return false;
	}
	public function EMailAddressFormat($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/', $_POST[$Subject->Name]);
		}

		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/', $Subject);
	}
	public function FileUploaded($Control) {
		return 0 < $_FILES[$Control->Name]['size'];
	}
	public function ValueNotEmpty($Control) {
		return isset($_POST[$Control->Name][0]);
	}
	public function RenderValueNotEmpty($Control, $Data) {
		return 'return 0<this.value.length';
	}
	public function RenderValueRegExp($Control, $Data) {
		return 'return -1<this.value.search('.$Data['RegExp'].')';
	}
	public function MaxLength($Subject, $MaxLength = 0) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return isset($_POST[$Subject->Name][$Subject->MaxLength]);
		}

		return !isset($Subject[$MaxLength]);
	}
	public function MinLength($Subject, $MinLength = 1) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return isset($_POST[$Subject->Name][$Subject->MinLength - 1]);
		}

		return isset($Subject[$MinLength - 1]);
	}
	public function IsDigits($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return ctype_digit($_POST[$Subject->Name]);
		}

		return ctype_digit($Subject);
	}
	public function IsBetweenIntegers($Subject, $MinValue, $MaxValue) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			$Value = (int)$_POST[$Subject->Name];
			return $Subject->MinValue <= $Value && $Value <= $Subject->MaxValue;
		}

		$Value = (int)$Subject;
		return $MinValue <= $Value && $Value <= $MaxValue;
	}
}
?>