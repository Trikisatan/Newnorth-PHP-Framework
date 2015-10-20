<?
namespace Framework\Newnorth;

abstract class AStandardDataManager extends ADataManager {
	/* Instance methods */

	public function CreateSelectQuery() {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn($this);

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof \Framework\Newnorth\ReferenceDataMember) {
				$Query->AddColumn(
					'`'.$DataMember->Alias.'`.`'.$DataMember->ForeignDataMember->Alias.'`',
					$DataMember->Alias
				);
			}
		}

		$Query->AddSource($this);

		foreach($this->DataMembers as $DataMember) {
			if($DataMember instanceof \Framework\Newnorth\ReferenceDataMember) {
				$Conditions = new \Framework\Newnorth\DbAnd();

				for($I = 0; $I < $DataMember->KeyCount; ++$I) {
					$ForeignColumn = '`'.$DataMember->Alias.'`.`'.$DataMember->ForeignKeys[$I]->Alias.'`';

					if($DataMember->LocalKeys[$I] instanceof \Framework\Newnorth\ADataMember) {
						$Conditions->EqualTo($ForeignColumn, $DataMember->LocalKeys[$I]);
					}
					else {
						$Conditions->EqualTo($ForeignColumn, $DataMember->ForeignKeys[$I]->ToDbExpression($DataMember->LocalKeys[$I]));
					}
				}

				if($DataMember->ForeignDataManager instanceof \Framework\Newnorth\ATranslationDataManager) {
					$Conditions->EqualTo('`'.$DataMember->Alias.'`.`Locale`', '"'.$GLOBALS['Parameters']['Locale'].'"');
				}

				$Query->AddSource(
					$DataMember->ForeignDataManager,
					$DataMember->Alias,
					$DataMember->IsRequired ? DB_INNERJOIN : DB_LEFTJOIN,
					$Conditions
				);
			}
		}

		return $Query;
	}

	/* Insert methods */

	public function InsertByArray(array $Data, $Source) {
		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		foreach($Data as $Column => $Value) {
			$Query->AddColumn('`'.$Column.'`');

			$Query->AddValue($Value);
		}

		return $this->InsertByQuery($Query, $Source);
	}

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

	/* Find methods */

	public function FindByArray(array $Conditions = null, $SortColumn = null, $SortOrder = null) {
		$Query = $this->CreateSelectQuery();

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

	/* FindAll methods */

	public function FindAll(array $Sorts = null, $MaxRows = 0, $FirstRow = 0) {
		$Query = $this->CreateSelectQuery();

		if($Sorts !== null) {
			foreach($Sorts as $Sort) {
				$Query->AddSort($Sort['Column'], $Sort['Order']);
			}
		}

		if(0 < $MaxRows) {
			$Query->Limit($MaxRows, $FirstRow);
		}

		return $this->FindAllByQuery($Query);
	}

	public function FindAllByArray(array $Conditions = null, array $Sorts = null, $MaxRows = 0, $FirstRow = 0) {
		$Query = $this->CreateSelectQuery();

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

		if(0 < $MaxRows) {
			$Query->Limit($MaxRows, $FirstRow);
		}

		return $this->FindAllByQuery($Query);
	}

	/* Count methods */

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