<?
namespace Framework\Newnorth;

abstract class AValueDataMember extends \Framework\Newnorth\ADataMember {
	/* Instance variables */

	public $DataManager;

	public $Alias;

	public $IsDynamic = false;

	public $IsNullable = true;

	/* Magic methods */

	public function __construct($Parameters) {
		parent::__construct($Parameters);

		$this->DataManager = $Parameters['DataManager'];

		$this->Alias = $Parameters['Alias'];

		if(isset($Parameters['IsDynamic'])) {
			$this->IsDynamic = $Parameters['IsDynamic'];
		}

		if(isset($Parameters['IsNullable'])) {
			$this->IsNullable = $Parameters['IsNullable'];
		}
	}

	public function __toString() {
		return '`'.$this->DataManager->Table.'`.`'.$this->Alias.'`';
	}

	/* Instance methods */

	public function Set(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$Value = $this->Parse($Parameters[0]);

			array_splice($Parameters, 0, 1);

			if($DataType->{$this->Alias} === $Value) {
				return true;
			}
			else {
				$Query = new \Framework\Newnorth\DbUpdateQuery();

				$Query->AddSource($this->DataManager);

				$Query->Conditions = new \Framework\Newnorth\DbEqualTo($this->DataManager->PrimaryKey, $DataType->{$this->DataManager->PrimaryKey->Alias});

				$Query->AddChange($this, $this->ToDbExpression($Value));

				$Result = $this->DataManager->Connection->Update($Query);

				$DataType->{$this->Alias} = $Value;

				if($this->UseLogUpdate) {
					$this->LogUpdate($DataType, isset($Parameters[0]) ? $Parameters[0] : '');
				}

				return $Result;
			}
		}
		else {
			throw new RuntimeException(
				'This data member is static.',
				[
					'Data member' => $this->Alias,
				]
			);
		}
	}

	public function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$Query = new \Framework\Newnorth\DbUpdateQuery();

			$Query->AddSource($this->DataManager);

			$Query->Conditions = new \Framework\Newnorth\DbEqualTo($this->DataManager->PrimaryKey, $DataType->{$this->DataManager->PrimaryKey->Alias});

			$Query->AddChange($this, $this.' + 1');

			$Result = $this->DataManager->Connection->Update($Query);

			++$DataType->{$this->Alias};

			if($this->UseLogUpdate) {
				$this->LogUpdate($DataType, isset($Parameters[0]) ? $Parameters[0] : '');
			}

			return $Result;
		}
		else {
			throw new RuntimeException(
				'This data member is static.',
				[
					'Data member' => $this->Alias,
				]
			);
		}
	}

	public function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$Query = new \Framework\Newnorth\DbUpdateQuery();

			$Query->AddSource($this->DataManager);

			$Query->Conditions = new \Framework\Newnorth\DbEqualTo($this->DataManager->PrimaryKey, $DataType->{$this->DataManager->PrimaryKey->Alias});

			$Query->AddChange($this, $this.' - 1');

			$Result = $this->DataManager->Connection->Update($Query);

			--$DataType->{$this->Alias};

			if($this->UseLogUpdate) {
				$this->LogUpdate($DataType, isset($Parameters[0]) ? $Parameters[0] : '');
			}

			return $Result;
		}
		else {
			throw new RuntimeException(
				'This data member is static.',
				[
					'Data member' => $this->Alias,
				]
			);
		}
	}
}
?>