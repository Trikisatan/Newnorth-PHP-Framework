<?
namespace Framework\Newnorth;

class Action {
	/* Instance variables */

	private $Owner;

	private $Name;

	private $RealAction = null;

	private $SubActions = [];

	private $PreValidators;

	private $PreFormatters;

	private $DbLocks;

	private $Validators;

	private $Formatters;

	public $IsExecuted = false;

	/* Magic methods */

	public function __construct($Owner, $Name) {
		$this->Owner = $Owner;

		$this->Name = $Name;

		$GLOBALS['Application']->RegisterObject($this);
	}

	public function __toString() {
		return $this->Owner.'/'.$this->Name.'Action';
	}

	/* Life cycle methods */

	public function Initialize(array $Parameters) {
		if(isset($Parameters['RealAction'])) {
			$this->RealAction = $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['RealAction']);

			$this->RealAction->AddSubAction($this);
		}

		$this->Initialize_PreValidators(isset($Parameters['PreValidators']) ? $Parameters['PreValidators'] : []);

		$this->Initialize_PreFormatters(isset($Parameters['PreFormatters']) ? $Parameters['PreFormatters'] : []);

		$this->Initialize_DbLocks(isset($Parameters['DbLocks']) ? $Parameters['DbLocks'] : []);

		$this->Initialize_Validators(isset($Parameters['Validators']) ? $Parameters['Validators'] : []);

		$this->Initialize_Formatters(isset($Parameters['Formatters']) ? $Parameters['Formatters'] : []);
	}

	public function Initialize_PreValidators(array $PreValidators) {
		$this->PreValidators = $PreValidators;

		if(method_exists($this->Owner, 'InitializeAction»'.$this->Name.'»PreValidators')) {
			$this->Owner->{'InitializeAction»'.$this->Name.'»PreValidators'}($this);
		}
	}

	public function Initialize_PreFormatters(array $PreFormatters) {
		$this->PreFormatters = $PreFormatters;

		foreach($this->PreFormatters as $Name => &$Parameters) {
			if(!isset($Parameters['Method'])) {
				throw new ConfigException(
					'Pre formatter method is not set.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'PreFormatter' => $Name,
						'Parameters' => $Parameters,
					]
				);
			}

			$Parameters['Parameters'] = isset($Parameters['Parameters']) ? $Parameters['Parameters'] : [];
		}

		if(method_exists($this->Owner, 'InitializeAction»'.$this->Name.'»PreFormatters')) {
			$this->Owner->{'InitializeAction»'.$this->Name.'»PreFormatters'}($this);
		}
	}

	public function Initialize_DbLocks(array $DbLocks) {
		$this->DbLocks = $DbLocks;

		if(method_exists($this->Owner, 'InitializeAction»'.$this->Name.'»DbLocks')) {
			$this->Owner->{'InitializeAction»'.$this->Name.'»DbLocks'}($this);
		}
	}

	public function Initialize_Validators(array $Validators) {
		$this->Validators = $Validators;

		foreach($this->Validators as $Name => &$Parameters) {
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

			$Parameters['Parameters'] = isset($Parameters['Parameters']) ? $Parameters['Parameters'] : [];

			$Parameters['AllowFailures'] = isset($Parameters['AllowFailures']) ? $Parameters['AllowFailures'] === '1' : true;

			$Parameters['AbortOnFailure'] = isset($Parameters['AbortOnFailure']) ? $Parameters['AbortOnFailure'] === '1' : false;
		}

		if(method_exists($this->Owner, 'InitializeAction»'.$this->Name.'»Validators')) {
			$this->Owner->{'InitializeAction»'.$this->Name.'»Validators'}($this);
		}
	}

	public function Initialize_Formatters(array $Formatters) {
		$this->Formatters = $Formatters;

		foreach($this->Formatters as $Name => &$Parameters) {
			if(!isset($Parameters['Method'])) {
				throw new ConfigException(
					'Formatter method is not set.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'Formatter' => $Name,
						'Parameters' => $Parameters,
					]
				);
			}

			$Parameters['Parameters'] = isset($Parameters['Parameters']) ? $Parameters['Parameters'] : [];
		}

		if(method_exists($this->Owner, 'InitializeAction»'.$this->Name.'»Formatters')) {
			$this->Owner->{'InitializeAction»'.$this->Name.'»Formatters'}($this);
		}
	}

	public function Execute() {
		if($this->RealAction === null) {
			if($this->PreValidate()) {
				$this->PreFormat();

				try {
					$this->LockDbConnections();

					if($this->Validate()) {
						$this->Format();

						$this->Owner->{'Actions»'.$this->Name}();

						$this->IsExecuted = true;
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
				array_splice($this->SubActions, $I, 1);
			}
		}
	}

	public function AddPreValidator($Type, $Parameters) {
		$this->PreValidators[$Type][] = $Parameters;
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
			if(!eval('return isset('.str_replace('|', ')||isset(', $IsSet).');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsNotSet($PreValidators) {
		foreach($PreValidators as $IsNotSet) {
			if(!eval('return !isset('.str_replace('|', ')||!isset(', $IsNotSet).');')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsEmpty($PreValidators) {
		foreach($PreValidators as $IsEmpty) {
			if(!eval('return !isset('.str_replace('|', '[0])||!isset(', $IsEmpty).'[0]);')) {
				return false;
			}
		}

		return true;
	}

	private function PreValidate_IsNotEmpty($PreValidators) {
		foreach($PreValidators as $IsNotEmpty) {
			if(!eval('return isset('.str_replace('|', '[0])||isset(', $IsNotEmpty).'[0]);')) {
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

	private function PreFormat() {
		foreach($this->PreFormatters as $Name => $Parameters) {
			$Object = isset($Parameters['Object']) ? $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['Object']) : $this->Owner;

			if($Object === null) {
				throw new RuntimeException(
					'Unable to find pre formatter object.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'Validator' => $Name,
						'Object' => $Parameters['Object'],
						'Parameters' => $Parameters,
					]
				);
			}

			$Method = 'PreFormatters»'.$this->Name.'»'.$Parameters['Method'];

			if(!method_exists($Object, $Method)) {
				$Method = 'PreFormatters»'.$Parameters['Method'];
			}

			if(!method_exists($Object, $Method)) {
				throw new RuntimeException(
					'Unable to find pre formatter method.',
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

			$Object->$Method($Parameters['Parameters']);
		}

		foreach($this->SubActions as $SubAction) {
			$SubAction->PreFormat();
		}
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
			if(!$IsValid && !$Parameters['AllowFailures']) {
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

			$Method = 'Validators»'.$this->Name.'»'.$Parameters['Method'];

			if(!method_exists($Object, $Method)) {
				$Method = 'Validators»'.$Parameters['Method'];
			}

			if(!method_exists($Object, $Method)) {
				throw new RuntimeException(
					'Unable to find validator method.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'Validator' => $Name,
						'Object' => $Object->__toString(),
						'Method' => 'Validators»'.$this->Name.'»'.$Parameters['Method'],
						'Parameters' => $Parameters,
					]
				);
			}

			if(!$Object->$Method($Parameters['Parameters'])) {
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

				if($Parameters['AbortOnFailure']) {
					break;
				}
			}
		}

		foreach($this->SubActions as $SubAction) {
			$IsValid = $SubAction->Validate($IsValid);
		}

		return $IsValid;
	}

	private function Format() {
		foreach($this->Formatters as $Name => $Parameters) {
			$Object = isset($Parameters['Object']) ? $GLOBALS['Application']->GetObject($this->Owner->__toString(), $Parameters['Object']) : $this->Owner;

			if($Object === null) {
				throw new RuntimeException(
					'Unable to find formatter object.',
					[
						'Owner' => $this->Owner->__toString(),
						'Action' => $this->Name,
						'Validator' => $Name,
						'Object' => $Parameters['Object'],
						'Parameters' => $Parameters,
					]
				);
			}

			$Method = 'Formatters»'.$this->Name.'»'.$Parameters['Method'];

			if(!method_exists($Object, $Method)) {
				$Method = 'Formatters»'.$Parameters['Method'];
			}

			if(!method_exists($Object, $Method)) {
				throw new RuntimeException(
					'Unable to find formatter method.',
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

			$Object->$Method($Parameters['Parameters']);
		}

		foreach($this->SubActions as $SubAction) {
			$SubAction->Format();
		}
	}
}
?>