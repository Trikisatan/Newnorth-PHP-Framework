<?
namespace Framework\Newnorth;

abstract class ADataMember {
	/* Instance variables */

	public $UseLogInsert = false;

	public $UseLogUpdate = false;

	public $UseLogDelete = false;

	/* Magic methods */

	public function __construct($Parameters) {
		if(isset($Parameters['UseLogInsert'])) {
			$this->UseLogInsert = $Parameters['UseLogInsert'];
		}

		if(isset($Parameters['UseLogUpdate'])) {
			$this->UseLogUpdate = $Parameters['UseLogUpdate'];
		}

		if(isset($Parameters['UseLogDelete'])) {
			$this->UseLogDelete = $Parameters['UseLogDelete'];
		}
	}

	/* Instance methods */

	public abstract function Parse($Value);

	public abstract function ToDbExpression($Value);

	public abstract function Set(\Framework\Newnorth\DataType $DataType, array $Parameters);

	public abstract function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters);

	public abstract function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters);

	private function Log(\Framework\Newnorth\DataType $DataType, $Method) {
		$Query = new \Framework\Newnorth\DbInsertQuery();

		if($this->DataManager->Database === null) {
			$Query->Source = '`'.$this->DataManager->Table.'»Log`';
		}
		else {
			$Query->Source = '`'.$this->DataManager->Database.'`.`'.$this->DataManager->Table.'»Log`';
		}

		$Query->AddColumn('`PrimaryKey`');

		$Query->AddValue($DataType->{$this->DataManager->PrimaryKey->Alias});

		$Query->AddColumn('`Method`');

		$Query->AddValue('"'.$Method.'"');

		$Query->AddColumn('`Column`');

		$Query->AddValue('"'.$this->Alias.'"');

		$Query->AddColumn('`Value`');

		$Query->AddValue($this->ToDbExpression($DataType->{$this->Alias}));

		$Query->AddColumn('`System`');

		$Query->AddValue('"'.Config('Logging/System', '').'"');

		$Query->AddColumn('`Source`');

		$Query->AddValue('"'.Config('Logging/Source', '').'"');

		$Query->AddColumn('`Time`');

		$Query->AddValue($_SERVER['REQUEST_TIME']);

		return $this->DataManager->Connection->Insert($Query);
	}

	public function LogUpdate(\Framework\Newnorth\DataType $DataType) {
		return $this->Log($DataType, 'UPDATE');
	}

	public function LogInsert(\Framework\Newnorth\DataType $DataType) {
		return $this->Log($DataType, 'INSERT');
	}

	public function LogDelete(\Framework\Newnorth\DataType $DataType) {
		return $this->Log($DataType, 'DELETE');
	}
}
?>