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
	}

	/* Life cycle methods */

	public function Initialize() {
		if(file_exists($this->FilePath)) {
			$Items = ParseIniFile($this->FilePath);

			foreach($Items as $Name => $Parameters) {
				$Action = new Action($this->Owner, $Name);

				$Action->Initialize($Parameters);

				$this->Items[$Name] = $Action;
			}
		}
	}

	public function Execute() {
		foreach($this->Items as $Action) {
			$Action->Execute();
		}
	}

	/* Instance methods */

	public function Destroy() {
		foreach($this->Items as $Item) {
			$Item->Destroy();
		}
	}
}
?>