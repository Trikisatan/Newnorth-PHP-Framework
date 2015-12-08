<?
namespace Framework\MySQL;

use \Framework\Newnorth\ConfigException;
use \Framework\Newnorth\DbConnection;
use \Framework\Newnorth\DbCondition;
use \Framework\Newnorth\DbAnd;
use \Framework\Newnorth\DbOr;
use \Framework\Newnorth\DbEqualTo;
use \Framework\Newnorth\DbLike;
use \Framework\Newnorth\DbContains;
use \Framework\Newnorth\DbStartsWith;
use \Framework\Newnorth\DbEndsWith;
use \Framework\Newnorth\DbGreaterThan;
use \Framework\Newnorth\DbLessThan;
use \Framework\Newnorth\DbExpression;
use \Framework\Newnorth\DbArray;
use \Framework\Newnorth\DbBool;
use \Framework\Newnorth\DbColumn;
use \Framework\Newnorth\DbFloat;
use \Framework\Newnorth\DbInt;
use \Framework\Newnorth\DbString;
use \Framework\Newnorth\DbNull;
use \Framework\Newnorth\DbInsertQuery;
use \Framework\Newnorth\DbUpdateQuery;
use \Framework\Newnorth\DbDeleteQuery;
use \Framework\Newnorth\DbSelectQuery;

class Connection extends DbConnection {
	/* Instance variables */

	private $Data;

	private $Base;

	/* Magic methods */

	public function __construct(Array $Data) {
		$this->Data = $Data;

		$this->Base = @new \mysqli(
			$this->Data['Hostname'],
			$this->Data['Username'],
			$this->Data['Password'],
			$this->Data['Database']
		);

		if($this->Base->connect_errno !== 0) {
			throw new ConfigException(
				'Unable to connect to MySQL.',
				[
					'ErrorNumber' => $this->Base->connect_errno,
					'ErrorMessage' => $this->Base->connect_error,
				]
			);
		}

		$this->Base->set_charset($this->Data['CharSet']);
	}

	/* Instance methods */

	// TODO: Add support for using a select query as an input.
	// TODO: Add support for IGNORE.
	public function Insert(DbInsertQuery $Query, $Execute = true) {
		$Query =
			'INSERT INTO '.$Query->Source.
			$this->Insert_ProcessColumns($Query->Columns).
			$this->Insert_ProcessValues($Query->Values);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function Insert_ProcessColumns($Columns) {
		$Count = count($Columns);

		if($Count === 0) {
			return '';
		}

		$Sql = $this->ProcessExpression»DbColumn($Columns[0]);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression»DbColumn($Columns[$I]);
		}

		return ' ('.$Sql.')';
	}

	// TODO: Add support for multiple rows per insert.
	private function Insert_ProcessValues($Values) {
		$Count = count($Values);

		if($Count === 0) {
			return '';
		}

		$Sql = $this->ProcessExpression($Values[0]);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression($Values[$I]);
		}

		return ' VALUES ('.$Sql.')';
	}

	// TODO: Add support for using a select query as an input.
	public function InsertUpdate(\Framework\Newnorth\DbInsertUpdateQuery $Query, $Execute = true) {
		$Query =
			'INSERT INTO '.$Query->Source.
			$this->InsertUpdate_ProcessColumns($Query->Columns).
			$this->InsertUpdate_ProcessValues($Query->Values).
			$this->InsertUpdate_ProcessUpdates($Query->Updates);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function InsertUpdate_ProcessColumns($Columns) {
		$Count = count($Columns);

		if($Count === 0) {
			return '';
		}

		$Sql = $this->ProcessExpression»DbColumn($Columns[0]);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression»DbColumn($Columns[$I]);
		}

		return ' ('.$Sql.')';
	}

	// TODO: Add support for multiple rows per insert.
	private function InsertUpdate_ProcessValues($Values) {
		$Count = count($Values);

		if($Count === 0) {
			return '';
		}

		$Sql = $this->ProcessExpression($Values[0]);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression($Values[$I]);
		}

		return ' VALUES ('.$Sql.')';
	}

	private function InsertUpdate_ProcessUpdates($Updates) {
		$Count = count($Updates);

		if($Count === 0) {
			return '';
		}
		else {
			$Sql = $this->ProcessExpression»DbColumn($Updates[0]->Column).'='.$this->ProcessExpression($Updates[0]->Value);

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ', '.$this->ProcessExpression»DbColumn($Updates[$I]->Column).'='.$this->ProcessExpression($Updates[$I]->Value);
			}

			return ' ON DUPLICATE KEY UPDATE '.$Sql;
		}
	}

	public function Update(DbUpdateQuery $Query, $Execute = true) {
		$Query =
			'UPDATE '.$this->ProcessSources($Query->Sources).
			' SET '.$this->Update_ProcessChanges($Query->Changes).
			$this->Update_ProcessConditions($Query->Conditions);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function Update_ProcessChanges($Changes) {
		$Count = count($Changes);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No changes specified.');
		}

		$Sql = $this->ProcessExpression»DbColumn($Changes[0]->Column).'='.$this->ProcessExpression($Changes[0]->Value);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression»DbColumn($Changes[$I]->Column).'='.$this->ProcessExpression($Changes[$I]->Value);
		}

		return $Sql;
	}

	private function Update_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			$Condition = $this->ProcessCondition($Condition);

			if($Condition === null) {
				return '';
			}
			else {
				return ' WHERE '.$Condition;
			}
		}
	}

	public function Delete(DbDeleteQuery $Query, $Execute = true) {
		$Query =
			'DELETE'.$this->Delete_ProcessTargets($Query->Targets).
			' FROM '.$this->ProcessSources($Query->Sources).
			$this->Delete_ProcessConditions($Query->Conditions);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function Delete_ProcessTargets($Targets) {
		$Count = count($Targets);

		if($Count === 0) {
			return '';
		}

		$Sql = '`'.$Targets[0].'`';

		for($I = 1; $I < $Count; ++$I) {
			$Sql = ', `'.$Targets[$I].'`';
		}

		return $Sql;
	}

	private function Delete_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			$Condition = $this->ProcessCondition($Condition);

			if($Condition === null) {
				return '';
			}
			else {
				return ' WHERE '.$Condition;
			}
		}
	}

	public function Find(DbSelectQuery $Query, $Execute = true) {
		$Query =
			'SELECT '.$this->Find_ProcessColumns($Query->Columns).
			' FROM '.$this->ProcessSources($Query->Sources).
			$this->Find_ProcessConditions($Query->Conditions).
			$this->Find_ProcessGroups($Query->Groups).
			$this->Find_ProcessSorts($Query->Sorts).
			' LIMIT 1';

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function Find_ProcessColumns($Columns) {
		if(count($Columns) === 0) {
			return '*';
		}
		else {
			$I = 0;

			foreach($Columns as $Column) {
				$Expression = $this->ProcessExpression($Column->Expression);

				$Alias = $Column->Alias;

				if($I === 0) {
					if($Column->Alias === null) {
						$Sql = $Expression;
					}
					else {
						$Sql = $Expression.' AS `'.$Alias.'`';
					}
				}
				else {
					if($Column->Alias === null) {
						$Sql .= ', '.$Expression;
					}
					else {
						$Sql .= ', '.$Expression.' AS `'.$Alias.'`';
					}
				}

				++$I;
			}

			return $Sql;
		}
	}

	private function Find_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			$Condition = $this->ProcessCondition($Condition);

			if($Condition === null) {
				return '';
			}
			else {
				return ' WHERE '.$Condition;
			}
		}
	}

	private function Find_ProcessGroups(array $Groups) {
		$Count = count($Groups);

		if(0 < $Count) {
			$Sql = $this->ProcessExpression($Groups[0]);

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ', '.$this->ProcessExpression($Groups[$I]);
			}

			return ' GROUP BY '.$Sql;
		}
		else {
			return '';
		}
	}

	private function Find_ProcessSorts(array $Sorts) {
		$Count = count($Sorts);

		if(0 < $Count) {
			$Sql = $this->ProcessExpression($Sorts[0]->Expression).' '.($Sorts[0]->Direction === DB_ASC ? 'ASC' : 'DESC');

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ', '.$this->ProcessExpression($Sorts[$I]->Expression).' '.($Sorts[$I]->Direction === DB_ASC ? 'ASC' : 'DESC');
			}

			return ' ORDER BY '.$Sql;
		}
		else {
			return '';
		}
	}

	public function FindAll(DbSelectQuery $Query, $Execute = true) {
		$Query =
			'SELECT '.$this->FindAll_ProcessColumns($Query->Columns).
			' FROM '.$this->ProcessSources($Query->Sources).
			$this->FindAll_ProcessConditions($Query->Conditions).
			$this->FindAll_ProcessGroups($Query->Groups).
			$this->FindAll_ProcessSorts($Query->Sorts).
			$this->FindAll_ProcessLimit($Query->MaxRows, $Query->FirstRow);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	private function FindAll_ProcessColumns($Columns) {
		if(count($Columns) === 0) {
			return '*';
		}
		else {
			$I = 0;

			foreach($Columns as $Column) {
				$Expression = $this->ProcessExpression($Column->Expression);

				$Alias = $Column->Alias;

				if($I === 0) {
					if($Column->Alias === null) {
						$Sql = $Expression;
					}
					else {
						$Sql = $Expression.' AS `'.$Alias.'`';
					}
				}
				else {
					if($Column->Alias === null) {
						$Sql .= ', '.$Expression;
					}
					else {
						$Sql .= ', '.$Expression.' AS `'.$Alias.'`';
					}
				}

				++$I;
			}

			return $Sql;
		}
	}

	private function FindAll_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			$Condition = $this->ProcessCondition($Condition);

			if($Condition === null) {
				return '';
			}
			else {
				return ' WHERE '.$Condition;
			}
		}
	}

	private function FindAll_ProcessGroups(array $Groups) {
		$Count = count($Groups);

		if(0 < $Count) {
			$Sql = $this->ProcessExpression($Groups[0]);

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ', '.$this->ProcessExpression($Groups[$I]);
			}

			return ' GROUP BY '.$Sql;
		}
		else {
			return '';
		}
	}

	private function FindAll_ProcessSorts(array $Sorts) {
		$Count = count($Sorts);

		if(0 < $Count) {
			$Sql = $this->ProcessExpression($Sorts[0]->Expression).' '.($Sorts[0]->Direction === DB_ASC ? 'ASC' : 'DESC');

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ', '.$this->ProcessExpression($Sorts[$I]->Expression).' '.($Sorts[$I]->Direction === DB_ASC ? 'ASC' : 'DESC');
			}

			return ' ORDER BY '.$Sql;
		}
		else {
			return '';
		}
	}

	private function FindAll_ProcessLimit($MaxRows, $FirstRow) {
		if(0 < $MaxRows) {
			if(0 < $FirstRow) {
				return ' LIMIT '.$FirstRow.', '.$MaxRows;
			}
			else {
				return ' LIMIT '.$MaxRows;
			}
		}
		else {
			return '';
		}
	}

	public function Count(DbSelectQuery $Query, $Execute = true) {
		$Query =
			'SELECT COUNT(*)'.
			' FROM '.$this->ProcessSources($Query->Sources).
			$this->Count_ProcessConditions($Query->Conditions);

		if($Execute) {
			$Result = $this->Query($Query);

			if($Result instanceof Result) {
				return $Result->Fetch() ? $Result->GetInt(0) : 0;
			}
			else {
				return $Result;
			}
		}
		else {
			return $Query;
		}
	}

	private function Count_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			$Condition = $this->ProcessCondition($Condition);

			if($Condition === null) {
				return '';
			}
			else {
				return ' WHERE '.$Condition;
			}
		}
	}

	public function Lock($Sources) {
		$Query = null;

		foreach($Sources as $Source => $LockType) {
			if($Query === null) {
				$Query = '`'.$Source.'` '.$LockType;
			}
			else {
				$Query .= ', `'.$Source.'` '.$LockType;
			}
		}

		$this->Query('LOCK TABLES '.$Query);
	}

	public function Unlock($Sources) {
		$this->Query('UNLOCK TABLES');
	}

	private function Query($QueryString) {
		$Result = $this->Base->query($QueryString);

		if($Result === false) {
			throw new \Framework\Newnorth\RuntimeException(
				'MySQL error when executing query.',
				[
					'Error number' => $this->Base->errno,
					'Error messages' => $this->Base->error,
					'Query string' => $QueryString,
				]
			);
		}
		else if($Result === true) {
			return true;
		}
		else {
			return new Result($Result);
		}
	}

	public function EscapeString($String) {
		return $this->Base->real_escape_string($String);
	}

	public function LastInsertId() {
		return $this->Base->insert_id;
	}

	public function AffectedRows() {
		return $this->Base->affected_rows;
	}

	public function FoundRows() {
		$Result = $this->Query('SELECT FOUND_ROWS()');
		return $Result->Fetch() ? $Result->GetInt(0) : 0;
	}

	private function ProcessSources($Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$I = 0;

			foreach($Sources as $Source) {
				if($I === 0) {
					if($Source->Alias === null) {
						$Sql = $Source->Expression;
					}
					else {
						$Sql = $Source->Expression.' AS `'.$Source->Alias.'`';
					}
				}
				else {
					$Sql .= ' '.$this->ProcessSource($Source);
				}

				++$I;
			}

			return $Sql;
		}
	}

	private function ProcessSource(\Framework\Newnorth\DbSource $Source) {
		switch($Source->Method) {
			case null: {
				$Sql = ', ';

				if($Source->Conditions !== null) {
					throw new \Framework\Newnorth\RuntimeException('Conditions not available when not using a join method.');
				}

				break;
			}
			case DB_INNERJOIN: {
				$Sql = 'INNER JOIN';

				if($Source->Conditions === null) {
					throw new \Framework\Newnorth\RuntimeException('Conditions required when using a join method.');
				}

				break;
			}
			case DB_LEFTJOIN: {
				$Sql = 'LEFT JOIN';

				if($Source->Conditions === null) {
					throw new \Framework\Newnorth\RuntimeException('Conditions required when using a join method.');
				}

				break;
			}
			default: {
				throw new \Framework\Newnorth\RuntimeException('Join method not recognized.');
			}
		}

		$Sql .= ' '.$Source->Expression;

		if($Source->Alias !== null) {
			$Sql .= ' AS `'.$Source->Alias.'`';
		}

		if($Source->Conditions !== null) {
			$Sql .= ' ON '.$this->ProcessCondition($Source->Conditions);
		}

		return $Sql;
	}

	private function ProcessCondition(DbCondition $Condition) {
		if($Condition instanceof DbAnd) {
			return $this->ProcessCondition»DbAnd($Condition);
		}
		else if($Condition instanceof DbOr) {
			return $this->ProcessCondition»DbOr($Condition);
		}
		else if($Condition instanceof DbEqualTo) {
			return $this->ProcessCondition»DbEqualTo($Condition);
		}
		else if($Condition instanceof \Framework\Newnorth\DbNotEqualTo) {
			return $this->ProcessCondition»DbNotEqualTo($Condition);
		}
		else if($Condition instanceof DbLike) {
			return $this->ProcessCondition»DbLike($Condition);
		}
		else if($Condition instanceof DbContains) {
			return $this->ProcessCondition»DbContains($Condition);
		}
		else if($Condition instanceof DbStartsWith) {
			return $this->ProcessCondition»DbStartsWith($Condition);
		}
		else if($Condition instanceof DbEndsWith) {
			return $this->ProcessCondition»DbEndsWith($Condition);
		}
		else if($Condition instanceof DbGreaterThan) {
			return $this->ProcessCondition»DbGreaterThan($Condition);
		}
		else if($Condition instanceof DbLessThan) {
			return $this->ProcessCondition»DbLessThan($Condition);
		}
		else if($Condition instanceof \Framework\Newnorth\DbIn) {
			return $this->ProcessCondition»DbIn($Condition);
		}
	}

	private function ProcessCondition»DbAnd(DbAnd $ConditionGroup) {
		$Count = count($ConditionGroup->Conditions);

		if(0 < $Count) {
			$String = $this->ProcessCondition($ConditionGroup->Conditions[0]);

			for($I = 1; $I < $Count; ++$I) {
				$String .= ' AND '.$this->ProcessCondition($ConditionGroup->Conditions[$I]);
			}

			return '('.$String.')';
		}
		else {
			return null;
		}
	}

	private function ProcessCondition»DbOr(DbOr $ConditionGroup) {
		$Count = count($ConditionGroup->Conditions);

		if(0 < $Count) {
			$String = $this->ProcessCondition($ConditionGroup->Conditions[0]);

			for($I = 1; $I < $Count; ++$I) {
				$String .= ' OR '.$this->ProcessCondition($ConditionGroup->Conditions[$I]);
			}

			return '('.$String.')';
		}
		else {
			return null;
		}
	}

	private function ProcessCondition»DbEqualTo(DbEqualTo $Condition) {
		if($Condition->A instanceof DbNull) {
			if($Condition->B instanceof DbNull) {
				return 'NULL IS NULL';
			}
			else {
				return $this->ProcessExpression($Condition->B).' IS NULL';
			}
		}
		else {
			if($Condition->B instanceof DbNull) {
				return $this->ProcessExpression($Condition->A).' IS NULL';
			}
			else {
				return $this->ProcessExpression($Condition->A).' = '.$this->ProcessExpression($Condition->B);
			}
		}
	}

	private function ProcessCondition»DbNotEqualTo(\Framework\Newnorth\DbNotEqualTo $Condition) {
		if($Condition->A instanceof DbNull) {
			if($Condition->B instanceof DbNull) {
				return 'NULL IS NOT NULL';
			}
			else {
				return $this->ProcessExpression($Condition->B).' IS NOT NULL';
			}
		}
		else {
			if($Condition->B instanceof DbNull) {
				return $this->ProcessExpression($Condition->A).' IS NOT NULL';
			}
			else {
				return $this->ProcessExpression($Condition->A).' != '.$this->ProcessExpression($Condition->B);
			}
		}
	}

	private function ProcessCondition»DbLike(DbLike $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition»DbContains(DbContains $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT("%", '.$this->ProcessExpression($Condition->B).', "%")';
	}

	private function ProcessCondition»DbStartsWith(DbStartsWith $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT('.$this->ProcessExpression($Condition->B).', "%")';
	}

	private function ProcessCondition»DbEndsWith(DbEndsWith $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT("%", '.$this->ProcessExpression($Condition->B).')';
	}

	private function ProcessCondition»DbGreaterThan(DbGreaterThan $Condition) {
		return $this->ProcessExpression($Condition->A).' > '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition»DbLessThan(DbLessThan $Condition) {
		return $this->ProcessExpression($Condition->A).' < '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition»DbIn(\Framework\Newnorth\DbIn $Condition) {
		$Count = count($Condition->B);

		if(0 < $Count) {
			$B = $this->ProcessExpression($Condition->B[0]);

			for($I = 1; $I < $Count; ++$I) {
				$B .= ', '.$this->ProcessExpression($Condition->B[$I]);
			}

			return $this->ProcessExpression($Condition->A).' IN ('.$B.')';
		}
		else {
			throw new \exception('Empty in-condition.');
		}
	}

	private function ProcessExpression(DbExpression $Expression) {
		if($Expression instanceof DbArray) {
			return $this->ProcessExpression»DbArray($Expression);
		}
		else if($Expression instanceof DbBool) {
			return $this->ProcessExpression»DbBool($Expression);
		}
		else if($Expression instanceof DbColumn) {
			return $this->ProcessExpression»DbColumn($Expression);
		}
		else if($Expression instanceof DbFloat) {
			return $this->ProcessExpression»DbFloat($Expression);
		}
		else if($Expression instanceof DbInt) {
			return $this->ProcessExpression»DbInt($Expression);
		}
		else if($Expression instanceof DbString) {
			return $this->ProcessExpression»DbString($Expression);
		}
		else if($Expression instanceof DbNull) {
			return $this->ProcessExpression»DbNull($Expression);
		}
		else {
			return $this->ProcessExpression»DbExpression($Expression);
		}
	}

	private function ProcessExpression»DbExpression(DbExpression $Expression) {
		return $Expression->Value;
	}

	private function ProcessExpression»DbArray(DbArray $Expression) {
		// The array is never empty.

		$Sql = $this->ProcessExpression($Expression->Value[0]);

		$Count = count($Expression->Value);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ','.$this->ProcessExpression($Expression->Value[$I]);
		}

		return '('.$Sql.')';
	}

	private function ProcessExpression»DbBool(DbBool $Expression) {
		return $Expression->Value ? MYSQL_TRUE : MYSQL_FALSE;
	}

	private function ProcessExpression»DbColumn(DbColumn $Expression) {
		return '`'.implode('`.`', $Expression->Value).'`';
	}

	private function ProcessExpression»DbFloat(DbFloat $Expression) {
		return (string)$Expression->Value;
	}

	private function ProcessExpression»DbInt(DbInt $Expression) {
		return (string)$Expression->Value;
	}

	private function ProcessExpression»DbString(DbString $Expression) {
		return '"'.$this->EscapeString($Expression->Value).'"';
	}

	private function ProcessExpression»DbNull(DbNull $Expression) {
		return 'NULL';
	}
}
?>