<?
namespace Framework\Newnorth;

class Controls {
	/* Instance variables */

	private $Owner;

	private $FilePath;

	public $Items = [];

	/* Magic methods */

	public function __construct($Owner) {
		$this->Owner = $Owner;

		$this->FilePath = $this->Owner->_Directory.$this->Owner->_Name.'.php.Controls.ini';

		$this->TryLoadIniFile();
	}

	public function __toString() {
		return $this->FilePath;
	}

	/* Instance methods */

	private function TryLoadIniFile() {
		if(file_exists($this->FilePath)) {
			$Controls = ParseIniFile($this->FilePath);

			foreach($Controls as $Alias => $Parameters) {
				$this->Add($Alias, $Parameters);
			}
		}
	}

	public function Destroy() {
		foreach($this->Items as $Item) {
			$Item->Destroy();
		}
	}

	public function Add($Alias, $Parameters = []) {
		if(!isset($Parameters['Class'])) {
			throw new RuntimeException(
				'Control class not set.',
				[
					'File path' => $this->FilePath,
					'Alias' => $Alias,
					'Parameters' => $Parameters,
				]
			);
		}
		else {
			$Control = Control::Instantiate(
				$this->Owner,
				$Parameters['Class'],
				$Alias,
				$Parameters
			);

			$this->Items[$Alias] = $Control;

			if(LIFECYCLESTAGE_PREINITIALIZE < $GLOBALS['Application']->Stage) {
				$Control->PreInitialize();
			}

			if(LIFECYCLESTAGE_INITIALIZE < $GLOBALS['Application']->Stage) {
				$Control->Initialize();
			}

			if(LIFECYCLESTAGE_POSTINITIALIZE < $GLOBALS['Application']->Stage) {
				$Control->PostInitialize();
			}

			if(LIFECYCLESTAGE_PRELOAD < $GLOBALS['Application']->Stage) {
				$Control->PreLoad();
			}

			if(LIFECYCLESTAGE_LOAD < $GLOBALS['Application']->Stage) {
				$Control->Load();
			}

			if(LIFECYCLESTAGE_POSTLOAD < $GLOBALS['Application']->Stage) {
				$Control->PostLoad();
			}

			if(LIFECYCLESTAGE_PREEXECUTE < $GLOBALS['Application']->Stage) {
				$Control->PreExecute();
			}

			if(LIFECYCLESTAGE_EXECUTE < $GLOBALS['Application']->Stage) {
				$Control->Execute();
			}

			if(LIFECYCLESTAGE_POSTEXECUTE < $GLOBALS['Application']->Stage) {
				$Control->PostExecute();
			}

			return $Control;
		}
	}

	public function PreInitialize() {
		foreach($this->Items as $Control) {
			$Control->PreInitialize();
		}
	}

	public function Initialize() {
		foreach($this->Items as $Control) {
			$Control->Initialize();
		}
	}

	public function PostInitialize() {
		foreach($this->Items as $Control) {
			$Control->PostInitialize();
		}
	}

	public function PreLoad() {
		foreach($this->Items as $Control) {
			$Control->PreLoad();
		}
	}

	public function Load() {
		foreach($this->Items as $Control) {
			$Control->Load();
		}
	}

	public function PostLoad() {
		foreach($this->Items as $Control) {
			$Control->PostLoad();
		}
	}

	public function PreExecute() {
		foreach($this->Items as $Control) {
			$Control->PreExecute();
		}
	}

	public function Execute() {
		foreach($this->Items as $Control) {
			$Control->Execute();
		}
	}

	public function PostExecute() {
		foreach($this->Items as $Control) {
			$Control->PostExecute();
		}
	}
}
?>