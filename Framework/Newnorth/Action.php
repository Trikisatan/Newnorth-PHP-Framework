<?php
namespace Framework\Newnorth;

class Action {
	/* Variables */
	private $Owner;
	private $Directory;
	private $Name;
	private $RequiredVariables;
	private $RequiredValues;
	private $AutoFill;
	private $Connections;
	private $Validation;
	public $ErrorMessages = array();

	/* Magic methods */
	public function __construct($Owner, $Directory, $Name, $Data) {
		$this->Owner = $Owner;
		$this->Directory = $Directory;
		$this->Name = $Name;
		$this->RequiredVariables = isset($Data['RequiredVariables']) ? $Data['RequiredVariables'] : array();
		$this->RequiredValues = isset($Data['RequiredValues']) ? $Data['RequiredValues'] : array();
		$this->AutoFill = isset($Data['AutoFill']) ? $Data['AutoFill'] : array();
	}
	public function __toString() {
		return $this->Directory.$this->Name;
	}

	/* Methods */
	public function Load() {
		$FilePath = 'Application/'.$this->Directory.'/'.$this->Name.'Action.ini';
		$Data = ParseIniFile($FilePath, false);

		if($Data === false) {
			return false;
		}

		$this->Connections = isset($Data['Connections']) ? $Data['Connections'] : array();
		$this->Validation = isset($Data['Validation']) ? $Data['Validation'] : array();
		return true;
	}
	public function ValidateRequiredVariables() {
		foreach($this->RequiredVariables as $Variable => $IsRequired) {
			$Variable = explode('/', $Variable);
			$IsRequired = ($IsRequired === '1');

			if($Variable[0] === '_FILES') {
				if(isset($_FILES[$Variable[1]]) !== $IsRequired) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_GET') {
				if(isset($_GET[$Variable[1]]) !== $IsRequired) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_POST') {
				if(isset($_POST[$Variable[1]]) !== $IsRequired) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_SESSION') {
				if(isset($_SESSION[$Variable[1]]) !== $IsRequired) {
					return false;
				}

				continue;
			}

			ConfigError(
				'Unknown variable provided in validation of required variables.',
				array(
					'Action' => $this->Directory.$this->Name.'Action',
					'Variable' => $Variable,
				)
			);
		}

		return true;
	}
	public function ValidateRequiredValues() {
		foreach($this->RequiredValues as $Variable => $Value) {
			$Variable = explode('/', $Variable);

			if($Variable[0] === '_FILES') {
				if($_FILES[$Variable[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_GET') {
				if($_GET[$Variable[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_POST') {
				if($_POST[$Variable[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($Variable[0] === '_SESSION') {
				if($_SESSION[$Variable[1]] !== $Value) {
					return false;
				}

				continue;
			}

			ConfigError(
				'Unknown variable provided in validation of required values.',
				array(
					'Action' => $this->Directory.$this->Name.'Action',
					'Variable' => $Variable,
				)
			);
		}

		return true;
	}
	public function AutoFill() {
		foreach($this->AutoFill as $Control => $Variable) {
			$Control = $this->Owner->GetControl($Control);
			$Variable = explode('/', $Variable);

			if($Variable[0] === '_GET') {
				$Control->AutoFill(isset($_GET[$Variable[1]]) ? $_GET[$Variable[1]] : null);
				continue;
			}

			if($Variable[0] === '_POST') {
				$Control->AutoFill(isset($_POST[$Variable[1]]) ? $_POST[$Variable[1]] : null);
				continue;
			}

			if($Variable[0] === '_SESSION') {
				$Control->AutoFill(isset($_SESSION[$Variable[1]]) ? $_SESSION[$Variable[1]] : null);
				continue;
			}

			ConfigError(
				'Unknown variable provided in auto fill.',
				array(
					'Action' => $this->Directory.$this->Name.'Action',
					'Variable' => $Variable,
				)
			);
		}
	}
	public function LockConnections() {
		foreach($this->Connections as $Connection => $Items) {
			if(0 < count($Items)) {
				GetConnection($Connection)->Lock($Items);
			}
		}
	}
	public function UnlockConnections() {
		foreach($this->Connections as $Connection => $Items) {
			if(0 < count($Items)) {
				GetConnection($Connection)->Unlock($Items);
			}
		}
	}
	public function Validate() {
		$HasErrorOccurred = false;

		foreach($this->Validation as $Name => $Validation) {
			if($HasErrorOccurred && $Validation['AbortOnPreviousFailures'] === '1') {
				return false;
			}

			$Method = $this->GetValidationMethod($Name);

			if(isset($Validation['Control'])) {
				$Control = $this->Owner->GetControl($Validation['Control']);

				if($this->Owner->$Method($Control)) {
					continue;
				}

				if(isset($Validation['ErrorMessage'])) {
					$Control->ErrorMessages[] = $Validation['ErrorMessage'];
				}
			}
			else {
				if($this->Owner->$Method(null)) {
					continue;
				}

				if(isset($Validation['ErrorMessage'])) {
					$this->ErrorMessages[] = $Validation['ErrorMessage'];
				}
			}

			if($Validation['AbortOnFailure'] === '1') {
				return false;
			}

			$HasErrorOccurred = true;
		}

		return !$HasErrorOccurred;
	}
	private function GetValidationMethod($Name) {
		$Method = $this->Name.'Action_'.$Name.'Validation';

		if(!method_exists($this->Owner, $Method)) {
			$Method = $Name.'Validation';

			if(!method_exists($this->Owner, $Method)) {
				$Method = strrpos($Name, '_');

				if($Method === false) {
					ConfigError(
						'Validation method not found.',
						array(
							'Action' => $this->Directory.$this->Name,
							'Name' => $Name,
						)
					);
				}

				$Method = substr($Name, $Method + 1).'Validation';

				if(!method_exists($this->Owner, $Method)) {
					ConfigError(
						'Validation method not found.',
						array(
							'Action' => $this->Directory.$this->Name,
							'Method' => $Method,
							'Name' => $Name,
						)
					);
				}
			}
		}

		return $Method;
	}
}
?>