<?
namespace Framework\Newnorth;

class ReferenceDataMember extends \Framework\Newnorth\ADataMember {
	/* Instance variables */

	public $DataManager;

	public $Reference;

	public $LocalKey;

	public $Name;

	/* Magic methods */

	public function __construct($Parameters) {
		$this->DataManager = $Parameters['Reference']->DataManager;

		$this->Reference = $Parameters['Reference'];

		$this->LocalKey = $Parameters['LocalKey'];

		$this->Name = $Parameters['Name'];
	}

	public function __toString() {
		return $this->Reference->__toString();
	}

	/* Instance methods */

	public function Parse($Value) {
		return $this->Reference->Parse($Value);
	}

	public function ToDbExpression($Value) {
		return $this->Reference->ToDbExpression($Value);
	}

	public function Set(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->Reference->Set($DataType, $Parameters);
	}

	public function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->Reference->Increment($DataType, $Parameters);
	}

	public function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		return $this->Reference->Decrement($DataType, $Parameters);
	}
}
?>