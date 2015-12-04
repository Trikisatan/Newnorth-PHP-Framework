<?
namespace Framework\Newnorth;

class Routing {
	/* Instance variables */

	public $FilePath;

	public $Route;

	/* Magic methods */

	public function __construct($FilePath = null) {
		$this->FilePath = $FilePath;

		$this->Route = new Route(null, '', []);

		$this->Route->AddRoute(
			'BadRequest',
			[
				'Pattern' => '400',
				'Layout' => 'Default',
			]
		);

		$this->Route->AddRoute(
			'Forbidden',
			[
				'Pattern' => '403',
				'Layout' => 'Default',
			]
		);

		$this->Route->AddRoute(
			'NotFound',
			[
				'Pattern' => '404',
				'Layout' => 'Default',
			]
		);

		$this->Route->AddRoute(
			'Error',
			[
				'Pattern' => '500',
				'Layout' => 'Default',
			]
		);
	}

	/* Instance methods */

	public function Initialize() {
		$Data = file_get_contents($this->FilePath);

		$Data = json_decode($Data, true);

		$this->Route = new Route(null, '', $Data);
	}
}
?>