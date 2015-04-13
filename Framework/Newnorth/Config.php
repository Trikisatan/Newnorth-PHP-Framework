<?
namespace Framework\Newnorth;

class Config {
	/* Instance variables */

	public $FilePath;

	public $Data;

	public $Files = [
		'DataManagers' => '',
		'DataTypes' => '',
		'EMailTemplates' => '',
		'Layouts' => '',
		'Pages' => '',
		'Controls' => '',
		'Translations' => '',
		'ErrorLog' => 'errors.log',
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
		'RouteNotFound' => [
			'Log' => true,
			'Report' => false,
		],
		'Pages' => [
			'Error' => [],
			'NotFound' => [],
		],
	];

	public $Translation = [
		'ThrowException' => true,
		'Log' => true,
		'Report' => true,
	];

	public $EMailer = [
		'ErrorReport' => [
			'From' => '',
			'To' => '',
		],
	];

	/* Magic methods */

	public function __construct($FilePath = 'Config.ini') {
		$this->FilePath = $FilePath;
	}

	/* Instance methods */

	public function Initialize() {
		$this->Data = ParseIniFile($this->FilePath);

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

	private function Initialize_Files($Section) {
		$this->Files['DataManagers'] = isset($Section['DataManagers']) ? $Section['DataManagers'] : $this->Files['DataManagers'];

		$this->Files['DataTypes'] = isset($Section['DataTypes']) ? $Section['DataTypes'] : $this->Files['DataTypes'];

		$this->Files['EMailTemplates'] = isset($Section['EMailTemplates']) ? $Section['EMailTemplates'] : $this->Files['EMailTemplates'];

		$this->Files['Layouts'] = isset($Section['Layouts']) ? $Section['Layouts'] : $this->Files['Layouts'];

		$this->Files['Pages'] = isset($Section['Pages']) ? $Section['Pages'] : $this->Files['Pages'];

		$this->Files['Controls'] = isset($Section['Controls']) ? $Section['Controls'] : $this->Files['Controls'];

		$this->Files['Translations'] = isset($Section['Translations']) ? $Section['Translations'] : $this->Files['Translations'];

		$this->Files['ErrorLog'] = isset($Section['ErrorLog']) ? $Section['ErrorLog'] : $this->Files['ErrorLog'];
	}

	private function Initialize_ErrorHandling($Section) {
		$this->ErrorHandling['Log'] = isset($Section['Log']) ? $Section['Log'] : $this->ErrorHandling['Log'];

		$this->ErrorHandling['LogMethods'] = isset($Section['LogMethods']) ? explode(';', $Section['LogMethods']) : $this->ErrorHandling['LogMethods'];

		$this->ErrorHandling['Report'] = isset($Section['Report']) ? $Section['Report'] : $this->ErrorHandling['Report'];

		$this->ErrorHandling['ReportMethods'] = isset($Section['ReportMethods']) ? explode(';', $Section['ReportMethods']) : $this->ErrorHandling['ReportMethods'];

		$this->ErrorHandling['DisplayErrorMessages'] = isset($Section['DisplayErrorMessages']) ? $Section['DisplayErrorMessages'] : $this->ErrorHandling['DisplayErrorMessages'];

		$this->ErrorHandling['DisplayErrorMessageDetails'] = isset($Section['DisplayErrorMessageDetails']) ? $Section['DisplayErrorMessageDetails'] : $this->ErrorHandling['DisplayErrorMessageDetails'];

		if(isset($Section['RouteNotFound'])) {
			$this->Initialize_ErrorHandling_RouteNotFound($Section['RouteNotFound']);
		}

		if(isset($Section['Pages'])) {
			$this->Initialize_ErrorHandling_Pages($Section['Pages']);
		}
	}

	private function Initialize_ErrorHandling_RouteNotFound($Section) {
		$this->ErrorHandling['RouteNotFound']['Log'] = isset($Section['Log']) ? $Section['Log'] : $this->ErrorHandling['RouteNotFound']['Log'];

		$this->ErrorHandling['RouteNotFound']['Report'] = isset($Section['Report']) ? $Section['Report'] : $this->ErrorHandling['RouteNotFound']['Report'];
	}

	private function Initialize_ErrorHandling_Pages($Section) {
		$this->ErrorHandling['Pages']['Error'] = isset($Section['Error']) ? $Section['Error'] : $this->ErrorHandling['Pages']['Error'];

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