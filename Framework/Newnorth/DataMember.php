<?
namespace Framework\Newnorth;

abstract class DataMember {
	/* Instance variables */

	public $DataManager;

	public $Name;

	public $IsDynamic;

	/* Magic methods */

	public function __construct($DataManager, $Name, $IsDynamic) {
		$this->DataManager = $DataManager;

		$this->Name = $Name;

		$this->IsDynamic = $IsDynamic;
	}

	/* Instance methods */

	public abstract function Parse($Value);

	public abstract function ToDbExpression($Value);

	public abstract function Set(DataType $DataType, array $Parameters);
}
?>