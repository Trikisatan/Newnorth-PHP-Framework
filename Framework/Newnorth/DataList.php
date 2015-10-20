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
				'DataList requires the parameter "LocalKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['LocalKeys'])) {
			throw new RuntimeException(
				'DataList requires the parameter "LocalKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) === 0) {
			throw new RuntimeException(
				'DataList requires the parameter "LocalKeys" to not be an empty array.',
				[]
			);
		}
		else if(!isset($Parameters['ForeignDataManager'])) {
			throw new RuntimeException(
				'DataList requires the parameter "ForeignDataManager".',
				[]
			);
		}
		else if(!isset($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataList requires the parameter "ForeignKeys".',
				[]
			);
		}
		else if(!is_array($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataList requires the parameter "ForeignKeys" to be an array.',
				[]
			);
		}
		else if(count($Parameters['ForeignKeys']) === 0) {
			throw new RuntimeException(
				'DataList requires the parameter "ForeignKeys" to not be an empty array.',
				[]
			);
		}
		else if(count($Parameters['LocalKeys']) !== count($Parameters['ForeignKeys'])) {
			throw new RuntimeException(
				'DataList requires the parameters "LocalKeys" and "ForeignKeys" to be equal sized arrays.',
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
		if($this->PluralAlias !== null) {
			$DataType->{$this->PluralAlias} = null;

			$DataType->{'Is'.$this->PluralAlias.'Loaded'} = false;
		}
	}

	public function Load(\Framework\Newnorth\DataType $DataType) {
		if($this->PluralAlias === null || !$DataType->{'Is'.$this->PluralAlias.'Loaded'}) {
			if($this->ForeignDataManager instanceof \Framework\Newnorth\AStandardDataManager) {
				return $this->Load_AStandardDataManager($DataType);
			}
			else if($this->ForeignDataManager instanceof \Framework\Newnorth\ATranslationDataManager) {
				return $this->Load_ATranslationDataManager($DataType);
			}
			else {
				throw new RuntimeException(
					'DataList does not support loading from "'.get_class($this->ForeignDataManager).'".',
					[]
				);
			}
		}
		else {
			return $DataType->{$this->PluralAlias};
		}
	}

	public function Load_AStandardDataManager(\Framework\Newnorth\DataType $DataType) {
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

		if($this->Sorts !== null) {
			foreach($this->Sorts as $Sort) {
				$Query->AddSort($Sort['Column'], $Sort['Order']);
			}
		}

		$Result = $this->ForeignDataManager->FindAllByQuery($Query);

		if($this->PluralAlias !== null) {
			$DataType->{$this->PluralAlias} = $Result;

			$DataType->{'Is'.$this->PluralAlias.'Loaded'} = true;
		}

		return $Result;
	}

	public function Load_ATranslationDataManager(\Framework\Newnorth\DataType $DataType) {
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

		$Result = $this->ForeignDataManager->FindAllByQuery($Query);

		$Items = [];

		while(($Item = array_shift($Result)) !== null) {
			$Items[$Item->Locale] = $Item;
		}

		if($this->PluralAlias !== null) {
			$DataType->{$this->PluralAlias} = $Items;

			$DataType->{'Is'.$this->PluralAlias.'Loaded'} = true;
		}

		return $Items;
	}

	public function Create(\Framework\Newnorth\DataType $DataType, array $Data, $Source) {
		if($this->PluralAlias !== null) {
			$this->Load($DataType);
		}

		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = $this->ForeignDataManager;

		for($I = 0; $I < $this->KeyCount; ++$I) {
			$DataMember = $this->ForeignKeys[$I];

			$Query->AddColumn($DataMember);

			if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
				$Query->AddValue($DataMember->ToDbExpression($DataType->{$this->LocalKeys[$I]->Alias}));
			}
			else {
				$Query->AddValue($DataMember->ToDbExpression($this->LocalKeys[$I]));
			}
		}

		foreach($Data as $Key => $Value) {
			$DataMember = $this->ForeignDataManager->DataMembers[$Key];

			$Query->AddColumn($DataMember);

			$Query->AddValue($DataMember->ToDbExpression($Value));
		}

		$Item = $this->ForeignDataManager->InsertByQuery($Query, $Source);

		if($this->PluralAlias !== null) {
			$DataType->{$this->PluralAlias}[] = $Item;
		}

		return $Item;
	}

	public function Add(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item, $Source) {
		if($this->PluralAlias !== null) {
			$this->Load($DataType);
		}

		for($I = 0; $I < $this->KeyCount; ++$I) {
			if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
				$Item->{'Set'.$this->ForeignKeys[$I]->Alias}($DataType->{$this->LocalKeys[$I]->Alias}, $Source);
			}
			else {
				$Item->{'Set'.$this->ForeignKeys[$I]->Alias}($this->LocalKeys[$I], $Source);
			}
		}

		if($this->PluralAlias !== null) {
			$DataType->{$this->PluralAlias}[] = $Item;
		}
	}

	public function Delete(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item, $Source) {
		if($this->PluralAlias !== null) {
			$this->Load($DataType);
		}

		$this->ForeignDataManager->{'DeleteBy'.$this->ForeignPrimaryKey->Alias}($Item->{$this->ForeignPrimaryKey->Alias}, $Source);

		if($this->PluralAlias !== null) {
			$Index = $this->IndexOf($DataType, $Item);

			if(is_int($Index)) {
				array_splice($DataType->{$this->PluralAlias}, $Index, 1);
			}
			else {
				unset($DataType->{$this->PluralAlias}[$Index]);
			}
		}
	}

	public function Remove(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item, $Source) {
		if($this->PluralAlias !== null) {
			$this->Load($DataType);
		}

		for($I = 0; $I < $this->KeyCount; ++$I) {
			if($this->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
				$Item->{'Set'.$this->ForeignKeys[$I]->Alias}(null, $Source);
			}
		}

		if($this->PluralAlias !== null) {
			$Index = $this->IndexOf($DataType, $Item);

			if(is_int($Index)) {
				array_splice($DataType->{$this->PluralAlias}, $Index, 1);
			}
			else {
				unset($DataType->{$this->PluralAlias}[$Index]);
			}
		}
	}

	public function FindBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Alias} !== $Values[$J]) {
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
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Alias} !== $Values[$J]) {
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

	public function IndexOf(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $OriginalItem) {
		$this->Load($DataType);

		foreach($DataType->{$this->PluralAlias} as $Key => $Item) {
			if($Item->{$this->ForeignPrimaryKey->Alias} === $OriginalItem->{$this->ForeignPrimaryKey->Alias}) {
				return $Key;
			}
		}

		return null;
	}

	public function IndexOfBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		foreach($DataType->{$this->PluralAlias} as $Key => $Item) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($Item->{$DataMembers[$J]->Alias} !== $Values[$J]) {
					$IsFound = false;

					break;
				}
			}

			if($IsFound) {
				return $Key;
			}
		}

		return null;
	}

	public function Has(\Framework\Newnorth\DataType $DataType, \Framework\Newnorth\DataType $Item) {
		$this->Load($DataType);

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			if($DataType->{$this->PluralAlias}[$I]->{$this->ForeignPrimaryKey->Alias} === $Item->{$this->ForeignPrimaryKey->Alias}) {
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
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Alias} !== $Values[$J]) {
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

	public function Count(\Framework\Newnorth\DataType $DataType, array $Values) {
		if($DataType->{'Is'.$this->PluralAlias.'Loaded'}) {
			return count($DataType->{$this->PluralAlias});
		}
		else {
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

			return $this->ForeignDataManager->CountByQuery($Query);
		}
	}

	public function CountBy(\Framework\Newnorth\DataType $DataType, array $DataMembers, array $Values) {
		$this->Load($DataType);

		$Count = 0;

		for($I = 0; $I < count($DataType->{$this->PluralAlias}); ++$I) {
			$IsFound = true;

			for($J = 0; $J < count($DataMembers); ++$J) {
				if($DataType->{$this->PluralAlias}[$I]->{$DataMembers[$J]->Alias} !== $Values[$J]) {
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