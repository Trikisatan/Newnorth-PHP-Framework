<?
namespace Framework\Newnorth;

define('DB_ASC', 1);
define('DB_DESC', 2);

abstract class DbConnection {
	/* Variables */

	public $IsConnected = false;

	/* Methods */

	public abstract function Connect();

	public abstract function Insert(DbInsertQuery $Query);

	public abstract function Update(DbUpdateQuery $Query);

	public abstract function Delete(DbDeleteQuery $Query);

	public abstract function Find(DbSelectQuery $Query);

	public abstract function FindAll(DbSelectQuery $Query);

	public abstract function Count(DbSelectQuery $Query);

	public abstract function Lock($Sources);

	public abstract function Unlock($Sources);
}

abstract class DbResult {
	/* Variables */

	public $Rows;

	public $Row;

	/* Methods */

	public abstract function Fetch();

	public abstract function FetchAssoc();

	public abstract function GetBoolean($Column);

	public abstract function GetFloat($Column);

	public abstract function GetInt($Column);

	public abstract function GetString($Column);

	public abstract function IsFalse($Column);

	public abstract function IsTrue($Column);

	public abstract function IsNull($Column);
}

abstract class DbCondition {
	
}

abstract class DbConditionGroup extends DbCondition {
	/* Variables */

	public $Conditions = [];

	/* Methods */

	public function Add(DbCondition $Condition) {
		$this->Conditions[] = $Condition;
		return $this;
	}

	public function EqualTo($A, $B) {
		$this->Conditions[] = new DbEqualTo($A, $B);
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
}

class DbAnd extends DbConditionGroup {
	
}

class DbOr extends DbConditionGroup {
	
}

class DbEqualTo extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbLike extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbContains extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbStartsWith extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbEndsWith extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbGreaterThan extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbLessThan extends DbCondition {
	/* Variables */

	public $A;

	public $B;

	/* Magic methods */

	public function __construct($A, $B) {
		$this->A = DbExpression::Parse($A);

		$this->B = DbExpression::Parse($B);
	}
}

class DbExpression {
	/* Static methods */

	static public function Parse($Expression) {
		if($Expression instanceof DbExpression) {
			return $Expression;
		}

		if($Expression === null) {
			return new DbNull();
		}

		if(is_array($Expression)) {
			return new DbArray($Expression);
		}

		if(is_float($Expression)) {
			return new DbFloat($Expression);
		}

		if(is_int($Expression)) {
			return new DbInt($Expression);
		}

		$Length = strlen($Expression);

		if(2 < $Length && $Expression[0] === '`' && $Expression[$Length - 1] === '`') {
			return new DbColumn(explode('`.`', substr($Expression, 1, -1)));
		}

		if(1 < $Length && $Expression[0] === '"' && $Expression[$Length - 1] === '"') {
			if(2 < $Length) {
				return new DbString(substr($Expression, 1, -1));
			}
			else {
				return new DbString("");
			}
		}

		return new DbExpression($Expression);
	}

	static public function ParseDbColumn($Expression) {
		$Length = strlen($Expression);

		if(2 < $Length && $Expression[0] === '`' && $Expression[$Length - 1] === '`') {
			return new DbColumn(explode('`.`', substr($Expression, 1, -1)));
		}
		else {
			throw RuntimeException(
				'Invalid format on expression, could not be parsed to DbColumn.',
				[
					'Expression' => $Expression,
				]
			);
		}
	}

	/* Variables */

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

		$this->Value = $Value;
	}
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
	/* Variables */

	public $Expression;

	public $Direction;

	/* Magic methods */

	public function __construct($Expression, $Direction = DB_ASC) {
		$this->Expression = DbExpression::Parse($Expression);

		$this->Direction = $Direction;
	}
}

class DbInsertQuery {
	/* Variables */

	public $Source = null;

	public $Columns = [];

	public $Values = [];

	/* Methods */

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

class DbUpdateQuery {
	/* Variables */

	public $Sources = [];

	public $Changes = [];

	public $Conditions = null;

	/* Methods */

	public function AddSource($Expression, $Alias = null) {
		if($Expression instanceof DbUpdateSource) {
			return $this->Sources[] = $Source;
		}
		else {
			return $this->Sources[] = new DbUpdateSource($Expression, $Alias);
		}
	}

	public function AddChange($Column, $Value) {
		if(!($Column instanceof DbColumn)) {
			$Column = DbExpression::ParseDbColumn($Column);
		}

		if(!($Value instanceof DbExpression)) {
			$Value = DbExpression::Parse($Value);
		}

		return $this->Changes[] = new DbUpdateChange($Column, $Value);
	}
}

class DbUpdateSource {
	/* Variables */

	public $Reference;

	public $Alias = null;

	/* Magic methods */

	public function __construct($Reference, $Alias = null) {
		$this->Reference = $Reference;

		$this->Alias = $Alias;
	}
}

class DbUpdateChange {
	/* Variables */

	public $Column;

	public $Value;

	/* Magic methods */

	public function __construct($Column, $Value) {
		$this->Column = $Column;

		$this->Value = $Value;
	}
}

class DbDeleteQuery {
	/* Variables */

	public $Targets = [];

	public $Sources = [];

	public $Conditions = null;

	/* Methods */

	public function AddTarget($Target) {
		return $this->Targets[] = $Target;
	}

	public function AddSource($Expression, $Alias = null) {
		if($Expression instanceof DbDeleteSource) {
			return $this->Sources[] = $Source;
		}
		else {
			return $this->Sources[] = new DbDeleteSource($Expression, $Alias);
		}
	}
}

class DbDeleteSource {
	/* Variables */

	public $Reference;

	public $Alias = null;

	/* Magic methods */

	public function __construct($Reference, $Alias = null) {
		$this->Reference = $Reference;

		$this->Alias = $Alias;
	}
}

class DbSelectQuery {
	/* Variables */

	public $Columns = [];

	public $Sources = [];

	public $Conditions = null;

	public $Sorts = [];

	public $MaxRows = 0;

	public $FirstRow = 0;

	/* Methods */

	public function AddColumn($Expression, $Alias = null) {
		if($Expression instanceof DbSelectColumn) {
			return $this->Columns[] = $Expression;
		}
		else {
			return $this->Columns[] = new DbSelectColumn($Expression, $Alias);
		}
	}

	public function AddSource($Expression, $Alias = null) {
		if($Expression instanceof DbSelectSource) {
			return $this->Sources[] = $Source;
		}
		else {
			return $this->Sources[] = new DbSelectSource($Expression, $Alias);
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
	/* Variables */

	public $Expression;

	public $Alias = null;

	/* Magic methods */

	public function __construct($Expression, $Alias = null) {
		$this->Expression = DbExpression::Parse($Expression);

		$this->Alias = $Alias;
	}
}

class DbSelectSource {
	/* Variables */

	public $Reference;

	public $Alias = null;

	/* Magic methods */

	public function __construct($Reference, $Alias = null) {
		$this->Reference = $Reference;

		$this->Alias = $Alias;
	}
}
?>