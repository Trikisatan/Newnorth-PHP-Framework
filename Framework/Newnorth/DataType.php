<?
namespace Framework\Newnorth;

abstract class DataType {
	/* Magic methods */

	public abstract function __construct($Data);

	public function __toString() {
		return '';
	}
}
?>