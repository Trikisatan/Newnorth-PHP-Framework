<?
namespace Framework\Newnorth;

class ReferenceDataMember extends \Framework\Newnorth\ADataMember {
	/* Instance variables */

	public $LocalKey;

	public $ForeignDataManager;

	public $ForeignDataMember;

	public $Alias;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->LocalKey = $Parameters['LocalKey'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignDataMember = $Parameters['ForeignDataMember'];

		$this->Alias = $Parameters['Alias'];
	}

	public function __toString() {
		return $this->ForeignDataMember->__toString();
	}

	/* Instance methods */

	public function Parse($Value) {
		return $this->ForeignDataMember->Parse($Value);
	}

	public function ToDbExpression($Value) {
		return $this->ForeignDataMember->ToDbExpression($Value);
	}

	public function Set(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->ForeignDataMember->Set($DataType, $Parameters);
	}

	public function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->ForeignDataMember->Increment($DataType, $Parameters);
	}

	public function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->ForeignDataMember->Decrement($DataType, $Parameters);
	}
}
?>