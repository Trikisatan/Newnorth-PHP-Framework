<?php
namespace Framework\Newnorth;

abstract class Validators {
	/* Methods */
	public function DropDownListValueValidation($Control) {
		for($I = 0; $I < count($Control->Options); $I++) {
			if($Control->Options[$I]['Value'] === $_POST[$Control->Name]) {
				return true;
			}
		}

		return false;
	}
	public function EMailAddressFormatValidation($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/', $_POST[$Subject->Name]);
		}

		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/', $Subject);
	}
	public function FileUploadedValidation($Control) {
		return 0 < $_FILES[$Control->Name]['size'];
	}
	public function ValueNotEmptyValidation($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return isset($_POST[$Subject->Name][0]);
		}

		return isset($Subject[0]);
	}
	public function ValueMaxLengthValidation($Subject, $MaxLength = 0) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return isset($_POST[$Subject->Name][$Subject->MaxLength]);
		}

		return !isset($Subject[$MaxLength]);
	}
	public function ValueMinLengthValidation($Subject, $MinLength = 1) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return isset($_POST[$Subject->Name][$Subject->MinLength - 1]);
		}

		return isset($Subject[$MinLength - 1]);
	}
	public function ValueIsDigitsValidation($Subject) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			return ctype_digit($_POST[$Subject->Name]);
		}

		return ctype_digit($Subject);
	}
	public function ValueIsBetweenIntegers($Subject, $MinValue, $MaxValue) {
		if($Subject instanceof \Framework\Newnorth\Control) {
			$Value = (int)$_POST[$Subject->Name];
			return $Subject->MinValue <= $Value && $Value <= $Subject->MaxValue;
		}

		$Value = (int)$Subject;
		return $MinValue <= $Value && $Value <= $MaxValue;
	}
}
?>