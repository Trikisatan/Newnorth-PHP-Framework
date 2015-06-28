<?
namespace Framework\Newnorth;

class Actions {
	/* Instance variables */

	private $Owner;

	private $FilePath;

	public $Items = [];

	/* Magic methods */

	public function __construct($Owner) {
		$this->Owner = $Owner;

		$this->FilePath = $this->Owner->_Directory.$this->Owner->_Name.'.php.Actions.ini';

		$this->TryLoadIniFile();
	}

	/* Life cycle methods */

	public function Execute() {
		foreach($this->Items as $Action) {
			$Action->Execute();
		}
	}

	/* Instance methods */

	private function TryLoadIniFile() {
		if(file_exists($this->FilePath)) {
			$Items = ParseIniFile($this->FilePath);

			foreach($Items as $Name => $Parameters) {
				$this->Items[$Name] = new Action(
					$this->Owner,
					$Name,
					$Parameters
				);
			}
		}
	}

	public function Destroy() {
		foreach($this->Items as $Item) {
			$Item->Destroy();
		}
	}
}
?>