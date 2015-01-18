<?
namespace Framework\Newnorth;

class Application {
	/* Static variables */

	static private $Instance = null;

	static private $Url;

	static private $Config;

	static private $Files = [
		'DataManagers' => '',
		'DataTypes' => '',
	];

	static private $DefaultLocale;

	static private $DisplayErrors;

	static private $DisplayErrorDetails;

	static private $LogErrors;

	static private $LogFile;

	static private $EMailErrors;

	static private $EMailFrom;

	static private $EMailTo;

	static private $ErrorPage;

	static private $Cache = null;

	static private $Connections = array();

	static private $DataManagers = array();

	static private $Routes = array();

	static private $Parameters = null;

	static private $Locale = null;

	static private $Page = null;

	static private $Layout = null;

	/* Static methods */

	static private function LoadConfig($FilePath) {
		$FilePath = $FilePath.'.ini';
		$Config = ParseIniFile($FilePath);

		if($Config === false) {
			ConfigError(
				'Unable to load the application\'s configuration.',
				array(
					'File' => $FilePath,
				)
			);
		}

		Application::$Config = $Config;

		if(isset($Config['Files'])) {
			Application::LoadConfig_Files($Config['Files']);
		}

		Application::$DefaultLocale = isset($Config['DefaultLocale']) ? $Config['DefaultLocale'] : '';

		Application::$DisplayErrors = isset($Config['ErrorHandling']['DisplayErrors']) ? $Config['ErrorHandling']['DisplayErrors'] === '1' : true;

		Application::$DisplayErrorDetails = isset($Config['ErrorHandling']['DisplayErrorDetails']) ? $Config['ErrorHandling']['DisplayErrorDetails'] === '1' : true;

		Application::$LogErrors = isset($Config['ErrorHandling']['LogErrors']) ? $Config['ErrorHandling']['LogErrors'] === '1' : true;

		Application::$LogFile = isset($Config['ErrorHandling']['LogFile']) ? $Config['ErrorHandling']['LogFile'] : 'errors.log';

		Application::$EMailErrors = isset($Config['ErrorHandling']['EMailErrors']) ? $Config['ErrorHandling']['EMailErrors'] === '1' : false;

		Application::$EMailFrom = isset($Config['ErrorHandling']['EMailFrom']) ? $Config['ErrorHandling']['EMailFrom'] : '';

		Application::$EMailTo = isset($Config['ErrorHandling']['EMailTo']) ? $Config['ErrorHandling']['EMailTo'] : '';

		Application::$ErrorPage = isset($Config['ErrorHandling']['ErrorPage']) ? $Config['ErrorHandling']['ErrorPage'] : '';

		if(isset($Config['Connections'])) {
			foreach($Config['Connections'] as $Name => $Data) {
				if(!isset($Data['Type'])) {
					ConfigError(
						'Connection\'s type not set.',
						array(
							'ConfigFile' => $FilePath,
							'Connection' => $Name,
						)
					);
				}

				if(!class_exists($Data['Type'], false)) {
					ConfigError(
						'Connection\'s type not found.',
						array(
							'ConfigFile' => $FilePath,
							'Connection' => $Name,
							'Type' => $Data['Type'],
						)
					);
				}

				Application::$Connections[$Name] = new $Data['Type']($Data);
			}
		}
	}

	static private function LoadConfig_Files($Section) {
		Application::$Files['DataManagers'] = isset($Section['DataManagers']) ? $Section['DataManagers'] : Application::$Files['DataManagers'];

		Application::$Files['DataTypes'] = isset($Section['DataTypes']) ? $Section['DataTypes'] : Application::$Files['DataTypes'];
	}

	static public function HasConfig($Section) {
		return isset(Application::$Config[$Section]);
	}

	static public function GetConfig($Section) {
		return Application::$Config[$Section];
	}

	static public function HasParameter($Name) {
		return isset(Application::$Parameters[$Name]);
	}

	static public function GetParameter($Name) {
		return Application::$Parameters[$Name];
	}

	static public function GetLocale() {
		return Application::$Locale;
	}

	static public function GenerateUrl(array $Parameters) {
		$Parameters['Page'] = isset($Parameters['Page']) ? $Parameters['Page'] : Application::$Parameters['Page'];
		$Locale = isset($Parameters['Locale']) ? $Parameters['Locale'] : Application::$Locale;

		if(isset($Parameters['Route'][0])) {
			$Route = Application::$Routes[$Route];
			$RouteParameters = array_slice($Parameters, 0);

			$Route->SetDefaults($RouteParameters);

			foreach(Application::$Parameters as $Key => $Value) {
				if(is_int($Key)) {
					continue;
				}

				if(isset($RouteParameters[$Key])) {
					continue;
				}

				$RouteParameters[$Key.'?'] = $Value;
			}

			$Route->ReversedTranslate($RouteParameters, $Locale);

			if($Route->ReversedMatch($RouteParameters, $Locale, $Url)) {
				return $Url;
			}
		}
		else {
			foreach(Application::$Routes as $Route) {
				$RouteParameters = array_slice($Parameters, 0);

				$Route->SetDefaults($RouteParameters);

				foreach(Application::$Parameters as $Key => $Value) {
					if(is_int($Key)) {
						continue;
					}

					if(isset($RouteParameters[$Key])) {
						continue;
					}

					$RouteParameters[$Key.'?'] = $Value;
				}

				$Route->ReversedTranslate($RouteParameters, $Locale);

				if($Route->ReversedMatch($RouteParameters, $Locale, $Url)) {
					return $Url;
				}
			}
		}

		Application::HandleError(
			'Content generation error',
			'Unable to generate URL.',
			array(
				'Parameters' => $Parameters,
			)
		);
	}

	static public function GetConnection($Name) {
		return Application::$Connections[$Name];
	}

	static public function GetDataManager($Name) {
		if(isset(Application::$DataManagers[$Name])) {
			return Application::$DataManagers[$Name];
		}

		$DataManager = '\\'.str_replace('/', '\\', $Name).'DataManager';

		if(!class_exists($DataManager, false)) {
			include(Application::$Files['DataManagers'].$Name.'DataManager.php');

			if(!class_exists($DataManager, false)) {
				throw new \exception('Unable to get the data manager "'.$DataManager.'", it does not exist.');
			}
		}

		$DataManager = new $DataManager();

		$DataManager->DataType = '\\'.str_replace('/', '\\', $Name).'DataType';

		if(!class_exists($DataManager->DataType, false)) {
			include(Application::$Files['DataTypes'].$Name.'DataType.php');

			if(!class_exists($DataManager->DataType, false)) {
				throw new \exception('Unable to get the data type "'.$DataManager->DataType.'", it does not exist.');
			}
		}

		return Application::$DataManagers[$Name] = $DataManager;
	}

	static public function GetToken() {
		return $_SESSION['Token'];
	}

	static public function HandleError($Type, $Message, $File, $Line, $Data, $StackTrace) {
		ob_clean();

		Application::FormatStackTrace($StackTrace);

		$Data = [
			'Url' => Application::$Url,
			'Referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
			'File' => $File,
			'Line' => $Line,
			'Data' => $Data,
			'StackTrace' => $StackTrace,
		];

		if(Application::$LogErrors) {
			Application::LogError($Type, $Message, $Data);
		}

		if(Application::$EMailErrors) {
			Application::EMailError($Type, $Message, $Data);
		}

		if(isset(Application::$ErrorPage[0])) {
			Application::ShowErrorPage($Type, $Message, $Data);
		}
		else if(Application::$DisplayErrors) {
			Application::PrintError($Type, $Message, $Data);
		}

		exit();
	}

	static private function FormatStackTrace(&$StackTrace) {
		for($I = 0; $I < count($StackTrace); ++$I) {
			$Row = $StackTrace[$I];
			$String = '';

			if(isset($Row['class'])) {
				$String .= $Row['class'].$Row['type'];
			}

			$String .= $Row['function'].'(...)';

			if(isset($Row['file'], $Row['line'])) {
				$String .= ' in '.$Row['file'].' on line '.$Row['line'];
			}

			$StackTrace[$I] = $String;
		}
	}

	static public function LoadCache($Key, $TimeToLive, &$Data) {
		$Path = 'Cache/'.md5($Key);

		if(!file_exists($Path)) {
			return false;
		}

		$Data = @file_get_contents($Path, $Data);

		if($Data === false) {
			return false;
		}
		
		$Newline = strpos($Data, "\n");
		$Time = (int)substr($Data, 0, $Newline);
		$Data = substr($Data, $Newline + 1);

		if($Time < time() - $TimeToLive) {
			unlink($Path);
			return false;
		}

		return true;
	}

	static private function LoadPageCache() {
		if(!isset(Application::$Config['Cache'][Application::$Url])) {
			return false;
		}

		if(!Application::LoadCache(Application::$Url, Application::$Config['Cache'][Application::$Url], $Contents)) {
			return false;
		}

		Application::$Cache = $Contents;
		return true;
	}

	static public function SaveCache($Key, $Data) {
		file_put_contents('Cache/'.md5($Key), time()."\n".$Data);
	}

	static public function SavePageCache() {
		if(!isset(Application::$Config['Cache'][Application::$Url])) {
			return false;
		}

		Application::SaveCache(Application::$Url, ob_get_contents());
	}

	static private function LogError($Type, $Message, $Data) {
		$Data = array_merge(
			array(
				'Type' => $Type,
				'Message' => $Message,
			),
			$Data
		);

		try {
			file_put_contents(
				Application::$LogFile,
				json_encode($Data)."\n",
				FILE_APPEND
			);
		}
		catch(Exception $Exception) { }
	}

	static private function EMailError($Type, $Message, $Data) {
		if(isset(Application::$EMailTo[0])) {
			$EMail = new EMail();

			if(isset(Application::$EMailFrom[0])) {
				$EMail->SetFrom(Application::$EMailFrom);
			}

			$EMail->SetSubject($Type.': '.$Message);
			$EMail->SetHtml(
				'<b>'.$Type.'</b><br />'.
				htmlspecialchars($Message).
				Application::CreateErrorMessage(null, $Data)
			);

			try {
				$EMail->Send(Application::$EMailTo);
			}
			catch(Exception $Exception) { }
		}
	}

	static private function ShowErrorPage($Type, $Message, $Data) {
		try {
			require(Application::$ErrorPage);
		}
		catch(Exception $Exception) { }
	}

	static private function PrintError($Type, $Message, $Data) {
		if(Application::$DisplayErrorDetails) {
			echo '<b>', $Type, '</b><br />', htmlspecialchars($Message), Application::CreateErrorMessage(null, $Data);
		}
		else {
			echo '<b>', $Type, '</b><br />', htmlspecialchars($Message);
		}
	}

	static private function CreateErrorMessage($Section, $Data) {
		if($Section === null) {
			$Message = '';

			foreach($Data as $Section => $SubData) {
				$SubMessage = Application::CreateErrorMessage($Section, $SubData);

				if(isset($SubMessage[0])) {
					if(is_int($Section)) {
						$Message .= $SubMessage;
					}
					else if(is_array($SubData)) {
						$Message .= '<br />'.$SubMessage;
					}
					else {
						$Message .= $SubMessage;
					}
				}
			}

			return $Message;
		}

		if(is_int($Section)) {
			return '<br />'.htmlspecialchars($Data);
		}

		if(is_array($Data)) {
			if(count($Data) === 0) {
				return '';
			}

			$Message = '<br /><b>'.$Section.'</b>';

			foreach($Data as $SubSection => $SubData) {
				$Message .= Application::CreateErrorMessage($SubSection, $SubData);
			}

			return $Message;
		}

		return '<br /><b>'.$Section.':</b> '.htmlspecialchars($Data);
	}

	/* Magic methods */

	public function __construct($ConfigFilePath = 'Config', $RoutesFilePath = 'Routes') {
		if(Application::$Instance !== null) {
			ConfigError(
				'An instance of the application has already been initialized.'
			);
		}

		if(!isset($_SESSION['Token'])) {
			$_SESSION['Token'] = md5(rand());
		}

		Application::$Instance = $this;

		Application::$Url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/';

		Application::LoadConfig($ConfigFilePath);

		if(!Application::LoadPageCache()) {
			$this->LoadRoutes($RoutesFilePath);
			$this->ParseUrl();
			$this->LoadLayout();
			$this->LoadPage();
		}
	}

	public function __toString() {
		return '';
	}

	/* Methods */

	private function LoadRoutes($FilePath) {
		$FilePath = $FilePath.'.ini';
		$Routes = ParseIniFile($FilePath);

		if($Routes === false) {
			ConfigError(
				'Unable to load the application\'s routes.',
				array(
					'File' => $FilePath,
				)
			);
		}

		foreach($Routes as $Name => $Data) {
			if(!isset($Data['Pattern'])) {
				ConfigError(
					'No pattern set for route.',
					array(
						'File' => $FilePath,
						'Route' => $Name,
					)
				);
			}

			Application::$Routes[$Name] = new \Framework\Newnorth\Route(
				$Name,
				$Data['Pattern'],
				isset($Data['Requirements']) ? $Data['Requirements'] : array(),
				isset($Data['Translations']) ? $Data['Translations'] : array(),
				isset($Data['Defaults']) ? $Data['Defaults'] : array()
			);
		}
	}

	private function ParseUrl() {
		foreach(Application::$Routes as $Route) {
			if($Route->Match(Application::$Url, $Parameters)) {
				// Locale is required, either through the route
				// or through a session variable.
				if(isset($Parameters['Locale'][0])) {
					Application::$Locale = $Parameters['Locale'];
				}
				else if(isset($_SESSION['Locale'][0])) {
					Application::$Locale = $_SESSION['Locale'];
				}
				else if(isset(Application::$DefaultLocale[0])) {
					Application::$Locale = Application::$DefaultLocale;
				}
				else {
					ConfigError(
						'Locale not set.',
						array(
							'Route' => $Route->GetName(),
						)
					);
				}

				if(!$Route->Translate($Parameters, Application::$Locale)) {
					continue;
				}

				Application::$Parameters = $Parameters;

				// Layout is optional, is for example not used when
				// presenting pages with JSON-data.
				if(isset($Parameters['Layout'])) {
					Application::$Layout = isset($Parameters['Layout'][0]) ? $Parameters['Layout'].'Layout' : null;
				}

				// Page is required.
				if(!isset($Parameters['Page'])) {
					ConfigError(
						'Page not set.',
						array(
							'Route' => $Route->GetName(),
						)
					);
				}

				Application::$Page = $Parameters['Page'].'Page';
				return;
			}
		}

		ConfigError(
			'Unable to match the URL to a route.'
		);
	}

	private function LoadLayout() {
		if(Application::$Layout === null) {
			return;
		}

		$Path = Application::$Layout.'.php';
		$Class = str_replace('/', '\\', Application::$Layout);

		if(class_exists($Class, false)) {
			ConfigError(
				'Layout already loaded.',
				array(
					'Path' => $Path,
					'Class' => $Class,
				)
			);
		}

		try {
			include($Path);
		}
		catch(\Exception $Exception) {
			ConfigError(
				'Unable to load layout.',
				array(
					'Path' => $Path,
					'Class' => $Class,
				)
			);
		}

		if(!class_exists($Class, false)) {
			ConfigError(
				'Unable to load layout.',
				array(
					'Path' => $Path,
					'Class' => $Class,
				)
			);
		}

		Application::$Layout = $Class;
	}

	private function LoadPage() {
		$Path = Application::$Page.'.php';
		$Class = str_replace('/', '\\', Application::$Page);

		if(class_exists($Class, false)) {
			ConfigError(
				'Page already loaded.',
				array(
					'Path' => $Path,
					'Class' => $Class,
				)
			);
		}

		try {
			include($Path);
		}
		catch(\Exception $Exception) {
			ConfigError(
				'Unable to load page.',
				array(
					'Path' => $Path,
					'Class' => $Class,
					'Exception' => $Exception,
				)
			);
		}

		if(!class_exists($Class, false)) {
			ConfigError(
				'Unable to load page.',
				array(
					'Path' => $Path,
					'Class' => $Class,
				)
			);
		}

		Application::$Page = $Class;
	}

	public function Run() {
		global $InitializationTime, $LoadTime, $ExecutionTime, $RenderTime;
		
		if(Application::$Cache === null) {
			if(Application::$Layout === null) {
				global $Page;

				$Directory = strrpos(Application::$Page, '\\');
				$Page = new Application::$Page(
					$Directory === false ? '' : str_replace('\\', '/', substr(Application::$Page, 0, $Directory + 1)),
					$Directory === false ? Application::$Page : substr(Application::$Page, $Directory + 1)
				);

				$start = microtime(true);
				$Page->PreInitialize();
				$Page->Initialize();
				$Page->PostInitialize();
				$InitializationTime = microtime(true) - $start;

				$start = microtime(true);
				$Page->PreLoad();
				$Page->Load();
				$Page->PostLoad();
				$LoadTime = microtime(true) - $start;

				$start = microtime(true);
				$Page->PreExecute();
				$Page->Execute();
				$Page->PostExecute();
				$ExecutionTime = microtime(true) - $start;

				$start = microtime(true);
				$Page->Render();
				$RenderTime = microtime(true) - $start;
			}
			else {
				global $Layout, $Page;

				$Directory = strrpos(Application::$Page, '\\');
				$Layout = new Application::$Layout(
					$Directory === false ? '' : str_replace('\\', '/', substr(Application::$Layout, 0, $Directory + 1)),
					$Directory === false ? Application::$Layout : substr(Application::$Layout, $Directory + 1)
				);

				$Directory = strrpos(Application::$Page, '\\');
				$Page = new Application::$Page(
					$Directory === false ? '' : str_replace('\\', '/', substr(Application::$Page, 0, $Directory + 1)),
					$Directory === false ? Application::$Page : substr(Application::$Page, $Directory + 1)
				);

				$start = microtime(true);
				$Page->PreInitialize();
				$Layout->PreInitialize();
				$Page->Initialize();
				$Layout->Initialize();
				$Page->PostInitialize();
				$Layout->PostInitialize();
				$InitializationTime = microtime(true) - $start;

				$start = microtime(true);
				$Page->PreLoad();
				$Layout->PreLoad();
				$Page->Load();
				$Layout->Load();
				$Page->PostLoad();
				$Layout->PostLoad();
				$LoadTime = microtime(true) - $start;

				$start = microtime(true);
				$Page->PreExecute();
				$Layout->PreExecute();
				$Page->Execute();
				$Layout->Execute();
				$Page->PostExecute();
				$Layout->PostExecute();
				$ExecutionTime = microtime(true) - $start;

				$start = microtime(true);
				$Layout->Render();
				$RenderTime = microtime(true) - $start;
			}

			Application::SavePageCache();
		}
		else
		{
			$InitializationTime = 0;
			$LoadTime = 0;
			$ExecutionTime = 0;

			$start = microtime(true);
			echo Application::$Cache;
			$RenderTime = microtime(true) - $start;
		}
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

		foreach($Control->_Parameters['Options'] as $Option) {
			if($Option['Value'] === $Value) {
				return true;
			}
		}

		return false;
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
}
?>