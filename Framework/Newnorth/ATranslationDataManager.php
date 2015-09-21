<?
namespace Framework\Newnorth;

abstract class ATranslationDataManager extends ADataManager {
	/* Instance variables */

	public $ParentPrimaryKey = null;

	/* Instance methods */

	public function Insert($ParentPrimaryKey, $Locale, array $Data = null) {
		if($Locale === null) {
			$Locale = $GLOBALS['Parameters']['Locale'];
		}

		$Query = new \Framework\Newnorth\DbInsertQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		$Query->AddColumn('`'.$this->ParentPrimaryKey->Alias.'`');

		$Query->AddValue($ParentPrimaryKey);

		$Query->AddColumn('`Locale`');

		$Query->AddValue('"'.$Locale.'"');

		if($Data === null) {
			foreach($Data as $Column => $Value) {
				$Query->AddColumn('`'.$Column.'`');

				$Query->AddValue($Value);
			}
		}

		return $this->Connection->Insert($Query);
	}

	public function Update($ParentPrimaryKey, $Locale, array $Changes) {
		if($Locale === null) {
			$Locale = $GLOBALS['Parameters']['Locale'];
		}

		$Query = new \Framework\Newnorth\DbInsertUpdateQuery();

		$Query->Source = '`'.$this->Database.'`.`'.$this->Table.'`';

		$Query->AddColumn('`'.$this->ParentPrimaryKey->Alias.'`');

		$Query->AddValue($ParentPrimaryKey);

		$Query->AddColumn('`Locale`');

		$Query->AddValue('"'.$Locale.'"');

		foreach($Changes as $Column => $Value) {
			$Query->AddColumn('`'.$Column.'`');

			$Query->AddValue($Value);
		}

		foreach($Changes as $Column => $Value) {
			$Query->AddChange('`'.$Column.'`', $Value);
		}

		return $this->Connection->InsertUpdate($Query);
	}

	public function Delete($ParentPrimaryKey, $Locale) {
		if($Locale === null) {
			$Locale = $GLOBALS['Parameters']['Locale'];
		}

		$Query = new \Framework\Newnorth\DbDeleteQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = new \Framework\Newnorth\DbAnd([
			new \Framework\Newnorth\DbEqualTo('`'.$this->ParentPrimaryKey->Alias.'`', $ParentPrimaryKey),
			new \Framework\Newnorth\DbEqualTo('`Locale`', '"'.$Locale.'"'),
		]);

		return $this->Connection->Delete($Query);
	}

	public function DeleteAll($ParentPrimaryKey) {
		$Query = new \Framework\Newnorth\DbDeleteQuery();

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = new \Framework\Newnorth\DbEqualTo('`'.$this->ParentPrimaryKey->Alias.'`', $ParentPrimaryKey);

		return $this->Connection->Delete($Query);
	}

	public function FindAll($ParentPrimaryKey, $Column) {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn('`Locale`');

		$Query->AddColumn('`'.$Column.'`');

		$Query->AddSource('`'.$this->Database.'`.`'.$this->Table.'`');

		$Query->Conditions = new \Framework\Newnorth\DbEqualTo('`'.$this->ParentPrimaryKey->Alias.'`', $ParentPrimaryKey);

		$Result = $this->Connection->FindAll($Query);

		if($Result === false) {
			return [];
		}
		else {
			$Items = [];

			while($Result->Fetch()) {
				$Locale = $Result->GetString(0);

				$Value = $Result->GetString(1);

				$Value = $this->DataMembers[$Column]->Parse($Value);

				$Items[$Locale] = $Value;
			}

			return $Items;
		}
	}
}