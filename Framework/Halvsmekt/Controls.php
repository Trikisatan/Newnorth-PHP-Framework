<?php
namespace Framework\Halvsmekt;

class Controls implements \ArrayAccess {
	/* Variables */
	private $Directory;
	private $Items;

	/* Magic methods */
	public function __construct($Directory) {
		$this->Directory = $Directory;
	}
	public function __toString() {
		return $this->Directory;
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
	public function Load() {
		$FilePath = 'Application/'.$this->Directory.'Controls.ini';
		$Items = ParseIniFile($FilePath);

		if($Items === false) {
			return;
		}

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
	public function Add($Alias, $Name, $Data = array()) {
		if($Name[0] === '/') {
			$Path = $Name.'Control.php';
			$Class = str_replace('/', '\\', $Name).'Control';
			$Directory = substr($Name, 1, strrpos($Name, '/'));
			$Name = substr($Name, strrpos($Name, '/') + 1).'Control';
		}
		else {
			$Path = 'Application/'.$this->Directory.$Name.'Control.php';
			$Class = str_replace('/', '\\', $this->Directory.$Name).'Control';
			$Directory = $this->Directory;
			$Name = $Name.'Control';
		}

		if(!class_exists($Class, false)) {
			@include($Path);

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

		$Control = new $Class($this, $Directory, $Name);

		foreach($Data as $Key => $Value) {
			$Control->$Key = $Value;
		}

		return $this->Items[$Alias] = $Control;
	}
}
?>