<?
namespace Framework\Newnorth;

abstract class DataMember {
	/* Instance variables */

	public $DataManager;

	public $Name;

	public $IsDynamic = false;

	public $IsNullable = true;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->DataManager = $Parameters['DataManager'];
		if(!isset($Parameters['Name'])) {
		var_dump($Parameters);die();}
		$this->Name = $Parameters['Name'];

		if(isset($Parameters['IsDynamic'])) {
			$this->IsDynamic = $Parameters['IsDynamic'];
		}

		if(isset($Parameters['IsNullable'])) {
			$this->IsNullable = $Parameters['IsNullable'];
		}
	}

	/* Instance methods */

	public abstract function Parse($Value);

	public abstract function ToDbExpression($Value);

	public abstract function Set(DataType $DataType, array $Parameters);
}
?>