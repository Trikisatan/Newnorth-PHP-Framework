<?
namespace Framework\Newnorth;

class Controls implements \ArrayAccess {
	/* Instance variables */

	private $Owner;

	private $Namespace;

	private $Directory;

	private $Items = [];

	/* Magic methods */

	public function __construct($Owner, $Directory, $Namespace) {
		$this->Owner = $Owner;

		$this->Directory = $Directory;

		$this->Namespace = $Namespace;

		$this->TryLoadIniFile();
	}

	public function __toString() {
		return $this->Directory.'Controls.ini';
	}

	/* Array access methods */

	public function offsetSet($Key, $Value) {
		throw new Exception('Not allowed.');
	}

	public function offsetExists($Key) {
		return isset($this->Items[$Key]);
	}

	public function offsetUnset($Key) {
		throw new Exception('Not allowed.');
	}

	public function offsetGet($Key) {
		return $this->Items[$Key];
	}

	/* Instance methods */

	private function TryLoadIniFile() {
		$FilePath = $this->Directory.'Controls.ini';

		if(!file_exists($FilePath)) {
			return;
		}

		$Controls = ParseIniFile($FilePath);

		foreach($Controls as $Name => $Data) {
			if(!isset($Data['Class'])) {
				throw new ConfigException(
					'Class not set.',
					[
						'File' => $FilePath,
						'Control' => $Name,
					]
				);
			}

			$this->Add($Name, $Data['Class'], $Data);
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
			$ClassName = $this->Namespace.$Name.'Control';

			$Directory = './'.$this->Directory;

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