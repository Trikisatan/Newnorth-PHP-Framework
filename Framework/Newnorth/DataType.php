<?
namespace Framework\Newnorth;

abstract class DataType {
	/* Instance variables */

	public $DataManager;

	/* Magic methods */

	public function __construct(DataManager $DataManager, Array $Data) {
		$this->DataManager = $DataManager;

		foreach($Data as $Key => $Value) {
			if(isset($DataManager->DataMembers[$Key])) {
				$this->$Key = $DataManager->DataMembers[$Key]->Parse($Value);
			}
		}
	}

	public function __call($Function, $Parameters) {
		if(preg_match('/^([A-Z][a-z]+)([0-9A-Za-z]+)$/', $Function, $Matches) === 1) {
			$Function = $Matches[1];

			$Member = $Matches[2];

			if(isset($this->DataManager->DataMembers[$Member])) {
				$Member = $this->DataManager->DataMembers[$Member];

				if(method_exists($Member, $Function)) {
					return call_user_func_array(
						[$Member, $Function],
						[$this, $Parameters]
					);
				}
				else {
					throw new RuntimeException(
						'Object method doesn\'t exist.',
						['Function' => $Function.$Member->Name, 'Parameters' => $Parameters]
					);
				}
			}
			else {
				throw new RuntimeException(
					'Object method doesn\'t exist.',
					['Function' => $Function.$Member, 'Parameters' => $Parameters]
				);
			}
		}
		else {
			throw new RuntimeException(
				'Object method doesn\'t exist.',
				['Function' => $Function, 'Parameters' => $Parameters]
			);
		}
	}
}
?>