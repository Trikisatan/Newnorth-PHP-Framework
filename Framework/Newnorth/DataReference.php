<?
namespace Framework\Newnorth;

class DataReference {
	/* Instance variables */

	public $KeyCount;

	public $LocalKeys;

	public $ForeignDataManager;

	public $ForeignPrimaryKey;

	public $ForeignKeys;

	public $Alias;

	public $OnDelete = null;

	/* Magic methods */

	public function __construct($Parameters) {
		if(!isset($Parameters['LocalKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "LocalKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['LocalKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "LocalKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) === 0) {
			throw new RuntimeException(
				'DataReference requires the parameter "LocalKeys" to not be an empty array.',
				[]
			);
		}
		else if(!isset($Parameters['ForeignDataManager'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "ForeignDataManager".',
				[]
			);
		}
		else if(!isset($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "ForeignKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "ForeignKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['ForeignKeys']) === 0) {
			throw new RuntimeException(
				'DataReference requires the parameter "ForeignKeys" to not be an empty array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) !== count($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameters "LocalKeys" and "ForeignKeys" to be equal sized arrays.',
				[]
			);
		}
		else if(!isset($Parameters['Alias'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "Alias".',
				[]
			);
		}

		$this->KeyCount = count($Parameters['LocalKeys']);

		$this->LocalKeys = $Parameters['LocalKeys'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignPrimaryKey = $Parameters['ForeignDataManager']->PrimaryKey;

		$this->ForeignKeys = $Parameters['ForeignKeys'];

		$this->Alias = $Parameters['Alias'];

		if(isset($Parameters['OnDelete'])) {
			$this->OnDelete = $Parameters['OnDelete'];
		}
	}

	/* Instance methods */

	public function Initialize(\Framework\Newnorth\DataType $DataType) {
		$DataType->{$this->Alias} = null;

		$DataType->{'Is'.$this->Alias.'Loaded'} = false;
	}

	public function Load(\Framework\Newnorth\DataType $DataType) {
		if(!$DataType->{'Is'.$this->Alias.'Loaded'}) {
			$Query = $this->ForeignDataManager->CreateSelectQuery();

			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			for($I = 0; $I < $this->KeyCount; ++$I) {
				if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
					$Query->Conditions->EqualTo($this->ForeignKeys[$I], $this->ForeignKeys[$I]->ToDbExpression($DataType->{$this->LocalKeys[$I]->Alias}));
				}
				else {
					$Query->Conditions->EqualTo($this->ForeignKeys[$I], $this->ForeignKeys[$I]->ToDbExpression($this->LocalKeys[$I]));
				}
			}

			$DataType->{$this->Alias} = $this->ForeignDataManager->FindByQuery($Query);

			$DataType->{'Is'.$this->Alias.'Loaded'} = true;
		}

		return $DataType->{$this->Alias} !== null;
	}

	public function Delete(\Framework\Newnorth\DataType $DataType, $Source) {
		$this->Load($DataType);

		$this->ForeignDataManager->{'DeleteBy'.$this->ForeignPrimaryKey->Alias}($DataType->{$this->Alias}->{$this->ForeignPrimaryKey->Alias}, $Source);

		$DataType->{$this->Alias} = null;
	}

	public function Remove(\Framework\Newnorth\DataType $DataType, $Source) {
		$this->Load($DataType);

		foreach($this->ForeignKeys as $ForeignKey)
		{
			$DataType->{$this->Alias}->{'Set'.$ForeignKey->Alias}(null, $Source);
		}

		$DataType->{$this->Alias} = null;
	}
}
?>