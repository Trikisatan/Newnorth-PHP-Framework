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

	public $Stage = 0;

	public $Objects = [];

	private $DbConnections = [];

	private $DataManagers = [];

	/* Magic methods */

	public function __construct() {

	}

	/* Life cycle methods */

	public function PreInitialize() {
		$this->Stage = LIFECYCLESTAGE_PREINITIALIZE;

		$GLOBALS['Layout']->PreInitialize();

		$GLOBALS['Page']->PreInitialize();
	}

	public function Initialize() {
		$this->Stage = LIFECYCLESTAGE_INITIALIZE;

		$GLOBALS['Layout']->Initialize();

		$GLOBALS['Page']->Initialize();
	}

	public function PostInitialize() {
		$this->Stage = LIFECYCLESTAGE_POSTINITIALIZE;

		$GLOBALS['Layout']->PostInitialize();

		$GLOBALS['Page']->PostInitialize();
	}

	public function PreLoad() {
		$this->Stage = LIFECYCLESTAGE_PRELOAD;

		$GLOBALS['Layout']->PreLoad();

		$GLOBALS['Page']->PreLoad();
	}

	public function Load() {
		$this->Stage = LIFECYCLESTAGE_LOAD;

		$GLOBALS['Layout']->Load();

		$GLOBALS['Page']->Load();
	}

	public function PostLoad() {
		$this->Stage = LIFECYCLESTAGE_POSTLOAD;

		$GLOBALS['Layout']->PostLoad();

		$GLOBALS['Page']->PostLoad();
	}

	public function PreExecute() {
		$this->Stage = LIFECYCLESTAGE_PREEXECUTE;

		$GLOBALS['Layout']->PreExecute();

		$GLOBALS['Page']->PreExecute();
	}

	public function Execute() {
		$this->Stage = LIFECYCLESTAGE_EXECUTE;

		$GLOBALS['Layout']->Execute();

		$GLOBALS['Page']->Execute();
	}

	public function PostExecute() {
		$this->Stage = LIFECYCLESTAGE_POSTEXECUTE;

		$GLOBALS['Layout']->PostExecute();

		$GLOBALS['Page']->PostExecute();
	}

	public function Render() {
		$this->Stage = LIFECYCLESTAGE_RENDER;

		$GLOBALS['Layout']->Render();

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
								'Request URI' => $_SERVER['REQUEST_URI'],
								'Request method' => $_SERVER['REQUEST_METHOD'],
								'Request data' => $_POST,
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
								'Request URI' => $_SERVER['REQUEST_URI'],
								'Request method' => $_SERVER['REQUEST_METHOD'],
								'Request data' => $_POST,
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

				$Scope = substr($Scope, 0, $Slash);
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

			$File = $GLOBALS['Config']->Files['DataManagers'].$Alias.'DataManager.php';

			if(class_exists($DataManager, false)) {
				$DataManager = new $DataManager();
			}
			else if(file_exists($File)) {
				include($File);

				if(class_exists($DataManager, false)) {
					$DataManager = new $DataManager();
				}
				else {
					throw new RuntimeException(
						'Unable to load data manager, class not found.',
						[
							'File' => $File,
							'Class' => $DataManager,
						]
					);
				}
			}
			else {
				throw new RuntimeException(
					'Unable to load data manager, file not found.',
					[
						'File' => $File,
						'Class' => $DataManager,
					]
				);
			}

			$DataType = '\\'.str_replace('/', '\\', $Alias).'DataType';

			$File = $GLOBALS['Config']->Files['DataTypes'].$Alias.'DataType.php';

			if(class_exists($DataType, false)) {
				$DataManager->DataType = $DataType;
			}
			else if(file_exists($File)) {
				include($File);

				if(class_exists($DataType, false)) {
					$DataManager->DataType = $DataType;
				}
				else {
					throw new RuntimeException(
						'Unable to load data type, class not found',
						[
							'File' => $File,
							'Class' => $DataType,
						]
					);
				}
			}
			else {
				$DataManager->DataType = '\\Framework\\Newnorth\\DataType';
			}

			$this->DataManagers[$Alias] = $DataManager;

			$DataManager->InitializeDataMembers();

			$DataManager->InitializeReferenceDataMembers();

			$DataManager->InitializeDataReferences();

			$DataManager->InitializeDataLists();

			return $DataManager;
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