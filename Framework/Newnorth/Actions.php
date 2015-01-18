<?
namespace Framework\Newnorth;

class Actions implements \ArrayAccess {
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
		return $this->Directory.'Actions.ini';
	}

	/* Array access methods */

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

	/* Methods */

	private function TryLoadIniFile() {
		$FilePath = $this->Directory.'Actions.ini';

		if(!file_exists($FilePath)) {
			return;
		}

		$Items = ParseIniFile($FilePath);

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

			if(!$Action->Load()) {
				continue;
			}

			$Action->LockConnections();

			if(!$Action->Validate()) {
				$Action->UnlockConnections();

				continue;
			}

			$Redirect = $Action->Execute();

			$Action->UnlockConnections();

			if(isset($Redirect)) {
				header('Location: '.$Redirect);

				exit();
			}
		}
	}
}
?>