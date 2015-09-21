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
			$Query = new \Framework\Newnorth\DbUpdateQuery();

			$Query->AddSource(
				$this->DataManager
			);

			$Query->Conditions = new \Framework\Newnorth\DbEqualTo(
				$this->DataManager->PrimaryKey,
				$DataType->{$this->DataManager->PrimaryKey->Alias}
			);

			$Query->AddChange(
				$this,
				$this->ToDbExpression($Parameters[0])
			);

			$Result = $this->DataManager->Connection->Update($Query);

			$DataType->{$this->Alias} = $this->Parse($Parameters[0]);

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

	public function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters) {
		if($this->IsDynamic) {
			$Query = new \Framework\Newnorth\DbUpdateQuery();

			$Query->AddSource(
				$this->DataManager
			);

			$Query->Conditions = new \Framework\Newnorth\DbEqualTo(
				$this->DataManager->PrimaryKey,
				$DataType->{$this->DataManager->PrimaryKey->Alias}
			);

			$Query->AddChange(
				$this,
				$this.' + 1'
			);

			return $this->DataManager->Connection->Update(
				$Query
			);
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

			$Query->AddSource(
				$this->DataManager
			);

			$Query->Conditions = new \Framework\Newnorth\DbEqualTo(
				$this->DataManager->PrimaryKey,
				$DataType->{$this->DataManager->PrimaryKey->Alias}
			);

			$Query->AddChange(
				$this,
				$this.' - 1'
			);

			return $this->DataManager->Connection->Update(
				$Query
			);
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