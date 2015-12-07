<?
namespace Framework\Newnorth;

class Config {
	/* Instance variables */

	public $Data = [];

	public $Files = [
		'Applications' => '',
		'Layouts' => '',
		'Pages' => '',
		'Controls' => '',
		'DataManagers' => '',
		'DataTypes' => '',
		'ErrorLog' => 'errors.log',
		'EMailTemplates' => '',
	];

	public $ErrorHandling = [
		'Log' => true,
		'LogMethods' => [
			'\Framework\Newnorth\Logger::ErrorLog',
		],
		'Report' => false,
		'ReportMethods' => [
			'\Framework\Newnorth\EMailer::ErrorReport',
		],
		'DisplayErrorMessages' => true,
		'DisplayErrorMessageDetails' => true,
		'Pages' => [
			'BadRequest' => [
				'Application' => 'Default',
				'Layout' => 'Default',
				'Page' => 'BadRequest',
			],
			'Error' => [
				'Application' => 'Default',
				'Layout' => 'Default',
				'Page' => 'Error',
			],
			'Forbidden' => [
				'Application' => 'Default',
				'Layout' => 'Default',
				'Page' => 'Forbidden',
			],
			'NotFound' => [
				'Application' => 'Default',
				'Layout' => 'Default',
				'Page' => 'NotFound',
			],
		],
	];

	public $Translation = [
		'ThrowException' => true,
		'Log' => true,
		'Report' => false,
	];

	public $EMailer = [
		'ErrorReport' => [
			'From' => '',
			'To' => '',
		],
	];

	/* Instance methods */

	public function Initialize(array $FilePaths) {
		$Data = [];

		foreach($FilePaths as $FilePath) {
			$FileData = file_get_contents($FilePath);

			$FileData = json_decode($FileData, true);

			if($FileData === null) {
				throw new ConfigException(
					'Unable to parse config file.',
					[
						'File path' => $FilePath,
					]
				);
			}
			else {
				$this->AppendData($Data, $FileData);
			}
		}

		$this->MergeData($Data);
	}

	public function AppendData(array &$A, array $B) {
		foreach($B as $K => $V) {
			if(is_array($V) && isset($A[$K]) && is_array($A[$K])) {
				$this->AppendData($A[$K], $V);
			}
			else {
				$A[$K] = $V;
			}
		}
	}

	public function MergeData(array $Data, $Path = null) {
		foreach($Data as $K => $V) {
			if($Path === null) {
				$this->Data[$K] = $V;
			}
			else {
				$this->Data[$Path.'/'.$K] = $V;
			}

			if(is_array($V)) {
				if($Path === null) {
					$this->MergeData($V, $K);
				}
				else {
					$this->MergeData($V, $Path.'/'.$K);
				}
			}
		}
	}

	public function Load() {
		$this->System = isset($this->Data['System']) ? $this->Data['System'] : $this->System;

		if(isset($this->Data['Defaults'])) {
			$this->Load_Defaults($this->Data['Defaults']);
		}

		if(isset($this->Data['Files'])) {
			$this->Load_Files($this->Data['Files']);
		}

		if(isset($this->Data['ErrorHandling'])) {
			$this->Load_ErrorHandling($this->Data['ErrorHandling']);
		}

		if(isset($this->Data['Translation'])) {
			$this->Load_Translation($this->Data['Translation']);
		}

		if(isset($this->Data['DbConnections'])) {
			$this->Load_DbConnections($this->Data['DbConnections']);
		}
	}

	private function Load_Defaults($Section) {
		$this->Defaults['Locale'] = isset($Section['Locale']) ? $Section['Locale'] : $this->Defaults['Locale'];
	}

	private function Load_Files($Section) {
		$this->Files['Applications'] = isset($Section['Applications']) ? $Section['Applications'] : $this->Files['Applications'];

		$this->Files['Layouts'] = isset($Section['Layouts']) ? $Section['Layouts'] : $this->Files['Layouts'];

		$this->Files['Pages'] = isset($Section['Pages']) ? $Section['Pages'] : $this->Files['Pages'];

		$this->Files['Controls'] = isset($Section['Controls']) ? $Section['Controls'] : $this->Files['Controls'];

		$this->Files['DataManagers'] = isset($Section['DataManagers']) ? $Section['DataManagers'] : $this->Files['DataManagers'];

		$this->Files['DataTypes'] = isset($Section['DataTypes']) ? $Section['DataTypes'] : $this->Files['DataTypes'];

		$this->Files['ErrorLog'] = isset($Section['ErrorLog']) ? $Section['ErrorLog'] : $this->Files['ErrorLog'];

		$this->Files['EMailTemplates'] = isset($Section['EMailTemplates']) ? $Section['EMailTemplates'] : $this->Files['EMailTemplates'];
	}

	private function Load_ErrorHandling($Section) {
		$this->ErrorHandling['Log'] = isset($Section['Log']) ? $Section['Log'] : $this->ErrorHandling['Log'];

		$this->ErrorHandling['LogMethods'] = isset($Section['LogMethods']) ? explode(';', $Section['LogMethods']) : $this->ErrorHandling['LogMethods'];

		$this->ErrorHandling['Report'] = isset($Section['Report']) ? $Section['Report'] : $this->ErrorHandling['Report'];

		$this->ErrorHandling['ReportMethods'] = isset($Section['ReportMethods']) ? explode(';', $Section['ReportMethods']) : $this->ErrorHandling['ReportMethods'];

		$this->ErrorHandling['DisplayErrorMessages'] = isset($Section['DisplayErrorMessages']) ? $Section['DisplayErrorMessages'] : $this->ErrorHandling['DisplayErrorMessages'];

		$this->ErrorHandling['DisplayErrorMessageDetails'] = isset($Section['DisplayErrorMessageDetails']) ? $Section['DisplayErrorMessageDetails'] : $this->ErrorHandling['DisplayErrorMessageDetails'];

		if(isset($Section['Pages'])) {
			$this->Load_ErrorHandling_Pages($Section['Pages']);
		}
	}

	private function Load_ErrorHandling_Pages($Section) {
		$this->ErrorHandling['Pages']['Error'] = isset($Section['Error']) ? $Section['Error'] : $this->ErrorHandling['Pages']['Error'];

		$this->ErrorHandling['Pages']['Forbidden'] = isset($Section['Forbidden']) ? $Section['Forbidden'] : $this->ErrorHandling['Pages']['Forbidden'];

		$this->ErrorHandling['Pages']['NotFound'] = isset($Section['NotFound']) ? $Section['NotFound'] : $this->ErrorHandling['Pages']['NotFound'];
	}

	private function Load_Translation($Section) {
		$this->Translation['ThrowException'] = isset($Section['ThrowException']) ? ($Section['ThrowException'] === '1') : $this->Translation['ThrowException'];

		$this->Translation['Log'] = isset($Section['Log']) ? ($Section['Log'] === '1') : $this->Translation['Log'];

		$this->Translation['Report'] = isset($Section['Report']) ? ($Section['Report'] === '1') : $this->Translation['Report'];
	}

	private function Load_DbConnections($DbConnections) {
		$this->DbConnections = $DbConnections;
	}
}
?>