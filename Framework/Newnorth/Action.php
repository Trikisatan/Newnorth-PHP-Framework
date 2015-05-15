<?
namespace Framework\Newnorth;

class Action {
	/* Instance variables */

	private $Owner;

	private $Name;

	private $PreValidators = null;

	private $DbLocks = null;

	private $Validators = null;

	/* Magic methods */

	public function __construct($Owner, $Name, $Parameters) {
		$this->Owner = $Owner;

		$this->Name = $Name;

		$this->PreValidators = isset($Parameters['PreValidators']) ? $Parameters['PreValidators'] : $this->PreValidators;

		$this->DbLocks = isset($Parameters['DbLocks']) ? $Parameters['DbLocks'] : $this->DbLocks;

		$this->Validators = isset($Parameters['Validators']) ? $Parameters['Validators'] : $this->Validators;
	}

	/* Life cycle methods */

	public function Execute() {
		if($this->PreValidate()) {
			try {
				$this->LockDbConnections();

				if($this->Validate()) {
					$this->Owner->{$this->Name.'Action'}();
				}
			}
			catch(\Exception $Exception) {
				throw $Exception;
			}
			finally {
				$this->UnlockDbConnections();
			}
		}
	}

	/* Instance methods */

	private function PreValidate() {
		if(is_array($this->PreValidators)) {
			if(isset($this->PreValidators['IsSet']) && is_array($this->PreValidators['IsSet']) && !$this->PreValidate_IsSet()) {
				return false;
			}
			else if(isset($this->PreValidators['IsNotSet']) && is_array($this->PreValidators['IsNotSet']) && !$this->PreValidate_IsNotSet()) {
				return false;
			}
			else if(isset($this->PreValidators['IsTrue']) && is_array($this->PreValidators['IsTrue']) && !$this->PreValidate_IsTrue()) {
				return false;
			}
			else if(isset($this->PreValidators['IsFalse']) && is_array($this->PreValidators['IsFalse']) && !$this->PreValidate_IsFalse()) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return true;
		}
	}

	private function PreValidate_IsSet() {
		foreach($this->PreValidators['IsSet'] as $IsSet) {
			if(!eval('return isset('.$IsSet.');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsNotSet() {
		foreach($this->PreValidators['IsNotSet'] as $IsNotSet) {
			if(eval('return isset('.$IsNotSet.');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsTrue() {
		foreach($this->PreValidators['IsTrue'] as $IsTrue) {
			if(!eval('return '.$IsTrue.';')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsFalse() {
		foreach($this->PreValidators['IsFalse'] as $IsFalse) {
			if(eval('return '.$IsFalse.';')) {
				return false;
			}
		}

		return true;
	}

	private function LockDbConnections() {
		if(is_array($this->DbLocks)) {
			foreach($this->DbLocks as $DbConnection => $Sources) {
				$GLOBALS['Application']->GetDbConnection($DbConnection)->Lock($Sources);
			}
		}
	}

	private function UnlockDbConnections() {
		if(is_array($this->DbLocks)) {
			foreach($this->DbLocks as $DbConnection => $Sources) {
				$GLOBALS['Application']->GetDbConnection($DbConnection)->Unlock($Sources);
			}
		}
	}

	private function Validate() {
		if(is_array($this->Validators)) {
			$IsValid = true;

			foreach($this->Validators as $Name => $Parameters) {
				if(!isset($Parameters['Method'])) {
					throw new RuntimeException(
						'Validator method is not set.',
						[
							'Owner' => $this->Owner->__toString(),
							'Action' => $this->Name,
							'Validator' => $Name,
							'Parameters' => $Parameters,
						]
					);
				}

				if(!$IsValid && (!isset($Parameters['AllowFailures']) || !$Parameters['AllowFailures'])) {
					break;
				}

				$Object = isset($Parameters['Object']) ? $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['Object']) : $this->Owner;

				if($Object === null) {
					throw new RuntimeException(
						'Unable to find validator object.',
						[
							'Owner' => $this->Owner->__toString(),
							'Action' => $this->Name,
							'Validator' => $Name,
							'Object' => $Parameters['Object'],
							'Parameters' => $Parameters,
						]
					);
				}

				$Method = $Parameters['Method'].'Validator';

				if(!method_exists($Object, $Method)) {
					throw new RuntimeException(
						'Unable to find validator method.',
						[
							'Owner' => $this->Owner->__toString(),
							'Action' => $this->Name,
							'Validator' => $Name,
							'Object' => $Object->__toString(),
							'Method' => $Method,
							'Parameters' => $Parameters,
						]
					);
				}

				if(!$Object->$Method(isset($Parameters['Parameters']) ? $Parameters['Parameters'] : null)) {
					$IsValid = false;

					if(isset($Parameters['ErrorMessage'])) {
						$SupervisorObject = isset($Parameters['SupervisorObject']) ? $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['SupervisorObject']) : $Object;

						if($SupervisorObject === null) {
							throw new RuntimeException(
								'Unable to find validator supervisor object.',
								[
									'Owner' => $this->Owner->__toString(),
									'Action' => $this->Name,
									'Validator' => $Name,
									'SupervisorObject' => $Parameters['SupervisorObject'],
									'Parameters' => $Parameters,
								]
							);
						}

						$SupervisorObject->_ErrorMessages[] = $Parameters['ErrorMessage'];
					}

					if(!isset($Parameters['AbortOnFailure']) || $Parameters['AbortOnFailure']) {
						break;
					}
				}
			}

			return $IsValid;
		}
		else {
			return true;
		}
	}
}
?>