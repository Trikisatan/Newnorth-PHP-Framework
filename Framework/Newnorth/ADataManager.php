<?
namespace Framework\Newnorth;

abstract class ADataManager {
	/* Instance variables */

	public $DataType;

	public $Connection = null;

	public $Database = null;

	public $Table = null;

	public $PrimaryKey = null;

	public $DataMembers = [];

	public $DataReferences = [];

	public $DataLists = [];

	/* Magic methods */

	public function __toString() {
		return '`'.$this->Database.'`.`'.$this->Table.'`';
	}

	/* Instance methods */

	public abstract function InitializeDataMembers();

	public abstract function InitializeReferenceDataMembers();

	public abstract function InitializeDataReferences();

	public abstract function InitializeDataLists();

	public function AddDataMember(\Framework\Newnorth\ADataMember $DataMember) {
		return $this->DataMembers[$DataMember->Alias] = $DataMember;
	}

	public function AddDataReference(array $Parameters) {
		$DataReference = new \Framework\Newnorth\DataReference($Parameters);

		$this->DataReferences[$DataReference->Alias] = $DataReference;

		return $DataReference;
	}

	public function AddDataList(array $Parameters) {
		$DataList = new \Framework\Newnorth\DataList($Parameters);

		$this->DataLists[$DataList->SingularAlias] = $DataList;

		$this->DataLists[$DataList->PluralAlias] = $DataList;

		return $DataList;
	}

	public abstract function OnInserted($LastInsertId);

	public abstract function OnDelete($Item);

	public abstract function OnDeleted($Item);
}
?>