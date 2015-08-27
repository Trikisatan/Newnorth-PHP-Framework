<?
namespace Framework\Newnorth;

abstract class DataType {
	/* Instance variables */

	public $_DataManager;

	public $_Data;

	/* Magic methods */

	public function __construct(DataManager $DataManager, Array $Data) {
		$this->_DataManager = $DataManager;

		$this->_Data = $Data;

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

			if(isset($this->_DataManager->DataMembers[$Member])) {
				$Member = $this->_DataManager->DataMembers[$Member];

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