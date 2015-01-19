<?
namespace Framework\Newnorth;

class Action {
	/* Variables */

	private $Owner;

	private $Directory;

	private $Name;

	private $RequiredVariables;

	private $RequiredValues;

	private $AutoFill;

	private $DbConnections = [];

	private $Validators = [];

	public $ErrorMessages = [];

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
		$FilePath = $this->Directory.$this->Name.'Action.ini';

		$Data = ParseIniFile($FilePath, false);

		if(isset($Data['DbConnections'])) {
			$this->Load_DbConnections($Data['DbConnections']);
		}

		if(isset($Data['Validators'])) {
			$this->Load_Validators($Data['Validators']);
		}
	}

	private function Load_DbConnections($DbConnections) {
		foreach($DbConnections as $DbConnection => $Sources) {
			$this->DbConnections[] = [
				'DbConnection' => GetDbConnection($DbConnection),
				'Sources' => $Sources,
			];
		}
	}

	private function Load_Validators($Validators) {
		foreach($Validators as $Name => $Validator) {
			$this->Validators[] = [
				'Control' => isset($Validator['Control']) ? $Validator['Control'] : null,
				'Method' => (isset($Validator['Method']) ? $Validator['Method'] : $Name).'Validator',
				'ErrorMessage' => isset($Validator['ErrorMessage']) ? $Validator['ErrorMessage'] : null,
				'AbortOnPreviousFailures' => isset($Validator['AbortOnPreviousFailures']) ? $Validator['AbortOnPreviousFailures'] : false,
				'AbortOnFailure' => isset($Validator['AbortOnFailure']) ? $Validator['AbortOnFailure'] : false,
			];
		}
	}

	public function ValidateRequiredVariables() {
		foreach($this->RequiredVariables as $Variable => $ShouldExist) {
			$VariableParts = explode('/', $Variable);

			$ShouldExist = ($ShouldExist === '1');

			if($VariableParts[0] === '_FILES') {
				if(isset($_FILES[$VariableParts[1]]) !== $ShouldExist) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_GET') {
				if(isset($_GET[$VariableParts[1]]) !== $ShouldExist) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_POST') {
				if(isset($_POST[$VariableParts[1]]) !== $ShouldExist) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_SESSION') {
				if(isset($_SESSION[$VariableParts[1]]) !== $ShouldExist) {
					return false;
				}

				continue;
			}

			throw new ConfigException(
				'Unknown variable provided in validation of required variables.',
				[
					'Action' => $this->__toString(),
					'Variable' => $Variable,
				]
			);
		}

		return true;
	}

	public function ValidateRequiredValues() {
		foreach($this->RequiredValues as $Variable => $Value) {
			$VariableParts = explode('/', $Variable);

			if($VariableParts[0] === '_FILES') {
				if($_FILES[$VariableParts[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_GET') {
				if($_GET[$VariableParts[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_POST') {
				if($_POST[$VariableParts[1]] !== $Value) {
					return false;
				}

				continue;
			}

			if($VariableParts[0] === '_SESSION') {
				if($_SESSION[$VariableParts[1]] !== $Value) {
					return false;
				}

				continue;
			}

			throw new ConfigException(
				'Unknown variable provided in validation of required values.',
				[
					'Action' => $this->__toString(),
					'Variable' => $Variable,
				]
			);
		}

		return true;
	}

	public function AutoFill() {
		foreach($this->AutoFill as $Control => $Variable) {
			$Control = $this->Owner->GetControl($Control);

			$VariableParts = explode('/', $Variable);

			if($VariableParts[0] === '_GET') {
				if(isset($_GET[$VariableParts[1]])) {
					$Control->AutoFill($_GET[$VariableParts[1]]);
				}

				continue;
			}

			if($VariableParts[0] === '_POST') {
				if(isset($_POST[$VariableParts[1]])) {
					$Control->AutoFill($_POST[$VariableParts[1]]);
				}

				continue;
			}

			if($VariableParts[0] === '_SESSION') {
				if(isset($_SESSION[$VariableParts[1]])) {
					$Control->AutoFill($_SESSION[$VariableParts[1]]);
				}

				continue;
			}

			throw new ConfigException(
				'Unknown variable provided in auto fill.',
				[
					'Action' => $this->__toString(),
					'Variable' => $Variable,
				]
			);
		}
	}

	public function LockDbConnections() {
		for($I = 0, $IC = count($this->DbConnections); $I < $IC; ++$I) {
			$this->DbConnections[$I]['DbConnection']->Lock($this->DbConnections[$I]['Sources']);
		}
	}

	public function UnlockDbConnections() {
		for($I = 0, $IC = count($this->DbConnections); $I < $IC; ++$I) {
			$this->DbConnections[$I]['DbConnection']->Unlock($this->DbConnections[$I]['Sources']);
		}
	}

	public function Validate() {
		$isValid = true;

		foreach($this->Validators as $Validator) {
			if($Validator['AbortOnPreviousFailures'] && !$isValid) {
				return false;
			}

			if(!$this->Owner->GetValidatorMethod($this->Name, $Validator['Method'], $MethodObject)) {
				throw new ConfigException(
					'Unable to find validator method.',
					[
						'Object' => $this->Owner->__toString(),
						'Method' => $Validator['Method'],
					]
				);
			}

			if($Validator['Control'] === null) {
				if($MethodObject->$Validator['Method'](null)) {
					continue;
				}

				if($Validator['ErrorMessage'] !== null) {
					$this->ErrorMessages[] = $Validator['ErrorMessage'];
				}
			}
			else {
				$Control = $this->Owner->GetControl($Validator['Control']);

				if($MethodObject->$Validator['Method']($Control)) {
					continue;
				}

				if($Validator['ErrorMessage'] !== null) {
					if(!isset($Control->_Parameters['ErrorMessages'])) {
						$Control->_Parameters['ErrorMessages'] = [$Validator['ErrorMessage']];
					}
					else {
						$Control->_Parameters['ErrorMessages'][] = $Validator['ErrorMessage'];
					}
				}
			}

			if($Validator['AbortOnFailure']) {
				return false;
			}

			$isValid = false;
		}

		return $isValid;
	}

	public function Execute() {
		return $this->Owner->{$this->Name.'Action'}();
	}
}
?>