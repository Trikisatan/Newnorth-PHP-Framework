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
	public function Insert(DbInsertQuery $Query, $Execute = true) {
		$Query =
			'INSERT INTO `'.$Query->Source.'`'.
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

		$Sql = $this->ProcessExpression_DbColumn($Columns[0]);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression_DbColumn($Columns[$I]);
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

	public function Update(DbUpdateQuery $Query, $Execute = true) {
		$Query =
			'UPDATE '.$this->Update_ProcessSources($Query->Sources).
			' SET '.$this->Update_ProcessChanges($Query->Changes).
			$this->Update_ProcessConditions($Query->Conditions);

		if($Execute) {
			return $this->Query($Query);
		}
		else {
			return $Query;
		}
	}

	// TODO: Add support for join method.
	// TODO: Add support for join conditions.
	private function Update_ProcessSources($Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$Sql = '`'.$Sources[0]->Expression.'`';

			if($Sources[0]->Alias !== null) {
				$Sql .= ' AS `'.$Sources[0]->Alias.'`';
			}

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ' '.$this->ProcessSource($Sources[$I]);
			}

			return $Sql;
		}
	}

	private function Update_ProcessChanges($Changes) {
		$Count = count($Changes);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No changes specified.');
		}

		$Sql = $this->ProcessExpression_DbColumn($Changes[0]->Column).'='.$this->ProcessExpression($Changes[0]->Value);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ', '.$this->ProcessExpression_DbColumn($Changes[$I]->Column).'='.$this->ProcessExpression($Changes[$I]->Value);
		}

		return $Sql;
	}

	private function Update_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			return ' WHERE '.$this->ProcessCondition($Condition);
		}
	}

	public function Delete(DbDeleteQuery $Query, $Execute = true) {
		$Query =
			'DELETE'.$this->Delete_ProcessTargets($Query->Targets).
			' FROM '.$this->Delete_ProcessSources($Query->Sources).
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

	// TODO: Add support for join method.
	// TODO: Add support for join conditions.
	private function Delete_ProcessSources($Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$Sql = '`'.$Sources[0]->Expression.'`';

			if($Sources[0]->Alias !== null) {
				$Sql .= ' AS `'.$Sources[0]->Alias.'`';
			}

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ' '.$this->ProcessSource($Sources[$I]);
			}

			return $Sql;
		}
	}

	private function Delete_ProcessConditions(DbCondition $Condition = null) {
		if($Condition === null) {
			return '';
		}
		else {
			return ' WHERE '.$this->ProcessCondition($Condition);
		}
	}

	public function Find(DbSelectQuery $Query, $Execute = true) {
		$Query =
			'SELECT '.$this->Find_ProcessColumns($Query->Columns).
			' FROM '.$this->Find_ProcessSources($Query->Sources).
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
		$Sql = '*';

		$Count = count($Columns);

		for($I = 0; $I < $Count; ++$I) {
			$Column = $Columns[$I];

			$Sql .= ', '.$this->ProcessExpression($Column->Expression);

			if($Column->Alias !== null) {
				$Sql .= ' AS `'.$Column->Alias.'`';
			}
		}

		if(isset($Sql[1])) {
			$Sql = substr($Sql, 3);
		}

		return $Sql;
	}

	// TODO: Add support for join method.
	// TODO: Add support for join conditions.
	private function Find_ProcessSources(array $Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$Sql = '`'.$Sources[0]->Expression.'`';

			if($Sources[0]->Alias !== null) {
				$Sql .= ' AS `'.$Sources[0]->Alias.'`';
			}

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ' '.$this->ProcessSource($Sources[$I]);
			}

			return $Sql;
		}
	}

	private function Find_ProcessConditions(DbCondition $Conditions = null) {
		if($Conditions === null) {
			return '';
		}
		else {
			return ' WHERE '.$this->ProcessCondition($Conditions);
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
			' FROM '.$this->FindAll_ProcessSources($Query->Sources).
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
		$Sql = '*';

		$Count = count($Columns);

		for($I = 0; $I < $Count; ++$I) {
			$Column = $Columns[$I];

			$Sql .= ', '.$this->ProcessExpression($Column->Expression);

			if($Column->Alias !== null) {
				$Sql .= ' AS `'.$Column->Alias.'`';
			}
		}

		if(isset($Sql[1])) {
			$Sql = substr($Sql, 3);
		}

		return $Sql;
	}

	// TODO: Add support for join method.
	// TODO: Add support for join conditions.
	private function FindAll_ProcessSources(array $Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$Sql = '`'.$Sources[0]->Expression.'`';

			if($Sources[0]->Alias !== null) {
				$Sql .= ' AS `'.$Sources[0]->Alias.'`';
			}

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ' '.$this->ProcessSource($Sources[$I]);
			}

			return $Sql;
		}
	}

	private function FindAll_ProcessConditions(DbCondition $Conditions = null) {
		if($Conditions === null) {
			return '';
		}
		else {
			return ' WHERE '.$this->ProcessCondition($Conditions);
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
			' FROM '.$this->Count_ProcessSources($Query->Sources).
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

	// TODO: Add support for join method.
	// TODO: Add support for join conditions.
	private function Count_ProcessSources($Sources) {
		$Count = count($Sources);

		if($Count === 0) {
			throw new \Framework\Newnorth\RuntimeException('No source specified.');
		}
		else {
			$Sql = '`'.$Sources[0]->Expression.'`';

			if($Sources[0]->Alias !== null) {
				$Sql .= ' AS `'.$Sources[0]->Alias.'`';
			}

			for($I = 1; $I < $Count; ++$I) {
				$Sql .= ' '.$this->ProcessSource($Sources[$I]);
			}

			return $Sql;
		}
	}

	private function Count_ProcessConditions(DbCondition $Conditions = null) {
		if($Conditions === null) {
			return '';
		}
		else {
			return ' WHERE '.$this->ProcessCondition($Conditions);
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
			trigger_error('MySQL error #'.$this->Base->errno.': '.$this->Base->error.'.', E_USER_ERROR);

			return false;
		}

		if($Result === true) {
			return true;
		}

		return new Result($Result);
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

		$Sql .= ' `'.$Source->Expression.'`';

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
			return $this->ProcessCondition_DbAnd($Condition);
		}
		else if($Condition instanceof DbOr) {
			return $this->ProcessCondition_DbOr($Condition);
		}
		else if($Condition instanceof DbEqualTo) {
			return $this->ProcessCondition_DbEqualTo($Condition);
		}
		else if($Condition instanceof DbLike) {
			return $this->ProcessCondition_DbLike($Condition);
		}
		else if($Condition instanceof DbContains) {
			return $this->ProcessCondition_DbContains($Condition);
		}
		else if($Condition instanceof DbStartsWith) {
			return $this->ProcessCondition_DbStartsWith($Condition);
		}
		else if($Condition instanceof DbEndsWith) {
			return $this->ProcessCondition_DbEndsWith($Condition);
		}
		else if($Condition instanceof DbGreaterThan) {
			return $this->ProcessCondition_DbGreaterThan($Condition);
		}
		else if($Condition instanceof DbLessThan) {
			return $this->ProcessCondition_DbLessThan($Condition);
		}
		else if($Condition instanceof \Framework\Newnorth\DbIn) {
			return $this->ProcessCondition_DbIn($Condition);
		}
	}

	private function ProcessCondition_DbAnd(DbAnd $ConditionGroup) {
		$Count = count($ConditionGroup->Conditions);

		if(0 < $Count) {
			$String = $this->ProcessCondition($ConditionGroup->Conditions[0]);

			for($I = 1; $I < $Count; ++$I) {
				$String .= ' AND '.$this->ProcessCondition($ConditionGroup->Conditions[$I]);
			}

			return '('.$String.')';
		}
		else {
			throw new \exception('Empty and-grouping.');
		}
	}

	private function ProcessCondition_DbOr(DbOr $ConditionGroup) {
		$Count = count($ConditionGroup->Conditions);

		if(0 < $Count) {
			$String = $this->ProcessCondition($ConditionGroup->Conditions[0]);

			for($I = 1; $I < $Count; ++$I) {
				$String .= ' OR '.$this->ProcessCondition($ConditionGroup->Conditions[$I]);
			}

			return '('.$String.')';
		}
		else {
			throw new \exception('Empty or-grouping.');
		}
	}

	private function ProcessCondition_DbEqualTo(DbEqualTo $Condition) {
		return $this->ProcessExpression($Condition->A).' = '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition_DbLike(DbLike $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition_DbContains(DbContains $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT("%", '.$this->ProcessExpression($Condition->B).', "%")';
	}

	private function ProcessCondition_DbStartsWith(DbStartsWith $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT('.$this->ProcessExpression($Condition->B).', "%")';
	}

	private function ProcessCondition_DbEndsWith(DbEndsWith $Condition) {
		return $this->ProcessExpression($Condition->A).' LIKE CONCAT("%", '.$this->ProcessExpression($Condition->B).')';
	}

	private function ProcessCondition_DbGreaterThan(DbGreaterThan $Condition) {
		return $this->ProcessExpression($Condition->A).' > '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition_DbLessThan(DbLessThan $Condition) {
		return $this->ProcessExpression($Condition->A).' < '.$this->ProcessExpression($Condition->B);
	}

	private function ProcessCondition_DbIn(\Framework\Newnorth\DbIn $Condition) {
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
			return $this->ProcessExpression_DbArray($Expression);
		}
		else if($Expression instanceof DbBool) {
			return $this->ProcessExpression_DbBool($Expression);
		}
		else if($Expression instanceof DbColumn) {
			return $this->ProcessExpression_DbColumn($Expression);
		}
		else if($Expression instanceof DbFloat) {
			return $this->ProcessExpression_DbFloat($Expression);
		}
		else if($Expression instanceof DbInt) {
			return $this->ProcessExpression_DbInt($Expression);
		}
		else if($Expression instanceof DbString) {
			return $this->ProcessExpression_DbString($Expression);
		}
		else if($Expression instanceof DbNull) {
			return $this->ProcessExpression_DbNull($Expression);
		}
		else {
			return $this->ProcessExpression_DbExpression($Expression);
		}
	}

	private function ProcessExpression_DbExpression(DbExpression $Expression) {
		return $Expression->Value;
	}

	private function ProcessExpression_DbArray(DbArray $Expression) {
		// The array is never empty.

		$Sql = $this->ProcessExpression($Expression->Value[0]);

		$Count = count($Expression->Value);

		for($I = 1; $I < $Count; ++$I) {
			$Sql .= ','.$this->ProcessExpression($Expression->Value[$I]);
		}

		return '('.$Sql.')';
	}

	private function ProcessExpression_DbBool(DbBool $Expression) {
		return $Expression->Value ? MYSQL_TRUE : MYSQL_FALSE;
	}

	private function ProcessExpression_DbColumn(DbColumn $Expression) {
		return '`'.implode('`.`', $Expression->Value).'`';
	}

	private function ProcessExpression_DbFloat(DbFloat $Expression) {
		return (string)$Expression->Value;
	}

	private function ProcessExpression_DbInt(DbInt $Expression) {
		return (string)$Expression->Value;
	}

	private function ProcessExpression_DbString(DbString $Expression) {
		return '"'.$this->EscapeString($Expression->Value).'"';
	}

	private function ProcessExpression_DbNull(DbNull $Expression) {
		return 'NULL';
	}
}
?>