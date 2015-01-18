<?
namespace Framework\Newnorth;

class Controls implements \ArrayAccess {
	/* Variables */
	private $Owner;
	private $Directory;
	private $Items = array();

	/* Magic methods */
	public function __construct($Owner, $Directory) {
		$this->Owner = $Owner;
		$this->Directory = $Directory;

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

	/* Methods */

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

	public function Add($Alias, $Name, $Data = array()) {
		if($Name[0] === '/') {
			$ClassName = str_replace('/', '\\', $Name).'Control';
			$Directory = '.'.substr($Name, 0, strrpos($Name, '/') + 1);
			$Name = substr($Name, strrpos($Name, '/') + 1).'Control';
		}
		else {
			$ClassName = str_replace('/', '\\', $this->Directory.$Name).'Control';
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

		$Control = new $ClassName($this->Owner, $Directory, $Name, $Data);

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