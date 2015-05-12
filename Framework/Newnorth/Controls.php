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
					$this->Items[$Alias] = Control::Instantiate($this->Owner, $Parameters['Class'], $Alias, $Parameters);
				}
			}
		}
	}

	public function Add($Alias, $Name, $Data = []) {
		if($Name[0] === '.') {
			$ClassName = str_replace('/', '\\', trim($Name, "./")).'Control';

			$Directory = substr($Name, 0, strrpos($Name, '/') + 1);

			$Name = substr($Name, strrpos($Name, '/') + 1).'Control';
		}
		else if($Name[0] === '/') {
			$ClassName = str_replace('/', '\\', $Name).'Control';

			$Directory = '/'.$GLOBALS['Config']->Files['Controls'].substr($Name, 1, strrpos($Name, '/'));

			$Name = substr($Name, strrpos($Name, '/') + 1).'Control';
		}
		else {
			$ClassName = $this->Owner->_Namespace.$this->Owner->_Name.'\\'.$Name.'Control';

			$Directory = './'.$this->Owner->_Directory.$this->Owner->_Name.'/';

			$Name = $Name.'Control';
		}

		if(!class_exists($ClassName, false)) {
			$FilePath = $Directory.$Name.'.php';

			include($FilePath);

			if(!class_exists($ClassName, false)) {
				throw new ConfigException(
					'Control not found.',
					[
						'File' => $FilePath,
						'Class' => $ClassName,
					]
				);
			}
		}

		$Control = new $ClassName($this->Owner, $Directory, $this->Namespace, $Name, $Alias, $Data);

		return $this->Items[$Alias] = $Control;
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