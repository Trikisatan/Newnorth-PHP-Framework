<?
namespace Framework\Newnorth;

class Translations {
	/* Instance variables */

	private $Owner;

	private $Directory;

	private $Items = [];

	/* Magic methods */

	public function __construct($Owner, $Directory) {
		$this->Owner = $Owner;

		$this->Directory = $Directory;

		$this->TryLoadIniFile();
	}

	/* Instance methods */

	private function TryLoadIniFile() {
		if(isset($GLOBALS['Parameters']['Locale'][0])) {
			if(isset($GLOBALS['Config']->Files['Translations'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Translations'].$this->Directory.'Translations.'.$GLOBALS['Parameters']['Locale'].'.ini';
			}
			else if($this->Owner instanceof Layout && isset($GLOBALS['Config']->Files['Layouts'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Layouts'].$this->Directory.'Translations.'.$GLOBALS['Parameters']['Locale'].'.ini';
			}
			else if($this->Owner instanceof Page && isset($GLOBALS['Config']->Files['Pages'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Pages'].$this->Directory.'Translations.'.$GLOBALS['Parameters']['Locale'].'.ini';
			}
			else
			{
				$FilePath = $this->Directory.'Translations.'.$GLOBALS['Parameters']['Locale'].'.ini';
			}
		}
		else {
			if(isset($GLOBALS['Config']->Files['Translations'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Translations'].$this->Directory.'Translations.ini';
			}
			else if($this->Owner instanceof Layout && isset($GLOBALS['Config']->Files['Layouts'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Layouts'].$this->Directory.'Translations.ini';
			}
			else if($this->Owner instanceof Page && isset($GLOBALS['Config']->Files['Pages'][0]))
			{
				$FilePath = $GLOBALS['Config']->Files['Pages'].$this->Directory.'Translations.ini';
			}
			else
			{
				$FilePath = $this->Directory.'Translations.ini';
			}
		}

		if(!file_exists($FilePath)) {
			return;
		}

		$Translations = ParseIniFile($FilePath);

		foreach($Translations as $Key => $Value) {
			$this->Items[$Key] = $Value;
		}
	}

	public function Translate(&$Contents) {
		$Offset = 0;

		while(0 < preg_match('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?)%(?!%)/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
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

		while(0 < preg_match('/(?<!%)%([a-zA-Z0-9_\\/\\\\]+?)\("(.*?)"\)%(?!%)/', $Contents, $Match, PREG_OFFSET_CAPTURE, $Offset)) {
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