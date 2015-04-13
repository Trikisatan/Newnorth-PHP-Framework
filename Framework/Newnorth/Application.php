<?
namespace Framework\Newnorth;

class Application {
	/* Instance variables */

	public $Url;

	private $DbConnections = [];

	private $DataManagers = [];

	public $Translations;

	public $IsExecuting = false;

	/* Magic methods */

	public function __construct() {
		$this->Url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/';
	}

	/* Instance methods */

	public function Initialize() {
		if(!$this->ParseUrl()) {
			if($GLOBALS['Config']->ErrorHandling['RouteNotFound']['Log']) {
				foreach($GLOBALS['Config']->ErrorHandling['LogMethods'] as $LogMethod) {
					call_user_func(
						$LogMethod,
						[
							'Type' => 'Route not found',
							'Message' => 'Unable to parse URL.',
							'Url' => $this->Url,
						]
					);
				}
			}

			if($GLOBALS['Config']->ErrorHandling['RouteNotFound']['Report']) {
				foreach($GLOBALS['Config']->ErrorHandling['ReportMethods'] as $ReportMethod) {
					call_user_func(
						$ReportMethod,
						[
							'Type' => 'Route not found',
							'Message' => 'Unable to parse URL.',
							'Url' => $this->Url,
						]
					);
				}
			}

			if(0 < count($GLOBALS['Config']->ErrorHandling['Pages']['NotFound'])) {
				header('HTTP/1.0 404 Not Found');

				$GLOBALS['Parameters'] = $GLOBALS['Config']->ErrorHandling['Pages']['NotFound'];
			}
		}

		$this->Translations = new Translations($this, '');
	}

	private function ParseUrl() {
		foreach($GLOBALS['Routing']->Routes as $RouteName => $Route) {
			if($Route->Match($this->Url, $Parameters)) {
				if(isset($Parameters['Locale'][0])) {
					$Locale = $Parameters['Locale'];
				}
				else if(isset($Route->Defaults['Locale'][0])) {
					$Locale = $Route->Defaults['Locale'];
				}
				else if(isset($_SESSION['Locale'][0])) {
					$Locale = $_SESSION['Locale'];
				}
				else {
					$Locale = '';
				}

				if($Route->Translate($Parameters, $Locale)) {
					$Route->SetDefaults($Parameters);

					$GLOBALS['Parameters'] = $Parameters;

					if(!isset($GLOBALS['Parameters']['Locale'])) {
						$GLOBALS['Parameters']['Locale'] = $Locale;
					}

					return true;
				}
			}
		}

		return false;
	}

	public function Execute() {
		$this->IsExecuting = true;

		try {
			try {
				if(!isset($GLOBALS['Parameters']['Page'][0])) {
					throw new ConfigException(
						'Page not set.',
						[]
					);
				}
				else if(!isset($GLOBALS['Parameters']['Layout'][0])) {
					$this->Execute_LoadPage();

					$start = microtime(true);
					$GLOBALS['Page']->PreInitialize();
					$GLOBALS['Page']->Initialize();
					$GLOBALS['Page']->PostInitialize();
					$InitializationTime = microtime(true) - $start;

					$start = microtime(true);
					$GLOBALS['Page']->PreLoad();
					$GLOBALS['Page']->Load();
					$GLOBALS['Page']->PostLoad();
					$LoadTime = microtime(true) - $start;

					$start = microtime(true);
					$GLOBALS['Page']->PreExecute();
					$GLOBALS['Page']->Execute();
					$GLOBALS['Page']->PostExecute();
					$ExecutionTime = microtime(true) - $start;

					$GLOBALS['Page']->Render();

					$this->Execute_Translate();
				}
				else {
					$this->Execute_LoadLayout();

					$this->Execute_LoadPage();

					$start = microtime(true);
					$GLOBALS['Layout']->PreInitialize();
					$GLOBALS['Page']->PreInitialize();
					$GLOBALS['Layout']->Initialize();
					$GLOBALS['Page']->Initialize();
					$GLOBALS['Layout']->PostInitialize();
					$GLOBALS['Page']->PostInitialize();
					$InitializationTime = microtime(true) - $start;

					$start = microtime(true);
					$GLOBALS['Layout']->PreLoad();
					$GLOBALS['Page']->PreLoad();
					$GLOBALS['Layout']->Load();
					$GLOBALS['Page']->Load();
					$GLOBALS['Layout']->PostLoad();
					$GLOBALS['Page']->PostLoad();
					$LoadTime = microtime(true) - $start;

					$start = microtime(true);
					$GLOBALS['Layout']->PreExecute();
					$GLOBALS['Page']->PreExecute();
					$GLOBALS['Layout']->Execute();
					$GLOBALS['Page']->Execute();
					$GLOBALS['Layout']->PostExecute();
					$GLOBALS['Page']->PostExecute();
					$ExecutionTime = microtime(true) - $start;

					$GLOBALS['Layout']->Render();

					$this->Execute_Translate();
				}
			}
			catch(RerouteException $Exception) {
				throw $Exception;
			}
			catch(Exception $Exception) {
				ErrorHandler::HandleError(
					$Exception->Type,
					$Exception->getMessage(),
					$Exception->getFile(),
					$Exception->getLine(),
					$Exception->Data,
					$Exception->getTrace()
				);
			}
			catch(\ErrorException $Exception) {
				ErrorHandler::HandleError(
					'Error',
					$Exception->getMessage(),
					$Exception->getFile(),
					$Exception->getLine(),
					[],
					$Exception->getTrace()
				);
			}
			catch(\Exception $Exception) {
				ErrorHandler::HandleError(
					'Unhandled exception',
					$Exception->getMessage(),
					$Exception->getFile(),
					$Exception->getLine(),
					[],
					$Exception->getTrace()
				);
			}
		}
		catch(RerouteException $Exception) {
			$GLOBALS['Parameters'] = $Exception->Parameters;

			$this->Execute();
		}

		$this->IsExecuting = false;
	}

	private function Execute_LoadLayout() {
		$FullName = $GLOBALS['Parameters']['Layout'].'Layout';

		$ClassName = str_replace('/', '\\', $FullName);

		if(!class_exists($ClassName, false)) {
			$FilePath = $GLOBALS['Config']->Files['Layouts'].$FullName.'.php';

			@include($FilePath);

			if(!class_exists($ClassName, false)) {
				throw new RuntimeException(
					'Unable to load layout.',
					[
						'File' => $FilePath,
						'Class' => $ClassName,
					]
				);
			}
		}

		$Name = strrpos($FullName, '/');

		$Name = $Name === false ? $FullName : substr($FullName, $Name + 1);

		$Directory = strrpos($FullName, '/');

		$Directory = $Directory === false ? '' : substr($FullName, 0, $Directory + 1);

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = $Namespace === false ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$GLOBALS['Layout'] = new $ClassName($Directory, $Namespace, $Name);
	}

	private function Execute_LoadPage() {
		$FullName = $GLOBALS['Parameters']['Page'].'Page';

		$ClassName = str_replace('/', '\\', $FullName);

		if(!class_exists($ClassName, false)) {
			$FilePath = $GLOBALS['Config']->Files['Pages'].$FullName.'.php';

			@include($FilePath);

			if(!class_exists($ClassName, false)) {
				throw new RuntimeException(
					'Unable to load page.',
					[
						'File' => $FilePath,
						'Class' => $ClassName,
					]
				);
			}
		}

		$Name = strrpos($FullName, '/');

		$Name = $Name === false ? $FullName : substr($FullName, $Name + 1);

		$Directory = strrpos($FullName, '/');

		$Directory = $Directory === false ? '' : substr($FullName, 0, $Directory + 1);

		$Namespace = strrpos($ClassName, '\\');

		$Namespace = $Namespace === false ? '\\' : substr($ClassName, 0, $Namespace + 1);

		$GLOBALS['Page'] = new $ClassName($Directory, $Namespace, $Name);
	}

	private function Execute_Translate() {
		$Output = ob_get_contents();

		ob_clean();

		$this->Translations->Translate($Output);

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
								'Url' => $this->Url,
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
								'Url' => $this->Url,
								'Missing translations' => $MissingTranslations,
							]
						);
					}
				}
			}
		}

		echo $Output;
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
				include($GLOBALS['Config']->Files['DataManagers'].$Alias.'DataManager.php');

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
				include($GLOBALS['Config']->Files['DataTypes'].$Alias.'DataType.php');

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

	public function GetValidatorMethod($ActionName, $MethodName, &$MethodObject) {
		if(method_exists($this, $MethodName)) {
			$MethodObject = $this;
			return true;
		}

		return false;
	}

	public function GetValidatorRenderMethod($MethodName, &$MethodObject) {
		if(method_exists($this, $MethodName)) {
			$MethodObject = $this;
			return true;
		}

		return false;
	}

	/* Validator methods */

	public function TokenValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'Validator requires a control.',
				[
					'Validator' => 'TokenValidator',
				]
			);
		}

		return $_POST[$Control->_Parameters['Name']] === $_SESSION['Token'];
	}

	public function DropDownListValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'Validator requires a control.',
				[
					'Validator' => 'DropDownListValidator',
				]
			);
		}

		$Value = $_POST[$Control->_Parameters['Name']];

		return isset($Control->_Parameters['Options'][$Value]);
	}

	public function EMailAddressFormatValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'Validator requires a control.',
				[
					'Validator' => 'EMailAddressFormatValidator',
				]
			);
		}

		return 0 < preg_match('/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/', $_POST[$Control->_Parameters['Name']]);
	}

	public function FileUploadedValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'Validator requires a control.',
				[
					'Validator' => 'FileUploadedValidator',
				]
			);
		}

		return 0 < $_FILES[$Control->_Parameters['Name']]['size'];
	}

	public function IsBetweenValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'IsBetweenValidator',
				]
			);
		}

		$Value = (int)$_POST[$Control->_Parameters['Name']];

		return $Control->_Parameters['MinValue'] <= $Value && $Value <= $Control->_Parameters['MaxValue'];
	}

	public function IsDigitsValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'IsDigitsValidator',
				]
			);
		}

		return ctype_digit($_POST[$Control->_Parameters['Name']]);
	}

	public function IsNumericValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'IsNumericValidator',
				]
			);
		}

		return is_numeric($_POST[$Control->_Parameters['Name']]);
	}

	public function MaxLengthValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'MaxLengthValidator',
				]
			);
		}

		return !isset($_POST[$Control->_Parameters['Name']][$Control->_Parameters['MaxLength']]);
	}

	public function MinLengthValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'MinLengthValidator',
				]
			);
		}

		return isset($_POST[$Control->_Parameters['Name']][$Control->_Parameters['MinLength']]);
	}

	public function ValueNotEmptyValidator($Control) {
		if($Control === null) {
			throw new ConfigException(
				'This validator requires a control.',
				[
					'Validator' => 'ValueNotEmptyValidator',
				]
			);
		}

		return isset($_POST[$Control->_Parameters['Name']][0]);
	}

	/* Validator render methods */

	public function RenderValueNotEmptyValidator($Control, $Parameters) {
		return 'return 0<this.value.length';
	}

	public function RenderValueRegExpValidator($Control, $Parameters) {
		return 'return -1<this.value.search('.$Parameters['RegExp'].')';
	}

	public function RenderEmailAddressFormatValidator($Control, $Parameters) {
		return 'return -1<this.value.search(/^([a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+.[a-zA-Z]+)?$/)';
	}
}
?>