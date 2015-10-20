<?
namespace Framework\Newnorth;

class Config {
	/* Instance variables */

	public $FilePath;

	public $Data;

	public $System = '';

	public $Defaults = [
		'Locale' => '',
	];

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

	/* Magic methods */

	public function __construct($FilePath = null) {
		$this->FilePath = $FilePath;
	}

	/* Instance methods */

	public function Initialize() {
		$this->Data = ParseIniFile($this->FilePath);

		$this->System = isset($this->Data['System']) ? $this->Data['System'] : $this->System;

		if(isset($this->Data['Defaults'])) {
			$this->Initialize_Defaults($this->Data['Defaults']);
		}

		if(isset($this->Data['Files'])) {
			$this->Initialize_Files($this->Data['Files']);
		}

		if(isset($this->Data['ErrorHandling'])) {
			$this->Initialize_ErrorHandling($this->Data['ErrorHandling']);
		}

		if(isset($this->Data['Translation'])) {
			$this->Initialize_Translation($this->Data['Translation']);
		}

		if(isset($this->Data['DbConnections'])) {
			$this->Initialize_DbConnections($this->Data['DbConnections']);
		}
	}

	private function Initialize_Defaults($Section) {
		$this->Defaults['Locale'] = isset($Section['Locale']) ? $Section['Locale'] : $this->Defaults['Locale'];
	}

	private function Initialize_Files($Section) {
		$this->Files['Applications'] = isset($Section['Applications']) ? $Section['Applications'] : $this->Files['Applications'];

		$this->Files['Layouts'] = isset($Section['Layouts']) ? $Section['Layouts'] : $this->Files['Layouts'];

		$this->Files['Pages'] = isset($Section['Pages']) ? $Section['Pages'] : $this->Files['Pages'];

		$this->Files['Controls'] = isset($Section['Controls']) ? $Section['Controls'] : $this->Files['Controls'];

		$this->Files['DataManagers'] = isset($Section['DataManagers']) ? $Section['DataManagers'] : $this->Files['DataManagers'];

		$this->Files['DataTypes'] = isset($Section['DataTypes']) ? $Section['DataTypes'] : $this->Files['DataTypes'];

		$this->Files['ErrorLog'] = isset($Section['ErrorLog']) ? $Section['ErrorLog'] : $this->Files['ErrorLog'];

		$this->Files['EMailTemplates'] = isset($Section['EMailTemplates']) ? $Section['EMailTemplates'] : $this->Files['EMailTemplates'];
	}

	private function Initialize_ErrorHandling($Section) {
		$this->ErrorHandling['Log'] = isset($Section['Log']) ? $Section['Log'] : $this->ErrorHandling['Log'];

		$this->ErrorHandling['LogMethods'] = isset($Section['LogMethods']) ? explode(';', $Section['LogMethods']) : $this->ErrorHandling['LogMethods'];

		$this->ErrorHandling['Report'] = isset($Section['Report']) ? $Section['Report'] : $this->ErrorHandling['Report'];

		$this->ErrorHandling['ReportMethods'] = isset($Section['ReportMethods']) ? explode(';', $Section['ReportMethods']) : $this->ErrorHandling['ReportMethods'];

		$this->ErrorHandling['DisplayErrorMessages'] = isset($Section['DisplayErrorMessages']) ? $Section['DisplayErrorMessages'] : $this->ErrorHandling['DisplayErrorMessages'];

		$this->ErrorHandling['DisplayErrorMessageDetails'] = isset($Section['DisplayErrorMessageDetails']) ? $Section['DisplayErrorMessageDetails'] : $this->ErrorHandling['DisplayErrorMessageDetails'];

		if(isset($Section['Pages'])) {
			$this->Initialize_ErrorHandling_Pages($Section['Pages']);
		}
	}

	private function Initialize_ErrorHandling_Pages($Section) {
		$this->ErrorHandling['Pages']['Error'] = isset($Section['Error']) ? $Section['Error'] : $this->ErrorHandling['Pages']['Error'];

		$this->ErrorHandling['Pages']['Forbidden'] = isset($Section['Forbidden']) ? $Section['Forbidden'] : $this->ErrorHandling['Pages']['Forbidden'];

		$this->ErrorHandling['Pages']['NotFound'] = isset($Section['NotFound']) ? $Section['NotFound'] : $this->ErrorHandling['Pages']['NotFound'];
	}

	private function Initialize_Translation($Section) {
		$this->Translation['ThrowException'] = isset($Section['ThrowException']) ? ($Section['ThrowException'] === '1') : $this->Translation['ThrowException'];

		$this->Translation['Log'] = isset($Section['Log']) ? ($Section['Log'] === '1') : $this->Translation['Log'];

		$this->Translation['Report'] = isset($Section['Report']) ? ($Section['Report'] === '1') : $this->Translation['Report'];
	}

	private function Initialize_DbConnections($DbConnections) {
		$this->DbConnections = $DbConnections;
	}
}
?>