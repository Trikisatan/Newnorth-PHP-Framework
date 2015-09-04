<?
namespace Framework\Newnorth;

class ReferenceDataMember extends DataMember {
	/* Instance variables */

	public $DataMember;

	public $Key;

	/* Magic methods */

	public function __construct($Parameters) {
		parent::__construct($Parameters);

		$this->DataMember = $Parameters['DataMember'];

		$this->Key = $Parameters['Key'];
	}

	/* Instance methods */

	public function Parse($Value) {
		return $this->DataMember->Parse($Value);
	}

	public function ToDbExpression($Value) {
		return $this->DataMember->ToDbExpression($Value);
	}

	public function Set(DataType $DataType, array $Parameters) {
		return $this->DataMember->Set($DataType, $Parameters);
	}
}
?>