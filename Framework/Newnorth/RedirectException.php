<?
namespace Framework\Newnorth;

class RedirectException extends \Exception {
	/* Instance variables */

	public $Url;

	/* Magic methods */

	public function __construct($Url) {
		$this->Url = $Url;
	}
}
?>