<?
namespace Framework\Newnorth;

class RerouteException extends \Exception {
	/* Instance variables */

	public $Parameters;

	/* Magic methods */

	public function __construct($Parameters = []) {
		$this->Parameters = $Parameters;
	}
}
?>