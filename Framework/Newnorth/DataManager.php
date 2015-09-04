<?
namespace Framework\Newnorth;

abstract class DataManager {
	/* Instance variables */

	public $UseDataType = true;

	public $DataType = null;

	public $Connection = null;

	public $Database = null;

	public $Table = null;

	public $PrimaryKey = null;

	public $DataMembers = [];

	/* Magic methods */

	public function __call($Function, $Parameters) {
		if(strpos($Function, 'DeleteBy') === 0) {
			if($this->DeleteBy(substr($Function, 8), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'FindBy') === 0) {
			if($this->FindBy(substr($Function, 6), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'FindAllBy') === 0) {
			if($this->FindAllBy(substr($Function, 9), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(strpos($Function, 'CountBy') === 0) {
			if($this->CountBy(substr($Function, 7), $Parameters, $Result)) {
				return $Result;
			}
		}
		else if(preg_match('/^Set([A-Z][0-9A-Za-z]*)By([A-Z][0-9A-Za-z]*)$/', $Function, $Matches) === 1) {
			$Column = $Matches[1];

			$Expression = $Matches[2];

			if($this->SetBy($Column, $Expression, $Parameters)) {
				return true;
			}
		}

		throw new RuntimeException(
			'Object method doesn\'t exist.',
			[
				'Function' => $Function,
				'Parameters' => $Parameters,
			]
		);
	}

	/* Add methods */

	public function AddBoolDataMember($Parameters) {
		if(!isset($Parameters['DataManager'])) {
			$Parameters['DataManager'] = $this;
		}

		$Parameter = new BoolDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddBoolTranslationDataMember($Parameters) {
		$Parameter = new BoolTranslationDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddFloatDataMember($Parameters) {
		if(!isset($Parameters['DataManager'])) {
			$Parameters['DataManager'] = $this;
		}

		$Parameter = new FloatDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddFloatTranslationDataMember($Parameters) {
		$Parameter = new FloatTranslationDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddIntDataMember($Parameters) {
		if(!isset($Parameters['DataManager'])) {
			$Parameters['DataManager'] = $this;
		}

		$Parameter = new IntDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddIntTranslationDataMember($Parameters) {
		$Parameter = new IntTranslationDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddReferenceDataMember($Parameters) {
		$Parameter = new ReferenceDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddStringDataMember($Parameters) {
		if(!isset($Parameters['DataManager'])) {
			$Parameters['DataManager'] = $this;
		}

		$Parameter = new StringDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	public function AddStringTranslationDataMember($Parameters) {
		$Parameter = new StringTranslationDataMember($Parameters);

		$this->DataMembers[$Parameter->Name] = $Parameter;

		return $Parameter;
	}

	/* Get methods */

	public function GetDataMembers(&$DataMembers) {
		for($I = 0; $I < count($DataMembers); ++$I) {
			if(isset($this->DataMembers[$DataMembers[$I]])) {
				$DataMembers[$I] = $this->DataMembers[$DataMembers[$I]];
			}
			else {
				return false;
			}
		}

		return true;
	}

	/* Set methods */

	private function SetBy($Column, $Expression, $Parameters) {
		$Value = $Parameters[0];

		array_splice($Parameters, 0, 1);

		if($this->FindAllBy($Expression, $Parameters, $Items)) {
			foreach($Items as $Item) {
				$Item->{'Set'.$Column}($Value);
			}

			return true;
		}
		else {
			return false;
		}
	}

	/* Insert methods */

	public function InsertByArray(array $Data) {
		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		foreach($Data as $Column => $Value) {
			$Query->AddColumn('`'.$Column.'`');

			$Query->AddValue($Value);
		}

		return $this->InsertByQuery($Query);
	}

	public function InsertByQuery(DbInsertQuery $Query) {
		$Result = $this->Connection->Insert($Query);

		if($Result === false) {
			return false;
		}
		else {
			$LastInsertId =  $this->Connection->LastInsertId();

			$this->OnInserted($LastInsertId);

			return $LastInsertId;
		}
	}

	public abstract function OnInserted($LastInsertId);

	/* InsertUpdate methods */

	public function InsertUpdateByQuery(DbInsertUpdateQuery $Query) {
		$Result = $this->Connection->InsertUpdate($Query);

		if($Result === false) {
			return false;
		}
		else {
			return $this->Connection->LastInsertId();
		}
	}

	/* Update methods */

	public function UpdateByArray(DbCondition $Conditions = null, array $Changes) {
		$Query = new \Framework\Newnorth\DbUpdateQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = $Conditions;

		foreach($Changes as $Column => $Value) {
			$Query->AddChange('`'.$Column.'`', $Value);
		}

		return $this->Connection->Update($Query);
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

	/* Delete methods */

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
		else if($this->Connection->AffectedRows() === 1) {
			$this->OnDeleted($Item);

			return true;
		}
		else {
			return false;
		}
	}

	private function DeleteBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		if($this->GetDataMembers($DataMembers)) {
			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->DataManager->Table.'`.`'.$DataMember->Name.'`';

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

			$Result = call_user_func_array([$this, 'DeleteByArray'], $Parameters);

			return true;
		}
		else {
			return false;
		}
	}

	public function DeleteByArray(array $Conditions = null) {
		$Items = $this->FindAllByArray($Conditions);

		if(0 < count($Items)) {
			$Result = true;

			foreach($Items as $Item) {
				$Result = $this->Delete($Item) && $Result;
			}

			return $Result;
		}
		else {
			return false;
		}
	}

	public function DeleteByQuery(DbDeleteQuery $Query) {
		$Items = $this->FindAllByQuery($Query);

		if(0 < count($Items)) {
			$Result = true;

			foreach($Items as $Item) {
				$Result = $this->Delete($Item) && $Result;
			}

			return $Result;
		}
		else {
			return false;
		}
	}

	public abstract function OnDeleted($Item);

	/* Find methods */

	private function FindBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		if($this->GetDataMembers($DataMembers)) {
			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->DataManager->Table.'`.`'.$DataMember->Name.'`';

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

			$Result = call_user_func_array([$this, 'FindByArray'], $Parameters);

			return true;
		}
		else {
			return false;
		}
	}

	public function FindByArray(array $Conditions = null, $SortColumn = null, $SortOrder = null) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn('`'.$this->Table.'`.*');

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddColumn(
					'`'.$DataMember->DataManager->Table.'`.`'.$DataMember->DataMember->Name.'`',
					$DataMember->Name
				);
			}
		}

		$Query->AddSource($this);

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddSource(
					$DataMember->DataManager,
					null,
					$DataMember->Key->IsNullable ? DB_LEFTJOIN : DB_INNERJOIN,
					new \Framework\Newnorth\DbEqualTo(
						$DataMember->DataManager->PrimaryKey,
						$DataMember->Key
					)
				);
			}
		}

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

	/* FindAll methods */

	public function FindAll(array $Sorts = null, $MaxRows = null, $FirstRow = 0) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn('`'.$this->Table.'`.*');

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddColumn(
					$DataMember->DataMember,
					$DataMember->Name
				);
			}
		}

		$Query->AddSource($this);

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddSource(
					$DataMember->DataManager,
					null,
					DB_INNERJOIN,
					new \Framework\Newnorth\DbEqualTo(
						$DataMember->DataManager->PrimaryKey,
						$DataMember->Key
					)
				);
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

	private function FindAllBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		if($this->GetDataMembers($DataMembers)) {
			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->DataManager->Table.'`.`'.$DataMember->Name.'`';

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

			$Result = call_user_func_array([$this, 'FindAllByArray'], $Parameters);

			return true;
		}
		else {
			return false;
		}
	}

	public function FindAllByArray(array $Conditions = null, array $Sorts = null, $MaxRows = null, $FirstRow = 0) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn('`'.$this->Table.'`.*');

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddColumn(
					$DataMember->DataMember,
					$DataMember->Name
				);
			}
		}

		$Query->AddSource($this);

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof ReferenceDataMember) {
				$Query->AddSource(
					$DataMember->DataManager,
					null,
					$DataMember->Key->IsNullable ? DB_LEFTJOIN : DB_INNERJOIN,
					new \Framework\Newnorth\DbEqualTo(
						$DataMember->DataManager->PrimaryKey,
						$DataMember->Key
					)
				);
			}
		}

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

	/* Count methods */

	private function CountBy($Expression, $Parameters, &$Result) {
		$DataMembers = explode('And', $Expression);

		if($this->GetDataMembers($DataMembers)) {
			for($I = 0; $I < count($DataMembers); ++$I) {
				$DataMember = $DataMembers[$I];

				$Column = '`'.$DataMember->DataManager->Table.'`.`'.$DataMember->Name.'`';

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

			$Result = call_user_func_array([$this, 'CountByArray'], $Parameters);

			return true;
		}
		else {
			return false;
		}
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

	public function CountByQuery(DbSelectQuery $Query) {
		return $this->Connection->Count($Query);
	}
}
?>