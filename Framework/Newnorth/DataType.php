<?
namespace Framework\Newnorth;

abstract class DataType {
	/* Variables */
	protected $Data;

	/* Magic methods */
	public function __construct($Data = array()) {
		$this->Data = $Data;
	}
	public function __toString() {
		return '';
	}
}
?>