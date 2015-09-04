<?
namespace Framework\Newnorth\DataMembers;

class TranslationDataMember extends \Framework\Newnorth\ADataMember {
	/* Instance variables */

	public $DataManager;

	public $DataMember;

	public $Key;

	public $Name;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->DataManager = $Parameters['DataMember']->DataManager;

		$this->DataMember = $Parameters['DataMember'];

		$this->Key = $Parameters['Key'];

		$this->Name = $Parameters['Name'];
	}

	/* Instance methods */

	public function Parse($Value) {
		return $this->DataMember->Parse($Value);
	}

	public function ToDbExpression($Value) {
		return $this->DataMember->ToDbExpression($Value);
	}

	public function Set(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->DataMember->Set($DataType, $Parameters);
	}

	public function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->DataMember->Increment($DataType, $Parameters);
	}

	public function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->DataMember->Decrement($DataType, $Parameters);
	}
}
?>