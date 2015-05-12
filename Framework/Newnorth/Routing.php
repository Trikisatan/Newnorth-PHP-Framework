<?
namespace Framework\Newnorth;

class Routing {
	/* Instance variables */

	public $FilePath;

	public $Data;

	public $Route;

	/* Magic methods */

	public function __construct($FilePath = null) {
		$this->FilePath = $FilePath;

		$this->Route = new Route(null, '', []);
	}

	/* Instance methods */

	public function Initialize() {
		$this->Data = file_get_contents($this->FilePath);

		$this->Data = json_decode($this->Data, true);

		$this->Route = new Route(null, '', $this->Data);
	}
}
?>