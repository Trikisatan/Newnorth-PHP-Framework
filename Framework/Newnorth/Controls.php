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

		$FilePath = $this->Directory.'Controls.ini';
		$Items = ParseIniFile($FilePath);

		if($Items !== false) {
			foreach($Items as $Name => $Data) {
				if(!isset($Data['Class'])) {
					ConfigError(
						'Control\'s class not set.',
						array(
							'File' => $FilePath,
							'Name' => $Name,
						)
					);
				}

				$this->Add($Name, $Data['Class'], $Data);
			}
		}
	}
	public function __toString() {
		return $this->Directory.'Controls.ini';
	}

	/* Methods */
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
	public function Add($Alias, $Name, $Data = array()) {
		if($Name[0] === '/') {
			$Class = str_replace('/', '\\', $Name).'Control';
			$Directory = '.'.substr($Name, 0, strrpos($Name, '/') + 1);
			$Name = substr($Name, strrpos($Name, '/') + 1).'Control';
		}
		else {
			$Class = str_replace('/', '\\', $this->Directory.$Name).'Control';
			$Directory = './'.$this->Directory;
			$Name = $Name.'Control';
		}

		if(!class_exists($Class, false)) {
			$Path = $Directory.$Name.'.php';

			include($Path);

			if(!class_exists($Class, false)) {
				ConfigError(
					'Unable to load control.',
					array(
						'Path' => $Path,
						'Class' => $Class,
					)
				);
			}
		}

		$Control = new $Class($this->Owner, $Directory, $Name);

		foreach($Data as $Key => $Value) {
			$Control->$Key = $Value;
		}

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