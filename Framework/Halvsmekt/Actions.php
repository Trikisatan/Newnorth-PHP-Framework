<?php
namespace Framework\Halvsmekt;

class Actions implements \ArrayAccess {
	/* Variables */
	private $Owner;
	private $Directory;
	private $Items = array();

	/* Magic methods */
	public function __construct($Owner, $Directory) {
		$this->Owner = $Owner;
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
        return isset($this->Actions[$Key]);
    }
    public function offsetUnset($Key) {
		throw new Exception('Not allowed.');
    }
    public function offsetGet($Key) {
        return $this->Actions[$Key];
    }
	public function Load() {
		$FilePath = $this->Directory.'Actions.ini';
		$Items = ParseIniFile($FilePath);

		if($Items === false) {
			return;
		}

		foreach($Items as $Name => $Data) {
			$this->Items[$Name] = new Action(
				$this->Owner,
				$this->Directory,
				$Name,
				$Data
			);
		}
	}
	public function Execute() {
		foreach($this->Items as $Action) {
			if(!$Action->ValidateRequiredVariables()) {
				continue;
			}

			if(!$Action->ValidateRequiredValues()) {
				continue;
			}

			$Action->AutoFill();
			$Action->Load();
			$Action->LockMySqlTables();

			if(!$Action->Validate()) {
				$Action->UnlockMySqlTables();
				continue;
			}

			$Redirect = $Action->Execute();
			$Action->UnlockMySqlTables();

			if(isset($Redirect)) {
				header('Location: '.$Redirect);
				exit();
			}
		}
	}
}
?>