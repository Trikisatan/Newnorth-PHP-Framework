<?
namespace Framework\Newnorth;

class Application {
	/* Static methods */

	public static function Instantiate($Path, &$Application) {
		Application::ParsePath(
			$Path,
			$FilePath,
			$Directory,
			$ClassName,
			$Namespace,
			$Name
		);

		if(!class_exists($ClassName, false)) {
			if(file_exists($FilePath)) {
				include($FilePath);
			}

			if(!class_exists($ClassName, false)) {
				throw new RuntimeException(
					'Unable to load application.',
					[
						'Path' => $Path,
						'File path' => $FilePath,
						'Directory' => $Directory,
						'Class name' => $ClassName,
						'Namespace' => $Namespace,
						'Name' => $Name,
					]
				);
			}
		}

		$Application = new $ClassName($Directory, $Namespace, $Name);
	}

	private static function ParsePath($Path, &$FilePath, &$Directory, &$ClassName, &$Namespace, &$Name) {
		$FilePath = $GLOBALS['Config']->Files['Applications'].$Path.'Application.php';

		$Directory = substr($FilePath, 0, strrpos($FilePath, '/') + 1);

		$ClassName = '\\'.str_replace('/', '\\', $Path).'Application';

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = ($Namespace === false) ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$Name = strrpos($Path, '/');

		$Name = (($Name === false) ? $Path : substr($Path, $Name + 1)).'Application';
	}

	/* Instance variables */

	public $Objects = [];

	private $DbConnections = [];

	private $DataManagers = [];

	/* Magic methods */

	public function __construct() {

	}

	/* Life cycle methods */

	public function PreInitialize() {

	}

	public function Initialize() {

	}

	public function PostInitialize() {

	}

	public function PreLoad() {

	}

	public function Load() {

	}

	public function PostLoad() {

	}

	public function PreExecute() {

	}

	public function Execute() {
		if(!isset($GLOBALS['Parameters']['Layout'][0])) {
			$GLOBALS['Page']->PreInitialize();
			$GLOBALS['Page']->Initialize();
			$GLOBALS['Page']->PostInitialize();

			$GLOBALS['Page']->PreLoad();
			$GLOBALS['Page']->Load();
			$GLOBALS['Page']->PostLoad();

			$GLOBALS['Page']->PreExecute();
			$GLOBALS['Page']->Execute();
			$GLOBALS['Page']->PostExecute();
		}
		else {
			$GLOBALS['Layout']->PreInitialize();
			$GLOBALS['Page']->PreInitialize();
			$GLOBALS['Layout']->Initialize();
			$GLOBALS['Page']->Initialize();
			$GLOBALS['Layout']->PostInitialize();
			$GLOBALS['Page']->PostInitialize();

			$GLOBALS['Layout']->PreLoad();
			$GLOBALS['Page']->PreLoad();
			$GLOBALS['Layout']->Load();
			$GLOBALS['Page']->Load();
			$GLOBALS['Layout']->PostLoad();
			$GLOBALS['Page']->PostLoad();

			$GLOBALS['Layout']->PreExecute();
			$GLOBALS['Page']->PreExecute();
			$GLOBALS['Layout']->Execute();
			$GLOBALS['Page']->Execute();
			$GLOBALS['Layout']->PostExecute();
			$GLOBALS['Page']->PostExecute();
		}
	}

	public function PostExecute() {

	}

	public function Render() {
		if($GLOBALS['Layout'] === null) {
			$GLOBALS['Page']->Render();
		}
		else {
			$GLOBALS['Layout']->Render();
		}

		$Output = ob_get_contents();

		ob_clean();

		$GLOBALS['Translations']->Translate($Output);

		$MissingTranslations = Translator::GetMissingTranslations($Output);

		if(0 < count($MissingTranslations)) {
			if($GLOBALS['Config']->Translation['ThrowException']) {
				throw new ConfigException(
					'Translation(s) not found',
					[
						'Missing translations' => $MissingTranslations,
					]
				);
			}
			else {
				if($GLOBALS['Config']->Translation['Log']) {
					foreach($GLOBALS['Config']->ErrorHandling['LogMethods'] as $LogMethod) {
						call_user_func(
							$LogMethod,
							[
								'Type' => 'Translation(s) not found',
								'Message' => 'Unable to translate content.',
								'Url' => $GLOBALS['Url'],
								'Missing translations' => $MissingTranslations,
							]
						);
					}
				}

				if($GLOBALS['Config']->Translation['Report']) {
					foreach($GLOBALS['Config']->ErrorHandling['ReportMethods'] as $ReportMethod) {
						call_user_func(
							$ReportMethod,
							[
								'Type' => 'Translation(s) not found',
								'Message' => 'Unable to translate content.',
								'Url' => $GLOBALS['Url'],
								'Missing translations' => $MissingTranslations,
							]
						);
					}
				}
			}
		}

		echo $Output;
	}

	/* Instance methods */

	public function GetObject($Scope, $Name) {
		if(!isset($Name[0])) {
			$Name = $Scope;
		}
		else if($Name[0] !== '/') {
			while(substr($Name, 0, 3) === '../') {
				$Name = substr($Name, 3);

				$Slash = strrpos($Scope, '/', -1);

				$Scope = substr($Scope, 0, $Slash + 1);
			}

			if(!isset($Name[0]) || $Name === './') {
				$Name = $Scope;
			}
			else {
				$Name = $Scope.'/'.$Name;
			}
		}

		return isset($this->Objects[$Name]) ? $this->Objects[$Name] : null;
	}

	public function RegisterObject($Object) {
		$this->Objects[$Object->__toString()] = $Object;
	}

	public function UnregisterObject($Object) {
		unset($this->Objects[$Object->__toString()]);
	}

	public function GetDbConnection($Alias) {
		if(isset($this->DbConnections[$Alias])) {
			return $this->DbConnections[$Alias];
		}
		else if(isset($GLOBALS['Config']->DbConnections[$Alias])) {
			$Parameters = $GLOBALS['Config']->DbConnections[$Alias];

			if(!isset($Parameters['Type'])) {
				throw new ConfigException(
					'Database connection type not set.',
					[
						'Alias' => $Alias,
					]
				);
			}
			else if(!class_exists($Parameters['Type'], false)) {
				throw new ConfigException(
					'Database connection type not found.',
					[
						'Aliass' => $Alias,
						'Type' => $Parameters['Type'],
					]
				);
			}
			else {
				return $this->DbConnections[$Alias] = new $Parameters['Type']($Parameters);
			}
		}
		else {
			throw new RuntimeException(
				'Database connection not found.',
				[
					'Alias' => $Alias,
				]
			);
		}
	}

	public function GetDataManager($Alias) {
		if(isset($this->DataManagers[$Alias])) {
			return $this->DataManagers[$Alias];
		}
		else {
			$DataManager = '\\'.str_replace('/', '\\', $Alias).'DataManager';

			if(!class_exists($DataManager, false)) {
				include($GLOBALS['Config']->Files['DataManagers'].str_replace('\\', '/', $Alias).'DataManager.php');

				if(!class_exists($DataManager, false)) {
					throw new RuntimeException(
						'Unable to load data manager.',
						[
							'File' => $GLOBALS['Config']->Files['DataManagers'].$Alias.'DataManager.php',
							'Class' => $DataManager,
						]
					);
				}
			}

			$DataManager = new $DataManager();

			$DataManager->DataType = '\\'.str_replace('/', '\\', $Alias).'DataType';

			if(!class_exists($DataManager->DataType, false)) {
				include($GLOBALS['Config']->Files['DataTypes'].str_replace('\\', '/', $Alias).'DataType.php');

				if(!class_exists($DataManager->DataType, false)) {
					throw new RuntimeException(
						'Unable to load data manager.',
						[
							'File' => $GLOBALS['Config']->Files['DataTypes'].$Alias.'DataManager.php',
							'Class' => $DataManager->DataType,
						]
					);
				}
			}

			return $this->DataManagers[$Alias] = $DataManager;
		}
	}

	public function GetTranslation($Key, $DefaultValue = null) {
		return isset($this->Translations[$Key]) ? $this->Translations[$Key] : $DefaultValue;
	}

	public function SetTranslation($Key, $Value) {
		$this->Translations[$Key] = $Value;
	}
}
?>