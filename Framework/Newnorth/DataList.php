<?
namespace Framework\Newnorth;

class DataList {
	/* Instance variables */

	public $KeyCount;

	public $LocalKeys;

	public $ForeignDataManager;

	public $ForeignPrimaryKey;

	public $ForeignKeys;

	public $SingularAlias = null;

	public $PluralAlias = null;

	public $Sorts = [];

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
		else if(!isset($Parameters['Alias'])) {
			throw new RuntimeException(
				'DataReference requires the parameter "Alias".',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) !== count($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataReference requires the parameters "LocalKeys" and "ForeignKeys" to be equal sized arrays.',
				[]
			);
		}

		$this->KeyCount = count($Parameters['LocalKeys']);

		$this->LocalKeys = $Parameters['LocalKeys'];

		$this->ForeignDataManager = $Parameters['ForeignDataManager'];

		$this->ForeignPrimaryKey = $Parameters['ForeignDataManager']->PrimaryKey;

		$this->ForeignKeys = $Parameters['ForeignKeys'];

		if(isset($Parameters['SingularAlias'])) {
			$this->SingularAlias = $Parameters['SingularAlias'];
		}

		if(isset($Parameters['PluralAlias'])) {
			$this->PluralAlias = $Parameters['PluralAlias'];
		}

		if(isset($Parameters['Sorts'])) {
			$this->Sorts = $Parameters['Sorts'];
		}

		if(isset($Parameters['OnDelete'])) {
			$this->OnDelete = $Parameters['OnDelete'];
		}
	}

	/* Instance methods */

	public function Initialize(\Framework\Newnorth\DataType $DataType) {
		$DataType->{$this->PluralAlias} = null;

		$DataType->{'Is'.$this->PluralAlias.'Loaded'} = false;
	}

	public function Load(\Framework\Newnorth\DataType $DataType) {
		if(!$DataType->{'Is'.$this->PluralAlias.'Loaded'}) {
			$Query = $this->ForeignDataManager->CreateSelectQuery();

			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			for($I = 0; $I < $this->KeyCount; ++$I) {
				if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
					$Query->Conditions->EqualTo($this->ForeignKeys[$I], $DataType->{$this->LocalKeys[$I]->Name});
				}
				else {
					$Query->Conditions->EqualTo($this->ForeignKeys[$I], $this->LocalKeys[$I]);
				}
			}

			if($this->Sorts !== null) {
				foreach($this->Sorts as $Sort) {
					$Query->AddSort($Sort['Column'], $Sort['Order']);
				}
			}

			$DataType->{$this->PluralAlias} = $this->ForeignDataManager->FindAllByQuery($Query);

			$DataType->{'Is'.$this->PluralAlias.'Loaded'} = true;
		}

		return 0 < count($DataType->{$this->PluralAlias});
	}

	public function Create(\Framework\Newnorth\DataType $DataType, array $Data) {
		$this->Load($DataType);

		for($I = 0; $I < $this->KeyCount; ++$I) {
			$Data[$this->ForeignKeys[$I]->Name] = $DataType->{$this->LocalKeys[$I]->Name};
		}

		foreach($Data as $Key => $Value) {
			$Data[$Key] = $this->ForeignDataManager->DataMembers[$Key]->ToDbExpression($Value);
		}

		$Id = $this->ForeignDataManager->InsertByArray($Data);

		$Item = $this->ForeignDataManager->{'FindBy'.$this->ForeignPrimaryKey->Name}($Id);

		foreach($this->ForeignDataManager->DataLists as $DataList) {
			$Item->{$DataList->PluralAlias} = [];

			$Item->{'Is'.$DataList->PluralAlias.'Loaded'} = true;
		}

		$DataType->{$this->PluralAlias}[] = $Item;

		return $Item;
	}

	public function Add(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < $this->KeyCount; ++$I) {
			if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
				$Item->{'Set'.$this->ForeignKeys[$I]->Name}($DataType->{$this->LocalKeys[$I]->Name});
			}
			else {
				$Item->{'Set'.$this->ForeignKeys[$I]->Name}($this->LocalKeys[$I]);
			}
		}

		$DataType->{$this->PluralAlias}[] = $Item;
	}

	public function Delete(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		$this->ForeignDataManager->{'DeleteBy'.$this->ForeignPrimaryKey->Name}($Item->{$this->ForeignPrimaryKey->Name});

		$Index = $this->IndexOf($DataType, $Item);

		array_splice($DataType->{$this->PluralAlias}, $Index, 1);
	}

	public function Remove(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < $this->KeyCount; ++$I) {
			if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
				$Item->{'Set'.$this->ForeignKeys[$I]->Name}(null);
			}
		}

		$Index = $this->IndexOf($DataType, $Item);

		array_splice($DataType->{$this->PluralAlias}, $Index, 1);
	}

	public function FindBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return $DataType->{$this->PluralAlias}[$I];
			}
		}

		return null;
	}

	public function FindAllBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		$Items = [];

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				$Items[] = $DataType->{$this->PluralAlias}[$I];
			}
		}

		return $Items;
	}

	public function IndexOf(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			if($DataType->{$this->PluralAlias}[$I]->{$this->ForeignPrimaryKey->Name} === $Item->{$this->ForeignPrimaryKey->Name}) {
				return $I;
			}
		}

		return -1;
	}

	public function IndexOfBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return $I;
			}
		}

		return -1;
	}

	public function Has(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			if($DataType->{$this->PluralAlias}[$I]->{$this->ForeignPrimaryKey->Name} === $Item->{$this->ForeignPrimaryKey->Name}) {
				return true;
			}
		}

		return false;
	}

	public function HasBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return true;
			}
		}

		return false;
	}

	public function CountBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		$Count = 0;

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Name} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				++$Count;
			}
		}

		return $Count;
	}
}
?>