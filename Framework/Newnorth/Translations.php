<?
namespace Framework\Newnorth;

class Translations implements \ArrayAccess {
	/* Variables */

	private $Directory;

	private $Items = array();

	/* Magic methods */

	public function __construct($Directory) {
		$this->Directory = $Directory;

		$this->TryLoadIniFile();
	}

	public function __toString() {
		return $this->Directory.'Translations.'.Application::GetLocale().'.ini';
	}

	/* Array access methods */

	public function offsetSet($Key, $Value) {
		if(is_array($Value)) {
			$Translations = $Value;

			foreach($Translations as $SubKey => $Value) {
				$this[$Key.'_'.$SubKey] = $Value;
			}
		}
		else if($Value === null) {
			unset($this->Items[$Key]);
		}
		else {
			$this->Items[$Key] = $Value;
		}
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
		$FilePath = $this->Directory.'Translations.'.Application::GetLocale().'.ini';

		if(!file_exists($FilePath)) {
			return;
		}

		$Translations = ParseIniFile($FilePath);

		foreach($Translations as $Key => $Value) {
			$this[$Key] = $Value;
		}
	}

	public function Translate(&$Contents) {
		$Offset = 0;

		while(0 < preg_match('/%([a-zA-Z0-9_\/]+?)%/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
			$Key = $Match[1][0];

			if(isset($this->Items[$Key])) {
				$Translation = $this->Items[$Key];

				$Contents = substr($Contents, 0, $Match[0][1]).$Translation.substr($Contents, $Match[0][1] + strlen($Match[0][0]));

				$Offset = $Match[0][1];
			}
			else {
				$Offset = $Match[0][1] + strlen($Match[0][0]);
			}
		}

		$Offset = 0;

		while(0 < preg_match('/%([a-zA-Z0-9_\/]+?)\("(.*?)"\)%/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
			$Key = $Match[1][0];

			if(isset($this->Items[$Key])) {
				$Translation = $this->Items[$Key];

				$Data = explode('","', $Match[2][0]);

				for($I = 0; $I < count($Data); ++$I) {
					$Translation = str_replace('\\'.$I, $Data[$I], $Translation);
				}

				$Contents = substr($Contents, 0, $Match[0][1]).$Translation.substr($Contents, $Match[0][1] + strlen($Match[0][0]));

				$Offset = $Match[0][1];
			}
			else {
				$Offset = $Match[0][1] + strlen($Match[0][0]);
			}
		}
	}
}
?>