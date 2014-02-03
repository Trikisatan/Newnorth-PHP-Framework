<?php
namespace Framework\Halvsmekt;

class Translations implements \ArrayAccess {
	/* Variables */
	private $Directory;
	private $Items = array();

	/* Magic methods */
	public function __construct($Directory) {
		$this->Directory = $Directory;
	}
	public function __toString() {
		return $this->Directory;
	}

	/* Methods */
    public function offsetSet($Key, $Value) {
		if(is_array($Value)) {
			$Translations = $Value;

			foreach($Translations as $SubKey => $Value) {
				$this[$Key.'_'.$SubKey] = $Value;
			}
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
	public function Load() {
		$FilePath = 'Application/'.$this->Directory.'/Translations.'.Application::GetLocale().'.ini';
		$Translations = ParseIniFile($FilePath);

		if(!$Translations) {
			return;
		}

		foreach($Translations as $Key => $Value) {
			$this[$Key] = $Value;
		}
	}
	public function Translate(&$Contents) {
		foreach($this->Items as $Key => $Translation) {
			$Contents = str_replace('%'.$Key.'%', $Translation, $Contents);
			
			if(0 < preg_match_all('/%'.$Key.'\("(.*?)"\)%/', $Contents, $Matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
				foreach($Matches as $Match) {
					$Copy = $Translation;

					$Data = explode('","', $Match[1][0]);

					for($I = 0; $I < count($Data); ++$I) {
						$Copy = str_replace('\\'.$I, $Data[$I], $Copy);
					}

					$Contents = substr($Contents, 0, $Match[0][1]).$Copy.substr($Contents, $Match[0][1] + strlen($Match[0][0]));
				}
			}
		}
	}
}
?>