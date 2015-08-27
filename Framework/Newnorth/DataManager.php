<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Instance variables */

	public $DataType = null;

	public $Connection = null;

	public $Database = null;

	public $Table = null;

	public $PrimaryKey = null;

	public $DataMembers = [];

	/* Magic methods */

	public function __call($Function, $Parameters) {
		if(strpos($Function, 'FindBy') === 0) {
			$DataMembers = substr($Function, 6);

			$DataMembers = explode('And', $DataMembers);

			for($I = 0; $I < count($DataMembers); ++$I) {
				if(isset($this->DataMembers[$DataMembers[$I]])) {
					$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
				}
				else {
					throw new RuntimeException(
						'Object method doesn\'t exist.',
						['Function' => $Function, 'Parameters' => $Parameters]
					);
				}
			}

			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->Name.'`';

				if($I === 0) {
					$Value = $DataMember->ToDbExpression($Parameters[0]);

					$Parameters[0] = [];
				}
				else {
					$Value = $DataMember->ToDbExpression($Parameters[1]);

					array_splice($Parameters, 1, 1);
				}

				$Parameters[0][$Column] = $Value;
			}

			return call_user_func_array([$this, 'FindByArray'], $Parameters);
		}
		else if(strpos($Function, 'FindAllBy') === 0) {
			$DataMembers = substr($Function, 9);

			$DataMembers = explode('And', $DataMembers);

			for($I = 0; $I < count($DataMembers); ++$I) {
				if(isset($this->DataMembers[$DataMembers[$I]])) {
					$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
				}
				else {
					throw new RuntimeException(
						'Object method doesn\'t exist.',
						['Function' => $Function, 'Parameters' => $Parameters]
					);
				}
			}

			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->Name.'`';

				if($I === 0) {
					$Value = $DataMember->ToDbExpression($Parameters[0]);

					$Parameters[0] = [];
				}
				else {
					$Value = $DataMember->ToDbExpression($Parameters[1]);

					array_splice($Parameters, 1, 1);
				}

				$Parameters[0][$Column] = $Value;
			}

			return call_user_func_array([$this, 'FindAllByArray'], $Parameters);
		}
		else if(strpos($Function, 'CountBy') === 0) {
			$DataMembers = substr($Function, 7);

			$DataMembers = explode('And', $DataMembers);

			for($I = 0; $I < count($DataMembers); ++$I) {
				if(isset($this->DataMembers[$DataMembers[$I]])) {
					$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
				}
				else {
					throw new RuntimeException(
						'Object method doesn\'t exist.',
						['Function' => $Function, 'Parameters' => $Parameters]
					);
				}
			}

			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->Name.'`';

				if($I === 0) {
					$Value = $DataMember->ToDbExpression($Parameters[0]);

					$Parameters[0] = [];
				}
				else {
					$Value = $DataMember->ToDbExpression($Parameters[1]);

					array_splice($Parameters, 1, 1);
				}

				$Parameters[0][$Column] = $Value;
			}

			return call_user_func_array([$this, 'CountByArray'], $Parameters);
		}
		else {
			throw new RuntimeException(
				'Object method doesn\'t exist.',
				['Function' => $Function, 'Parameters' => $Parameters]
			);
		}
	}

	/* Instance methods */

	public function AddBoolDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new BoolDataMember($this, $Name, $IsDynamic);
	}

	public function AddBoolTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new BoolTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddFloatDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new FloatDataMember($this, $Name, $IsDynamic);
	}

	public function AddFloatTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new FloatTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddIntDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new IntDataMember($this, $Name, $IsDynamic);
	}

	public function AddIntTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new IntTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function AddStringDataMember($Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new StringDataMember($this, $Name, $IsDynamic);
	}

	public function AddStringTranslationDataMember(TranslationDataManager $DataManager, $Name, $IsDynamic) {
		return $this->DataMembers[$Name] = new StringTranslationDataMember($DataManager, $Name, $IsDynamic);
	}

	public function InsertByQuery(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
		}
	}

	public function InsertByArray(array $Data) {
		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		foreach($Data as $Column => $Value) {
			$Query->AddColumn('`'.$Column.'`');

			$Query->AddValue($Value);
		}

		return $this->InsertByQuery($Query);
	}

	public function InsertUpdateByQuery(DbInsertUpdateQuery $Query) {
		$Result = $this->Connection->InsertUpdate($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
		}
	}

	public function UpdateByQuery(DbUpdateQuery $Query) {
		$Result = $this->Connection->Update($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->AffectedRows();
		}
	}

	public function UpdateByArray(DbCondition $Conditions = null, array $Changes) {
		$Query = new \Framework\Newnorth\DbUpdateQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = $Conditions;

		foreach($Changes as $Column => $Value) {
			$Query->AddChange('`'.$Column.'`', $Value);
		}

		return $this->Connection->Update($Query);
	}

	public function Delete($Item) {
		$Query = new \Framework\Newnorth\DbDeleteQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = new \Framework\Newnorth\DbEqualTo(
			'`'.$this->PrimaryKey->Name.'`',
			$Item->{$this->PrimaryKey->Name}
		);

		$Result = $this->Connection->Delete($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->AffectedRows() === 1;
		}
	}

	public function DeleteByQuery(DbDeleteQuery $Query) {
		$Result = $this->Connection->Delete($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->AffectedRows();
		}
	}

	public function DeleteByArray(array $Conditions = null) {
		$Query = new \Framework\Newnorth\DbDeleteQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		if($Conditions !== null) {
			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			foreach($Conditions as $DbExpressionA => $DbExpressionB) {
				$Query->Conditions->EqualTo($DbExpressionA, $DbExpressionB);
			}
		}

		return $this->DeleteByQuery($Query);
	}

	public function FindByQuery(DbSelectQuery $Query) {
		$Result = $this->Connection->Find($Query);

		if($Result === false) {
			return null;
		}
		else if($Result->FetchAssoc()) {
			return new $this->DataType($this, $Result->GetProcessedRow());
		}
		else {
			return null;
		}
	}

	public function FindByArray(array $Conditions = null, $SortColumn = null, $SortOrder = null) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		if($Conditions !== null) {
			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			foreach($Conditions as $DbExpressionA => $DbExpressionB) {
				$Query->Conditions->EqualTo($DbExpressionA, $DbExpressionB);
			}
		}

		if($SortColumn !== null && $SortOrder !== null) {
			$Query->AddSort($SortColumn, $SortOrder);
		}

		return $this->FindByQuery($Query);
	}

	public function FindAllByQuery(DbSelectQuery $Query) {
		$Result = $this->Connection->FindAll($Query);

		if($Result === false) {
			return [];
		}
		else {
			$Items = [];

			while($Result->FetchAssoc()) {
				$Items[] = new $this->DataType($this, $Result->GetProcessedRow());
			}

			return $Items;
		}
	}

	public function FindAllByArray(array $Conditions = null, array $Sorts = null, $MaxRows = null, $FirstRow = 0) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		if($Conditions !== null) {
			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			foreach($Conditions as $DbExpressionA => $DbExpressionB) {
				$Query->Conditions->EqualTo($DbExpressionA, $DbExpressionB);
			}
		}

		if($Sorts !== null) {
			foreach($Sorts as $Sort) {
				$Query->AddSort($Sort['Column'], $Sort['Order']);
			}
		}

		if($MaxRows !== null) {
			$Query->Limit($MaxRows, $FirstRow);
		}

		return $this->FindAllByQuery($Query);
	}

	public function CountByQuery(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}

	public function CountByArray(array $Conditions = null) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		if($Conditions !== null) {
			$Query->Conditions = new \Framework\Newnorth\DbAnd();

			foreach($Conditions as $DbExpressionA => $DbExpressionB) {
				$Query->Conditions->EqualTo($DbExpressionA, $DbExpressionB);
			}
		}

		return $this->CountByQuery($Query);
	}
}
?>