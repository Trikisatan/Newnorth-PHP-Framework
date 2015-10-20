<?
namespace Framework\Newnorth;

class ReferenceDataMember extends \Framework\Newnorth\ADataMember {
	/* Instance variables */

	public $KeyCount;

	public $LocalKeys;

	public $ForeignDataManager;

	public $ForeignKeys;

	public $ForeignDataMember;

	public $Alias;

	public $IsRequired = true;

	/* Magic methods */

	public function __construct($Parameters) {
		parent::__construct($Parameters);

		if(!isset($Parameters['LocalKeys'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "LocalKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['LocalKeys'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "LocalKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) === 0) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "LocalKeys" to not be an empty array.',
				[]
			);
		}
		else if(!isset($Parameters['ForeignDataManager'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "ForeignDataManager".',
				[]
			);
		}
		else if(!isset($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "ForeignKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "ForeignKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['ForeignKeys']) === 0) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "ForeignKeys" to not be an empty array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) !== count($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameters "LocalKeys" and "ForeignKeys" to be equal sized arrays.',
				[]
			);
		}
		else if(!isset($Parameters['ForeignDataMember'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "ForeignDataMember".',
				[]
			);
		}
		else if(!isset($Parameters['Alias'])) {
			throw new RuntimeException(
				'ReferenceDataMember requires the parameter "Alias".',
				[]
			);
		}

		$this->KeyCount = count($Parameters['LocalKeys']);

		$this->LocalKeys = $Parameters['LocalKeys'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignKeys = $Parameters['ForeignKeys'];

		$this->ForeignDataMember = $Parameters['ForeignDataMember'];

		$this->Alias = $Parameters['Alias'];

		if(isset($Parameters['IsRequired'])) {
			$this->IsRequired = $Parameters['IsRequired'];
		}
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