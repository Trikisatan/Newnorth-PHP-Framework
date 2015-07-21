<?
namespace Framework\Newnorth;

class Action {
	/* Instance variables */

	private $Owner;

	private $Name;

	private $RealAction = null;

	private $SubActions = [];

	private $PreValidators = [];

	private $DbLocks = [];

	private $Validators = [];

	private $PostValidators = [];

	public $IsExecuted = false;

	/* Magic methods */

	public function __construct($Owner, $Name, $Parameters) {
		$this->Owner = $Owner;

		$this->Name = $Name.'Action';

		if(isset($Parameters['RealAction'])) {
			$this->RealAction = $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['RealAction']);

			$this->RealAction->AddSubAction($this);
		}

		$this->PreValidators = isset($Parameters['PreValidators']) ? $Parameters['PreValidators'] : $this->PreValidators;

		$this->DbLocks = isset($Parameters['DbLocks']) ? $Parameters['DbLocks'] : $this->DbLocks;

		$this->Validators = isset($Parameters['Validators']) ? $Parameters['Validators'] : $this->Validators;

		foreach($this->Validators as $Name => $Parameters) {
			if(!isset($Parameters['Method'])) {
				throw new ConfigException(
					'Validator method is not set.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'Validator' => $Name,
						'Parameters' => $Parameters,
					]
				);
			}
		}

		$GLOBALS['Application']->RegisterObject($this);
	}

	public function __toString() {
		return $this->Owner.'/'.$this->Name;
	}

	/* Life cycle methods */

	public function Execute() {
		if($this->RealAction === null) {
			if($this->PreValidate()) {
				try {
					$this->LockDbConnections();

					if($this->Validate()) {
						$this->Owner->{$this->Name}();
					}
				}
				catch(\Exception $Exception) {
					throw $Exception;
				}
				finally {
					$this->UnlockDbConnections();

					$this->IsExecuted = true;
				}
			}
		}
	}

	/* Instance methods */

	public function Destroy() {
		if($this->RealAction !== null) {
			$this->RealAction->RemoveSubAction($this);
		}

		foreach($this->SubActions as $SubAction) {
			$SubAction->Owner->_RemoveAction($this);
		}

		$GLOBALS['Application']->UnregisterObject($this);
	}

	public function AddSubAction($SubAction) {
		$this->SubActions[] = $SubAction;
	}

	public function RemoveSubAction($SubAction) {
		for($I = 0; $I < count($this->SubActions); ++$I) {
			if($this->SubActions[$I] === $SubAction) {
				array_splice($this->SubActions, $I);
			}
		}
	}

	private function PreValidate() {
		foreach($this->PreValidators as $Type => $PreValidators) {
			if(!$this->{'PreValidate_'.$Type}($PreValidators)) {
				return false;
			}
		}

		foreach($this->SubActions as $SubAction) {
			if(!$SubAction->PreValidate()) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_ActionIsExecuted($PreValidators) {
		foreach($PreValidators as $Action) {
			$Action = $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Action);

			if($Action === null) {
				return false;
			}
			else if(!$Action->IsExecuted) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsSet($PreValidators) {
		foreach($PreValidators as $IsSet) {
			if(!eval('return isset('.$IsSet.');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsNotSet($PreValidators) {
		foreach($PreValidators as $IsNotSet) {
			if(eval('return isset('.$IsNotSet.');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsEmpty($PreValidators) {
		foreach($PreValidators as $IsEmpty) {
			if(eval('return isset('.$IsEmpty.'[0]);')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsNotEmpty($PreValidators) {
		foreach($PreValidators as $IsNotEmpty) {
			if(!eval('return isset('.$IsNotEmpty.'[0]);')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsTrue($PreValidators) {
		foreach($PreValidators as $IsTrue) {
			if(!eval('return '.$IsTrue.';')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsFalse($PreValidators) {
		foreach($PreValidators as $IsFalse) {
			if(eval('return '.$IsFalse.';')) {
				return false;
			}
		}

		return true;
	}

	private function LockDbConnections() {
		foreach($this->DbLocks as $DbConnection => $Sources) {
			$GLOBALS['Application']->GetDbConnection($DbConnection)->Lock($Sources);
		}

		foreach($this->SubActions as $SubAction) {
			$SubAction->LockDbConnections();
		}
	}

	private function UnlockDbConnections() {
		if(is_array($this->DbLocks)) {
			foreach($this->DbLocks as $DbConnection => $Sources) {
				$GLOBALS['Application']->GetDbConnection($DbConnection)->Unlock($Sources);
			}
		}

		foreach($this->SubActions as $SubAction) {
			$SubAction->UnlockDbConnections();
		}
	}

	private function Validate($IsValid = true) {
		foreach($this->Validators as $Name => $Parameters) {
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

		foreach($this->SubActions as $SubAction) {
			$IsValid = $SubAction->Validate($IsValid);
		}

		return $IsValid;
	}
}
?>