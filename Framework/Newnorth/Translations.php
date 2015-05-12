<?
namespace Framework\Newnorth;

class Translations {
	/* Instance variables */

	public $FilePath;

	public $Items = [];

	/* Magic methods */

	public function __construct($FilePath = 'Translations.ini') {
		$this->FilePath = $FilePath;
	}

	/* Instance methods */

	public function Initialize() {
		if(file_exists($this->FilePath)) {
			$this->Items = ParseIniFile($this->FilePath, false, true);
		}
	}

	public function Translate(&$Contents) {
		$Offset = 0;

		while(0 < preg_match('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?)%(?!%)/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
			$Key = str_replace('\/', '/', $Match[1][0]);

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

		while(0 < preg_match('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?)\("(.*?)"\)%(?!%)/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
			$Key = str_replace('\/', '/', $Match[1][0]);

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