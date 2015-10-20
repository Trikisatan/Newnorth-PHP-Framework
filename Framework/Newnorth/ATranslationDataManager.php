<?
namespace Framework\Newnorth;

abstract class ATranslationDataManager extends ADataManager {
	/* Instance methods */

	public function CreateSelectQuery() {
		$Query = new \Framework\Newnorth\DbSelectQuery();

		$Query->AddColumn($this);

		$Query->AddSource($this);

		return $Query;
	}
}