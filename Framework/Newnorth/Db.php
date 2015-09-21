<?
namespace Framework\Newnorth;

define('DB_ASC', 1);

define('DB_DESC', 2);

define('DB_INNERJOIN', 1);

define('DB_LEFTJOIN', 2);

abstract class DbConnection {
	/* Instance methods */

	public abstract function Insert(DbInsertQuery $Query, $Execute);

	public abstract function Update(DbUpdateQuery $Query, $Execute);

	public abstract function Delete(DbDeleteQuery $Query, $Execute);

	public abstract function Find(DbSelectQuery $Query, $Execute);

	public abstract function FindAll(DbSelectQuery $Query, $Execute);

	public abstract function Count(DbSelectQuery $Query, $Execute);

	public abstract function Lock($Sources);

	public abstract function Unlock($Sources);
}

abstract class DbResult {
	/* Instance variables */

	public $Rows;

	public $Row;

	/* Instance methods */

	public abstract function Fetch();

	public abstract function FetchAssoc();

	public abstract function GetProcessedRow();

	public abstract function GetBoolean($Column);

	public abstract function GetFloat($Column);

	public abstract function GetInt($Column);

	public abstract function GetString($Column);

	public abstract function IsFalse($Column);

	public abstract function IsTrue($Column);

	public abstract function IsNull($Column);
}

class DbSource {
	/* Instance variables */

	public $Expression;

	public $Alias;

	public $Method;

	public $Conditions;

	/* Magic methods */

	public function __construct($Expression, $Alias = null, $Method = null, $Conditions = null) {
		$this->Expression = $Expression;

		$this->Alias = $Alias;

		$this->Method = $Method;

		$this->Conditions = $Conditions;
	}
}

abstract class DbCondition {

}

abstract class DbConditionGroup extends DbCondition {
	/* Instance variables */

	public $Conditions = [];

	/* Instance methods */

	public function Add(DbCondition $Condition) {
		$this->Conditions[] = $Condition;

		return $this;
	}

	public function EqualTo($A, $B) {
		$this->Conditions[] = new DbEqualTo($A, $B);

		return $this;
	}

	public function NotEqualTo($A, $B) {
		$this->Conditions[] = new DbNotEqualTo($A, $B);

		return $this;
	}

	public function Like($A, $B) {
		$this->Conditions[] = new DbLike($A, $B);

		return $this;
	}

	public function Contains($A, $B) {
		$this->Conditions[] = new DbContains($A, $B);

		return $this;
	}

	public function StartsWith($A, $B) {
		$this->Conditions[] = new DbStartsWith($A, $B);

		return $this;
	}

	public function EndsWith($A, $B) {
		$this->Conditions[] = new DbEndsWith($A, $B);

		return $this;
	}

	public function GreaterThan($A, $B) {
		$this->Conditions[] = new DbGreaterThan($A, $B);

		return $this;
	}

	public function LessThan($A, $B) {
		$this->Conditions[] = new DbLessThan($A, $B);

		return $this;
	}

	public function In($A, array $B) {
		$this->Conditions[] = new DbIn($A, $B);

		return $this;
	}
}

class DbAnd extends DbConditionGroup {
	/* Magic methods */

	public function __construct($Conditions = []) {
		$this->Conditions = $Conditions;
	}
}

class DbOr extends DbConditionGroup {
	/* Magic methods */

	public function __construct($Conditions = []) {
		$this->Conditions = $Conditions;
	}
}

class DbEqualTo extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbNotEqualTo extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbLike extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbContains extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbStartsWith extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbEndsWith extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbGreaterThan extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbLessThan extends DbCondition {
	/* Instance variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbIn extends DbCondition {
	/* Instance variables */

	public $A;

	public $B = [];

	/* Magic methods */

	public function __construct($A, array $B) {
		$this->A = DbExpression::Parse($A);

		for($I = 0; $I < count($B); ++$I) {
			$this->B[] = DbExpression::Parse($B[$I]);
		}
	}
}

class DbExpression {
	/* Static methods */

	public static function Parse($Expression) {
		if($Expression === null) {
			return new DbNull();
		}
		else if($Expression instanceof DbExpression) {
			return $Expression;
		}
		else if($Expression instanceof \Framework\Newnorth\AValueDataMember) {
			return new DbColumn([
				$Expression->DataManager->Table,
				$Expression->Alias,
			]);
		}
		else if(is_array($Expression)) {
			return new DbArray($Expression);
		}
		else if(is_bool($Expression)) {
			return new DbBool($Expression);
		}
		else if(is_float($Expression)) {
			return new DbFloat($Expression);
		}
		else if(is_int($Expression)) {
			return new DbInt($Expression);
		}
		else {
			$Expression = (string)$Expression;

			$Length = strlen($Expression);

			if(2 <= $Length && $Expression[0] === $Expression[$Length - 1]) {
				if($Expression[0] === '`') {
					if(2 < $Length) {
						return new DbColumn(explode('`.`', substr($Expression, 1, -1)));
					}
					else {
						throw RuntimeException(
							'Invalid format on expression, could not be parsed as DbColumn.',
							[
								'Expression' => $Expression,
							]
						);
					}
				}
				else if($Expression[0] === '"') {
					if(2 < $Length) {
						return new DbString(substr($Expression, 1, -1));
					}
					else {
						return new DbString('');
					}
				}

				return new DbExpression($Expression);
			}
			else {
				return new DbExpression($Expression);
			}
		}
	}

	public static function ParseDbColumn($Expression) {
		$Length = strlen($Expression);

		if(2 < $Length && $Expression[0] === '`' && $Expression[$Length - 1] === '`') {
			return new DbColumn(explode('`.`', substr($Expression, 1, -1)));
		}
		else {
			throw RuntimeException(
				'Invalid format on expression, could not be parsed as DbColumn.',
				[
					'Expression' => $Expression,
				]
			);
		}
	}

	/* Instance variables */

	public $Value;

	/* Magic methods */

	public function __construct($Value) {
		$this->Value = $Value;
	}
}

class DbArray extends DbExpression {
	/* Magic methods */

	public function __construct(array $Value) {
		if(count($Value) === 0) {
			throw new Exception('Invalid value, empty array.');
		}

		for($I = 0; $I < count($Value); ++$I) {
			$Value[$I] = DbExpression::Parse($Value[$I]);
		}

		$this->Value = $Value;
	}
}

class DbBool extends DbExpression {

}

class DbColumn extends DbExpression {
	/* Magic methods */

	public function __construct(array $Value) {
		if(count($Value) === 0) {
			throw new Exception('Invalid value, empty array.');
		}

		$this->Value = $Value;
	}
}

class DbFloat extends DbExpression {

}

class DbInt extends DbExpression {

}

class DbNull extends DbExpression {
	/* Magic methods */

	public function __construct() {
		$this->Value = null;
	}
}

class DbString extends DbExpression {

}

class DbSort {
	/* Instance variables */

	public $Expression;

	public $Direction;

	/* Magic methods */

	public function __construct($Expression, $Direction = DB_ASC) {
		$this->Expression = DbExpression::Parse($Expression);

		$this->Direction = $Direction;
	}
}

class DbInsertQuery {
	/* Instance variables */

	public $Source = null;

	public $Columns = [];

	public $Values = [];

	/* Instance methods */

	public function AddColumn($Expression) {
		if($Expression instanceof DbColumn) {
			return $this->Columns[] = $Expression;
		}
		else {
			return $this->Columns[] = DbExpression::ParseDbColumn($Expression);
		}
	}

	public function AddValue($Expression) {
		if($Expression instanceof DbExpression) {
			return $this->Values[] = $Source;
		}
		else {
			return $this->Values[] = DbExpression::Parse($Expression);
		}
	}
}

class DbInsertUpdateQuery {
	/* Instance variables */

	public $Source = null;

	public $Columns = [];

	public $Values = [];

	public $Updates = [];

	/* Instance methods */

	public function AddColumn($Expression) {
		if($Expression instanceof DbColumn) {
			return $this->Columns[] = $Expression;
		}
		else {
			return $this->Columns[] = DbExpression::ParseDbColumn($Expression);
		}
	}

	public function AddValue($Expression) {
		if($Expression instanceof DbExpression) {
			return $this->Values[] = $Source;
		}
		else {
			return $this->Values[] = DbExpression::Parse($Expression);
		}
	}

	public function AddChange($Column, $Value) {
		if(!($Column instanceof DbColumn)) {
			$Column = DbExpression::ParseDbColumn($Column);
		}

		if(!($Value instanceof DbExpression)) {
			$Value = DbExpression::Parse($Value);
		}

		return $this->Updates[] = new DbUpdateChange($Column, $Value);
	}
}

class DbUpdateQuery {
	/* Instance variables */

	public $Sources = [];

	public $Changes = [];

	public $Conditions = null;

	/* Instance methods */

	public function AddSource($Expression, $Alias = null, $Method = null, $Conditions = null) {
		if($Expression instanceof DbSource) {
			return $this->Sources[] = $Source;
		}
		else {
			return $this->Sources[] = new DbSource($Expression, $Alias, $Method, $Conditions);
		}
	}

	public function AddChange($Column, $Value) {
		if($Column instanceof \Framework\Newnorth\AValueDataMember) {
			$Column = new DbColumn([
				$Column->DataManager->Table,
				$Column->Alias,
			]);
		}
		else if(!$Column instanceof DbColumn) {
			$Column = DbExpression::ParseDbColumn($Column);
		}

		if(!($Value instanceof DbExpression)) {
			$Value = DbExpression::Parse($Value);
		}

		return $this->Changes[] = new DbUpdateChange($Column, $Value);
	}
}

class DbUpdateChange {
	/* Instance variables */

	public $Column;

	public $Value;

	/* Magic methods */

	public function __construct($Column, $Value) {
		$this->Column = $Column;

		$this->Value = $Value;
	}
}

class DbDeleteQuery {
	/* Instance variables */

	public $Targets = [];

	public $Sources = [];

	public $Conditions = null;

	/* Instance methods */

	public function AddTarget($Target) {
		return $this->Targets[] = $Target;
	}

	public function AddSource($Expression, $Alias = null, $Method = null, $Conditions = null) {
		if($Expression instanceof DbSource) {
			return $this->Sources[] = $Source;
		}
		else {
			return $this->Sources[] = new DbSource($Expression, $Alias, $Method, $Conditions);
		}
	}
}

class DbSelectQuery {
	/* Instance variables */

	public $Columns = [];

	public $Sources = [];

	public $Conditions = null;

	public $Groups = [];

	public $Sorts = [];

	public $MaxRows = 0;

	public $FirstRow = 0;

	/* Instance methods */

	public function AddColumn($Expression, $Alias = null) {
		if($Expression instanceof DbSelectColumn) {
			$Column = $Expression;
		}
		else if($Expression instanceof \Framework\Newnorth\ADataManager) {
			$Column = new DbSelectColumn(
				'`'.$Expression->Table.'`.*',
				$Alias
			);
		}
		else if($Expression instanceof \Framework\Newnorth\AValueDataMember) {
			$Column = new DbSelectColumn(
				new DbColumn([
					$Expression->DataManager->Table,
					$Expression->Alias,
				]),
				$Alias
			);
		}
		else {
			$Column = new DbSelectColumn($Expression, $Alias);
		}

		if($Column->Alias === null) {
			$this->Columns[] = $Column;
		}
		else {
			$this->Columns[$Column->Alias] = $Column;
		}

		return $Column;
	}

	public function AddSource($Expression, $Alias = null, $Method = null, $Conditions = null) {
		if($Expression instanceof DbSource) {
			$Source = $Expression;
		}
		else if($Expression instanceof \Framework\Newnorth\ADataManager) {
			$Expression = '`'.$Expression->Database.'`.`'.$Expression->Table.'`';

			$Source = new DbSource($Expression, $Alias, $Method, $Conditions);
		}
		else {
			$Source = new DbSource($Expression, $Alias, $Method, $Conditions);
		}

		if($Source->Alias === null) {
			$this->Sources[] = $Source;
		}
		else {
			$this->Sources[$Source->Alias] = $Source;
		}

		return $Source;
	}

	public function AddGroup($Expression) {
		if($Expression instanceof DbExpression) {
			return $this->Groups[] = $Expression;
		}
		else {
			return $this->Groups[] = DbExpression::Parse($Expression);
		}
	}

	public function AddSort($Expression, $Direction = DB_ASC) {
		if($Expression instanceof DbSort) {
			return $this->Sorts[] = $Expression;
		}
		else {
			return $this->Sorts[] = new DbSort($Expression, $Direction);
		}
	}

	public function Limit($MaxRows = 0, $FirstRow = 0) {
		$this->MaxRows = $MaxRows;
		$this->FirstRow = $FirstRow;
	}
}

class DbSelectColumn {
	/* Instance variables */

	public $Expression;

	public $Alias = null;

	/* Magic methods */

	public function __construct($Expression, $Alias = null) {
		$this->Expression = DbExpression::Parse($Expression);

		$this->Alias = $Alias;
	}
}
?>