<?php
namespace Framework\Newnorth;

class Application {
	/* Static variables */
	private static $Instance = null;
	private static $Url;
	private static $Config;
	private static $DefaultLocale;
	private static $DisplayErrors;
	private static $DisplayErrorDetails;
	private static $LogErrors;
	private static $LogFile;
	private static $EMailErrors;
	private static $EMailFrom;
	private static $EMailTo;
	private static $ErrorPage;
	private static $Connections = array();
	private static $Routes = array();
	private static $Parameters = null;
	private static $Locale = null;
	private static $Page = null;
	private static $Layout = null;
	private static $DataManagers = array();

	/* Magic methods */
	public function __construct($ConfigFilePath = 'Config', $RoutesFilePath = 'Routes') {
		if(Application::$Instance !== null) {
			ConfigError(
				'An instance of the application has already been initialized.'
			);
		}

		Application::$Instance = $this;
		Application::$Url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/';

		$this->LoadConfig($ConfigFilePath);
		$this->LoadRoutes($RoutesFilePath);

		$this->ParseUrl();

		$this->LoadLayout();
		$this->LoadPage();
	}
	public function __toString() {
		return '';
	}

	/* Static methods */
	public static function GetInstance() {
		return Application::$Instance;
	}
	public static function HasConfig($Section) {
		return isset(Application::$Config[$Section]);
	}
	public static function GetConfig($Section) {
		return Application::$Config[$Section];
	}
	public static function HasParameter($Name) {
		return isset(Application::$Parameters[$Name]);
	}
	public static function GetParameter($Name) {
		return Application::$Parameters[$Name];
	}
	public static function GetLocale() {
		return Application::$Locale;
	}
	public static function GenerateUrl($Route = null, $Parameters = array()) {
		$Locale = isset($Parameters['Locale']) ? $Parameters['Locale'] : Application::$Locale;

		if($Route !== null) {
			$Route = Application::$Routes[$Route];

			$Route->SetDefaults($Parameters);

			$Route->ReversedTranslate($Parameters, $Locale);

			if(!$Route->ReversedMatch($Parameters, $Locale, $Url)) {
				throw new \exception('Unable to generate URL.');
			}

			return $Url;
		}

		foreach(Application::$Routes as $Route) {
			$Route->SetDefaults($Parameters);

			$Route->ReversedTranslate($Parameters, $Locale);

			if(!$Route->ReversedMatch($Parameters, $Locale, $Url)) {
				continue;
			}

			return $Url;
		}

		throw new \exception('Unable to generate URL.');
	}
	public static function GetConnection($Name) {
		return Application::$Connections[$Name];
	}
	public static function GetDataManager($Name) {
		if(isset(Application::$DataManagers[$Name])) {
			return Application::$DataManagers[$Name];
		}

		$ClassName = str_replace('/', '\\', $Name).'DataManager';

		if(!class_exists($ClassName, false)) {
			include('Application/'.substr($Name, 1).'DataManager.php');

			if(!class_exists($ClassName, false)) {
				throw new \exception('Unable to get the data manager "'.$ClassName.'", it does not exist.');
			}
		}

		$DataManager = new $ClassName();

		$DataManager->DataType = str_replace('/', '\\', $Name).'DataType';

		if(!class_exists($DataManager->DataType, false)) {
			include('Application/'.substr($Name, 1).'DataType.php');

			if(!class_exists($DataManager->DataType, false)) {
				throw new \exception('Unable to get the data type "'.$DataManager->DataType.'", it does not exist.');
			}
		}

		return Application::$DataManagers[$Name] = $DataManager;
	}
	public static function HandleError($Type, $Message, $Data) {
		ob_clean();

		$Data = array_merge(
			array(
				'Url' => Application::$Url,
				'Referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
			),
			$Data
		);

		Application::FormatStackTrace($Data);

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
	private static function FormatStackTrace(&$Data) {
		if(isset($Data['StackTrace'])) {
			$StackTrace = &$Data['StackTrace'];

			for($I = 0; $I < count($StackTrace); ++$I) {
				$StackTrace[$I] = $StackTrace[$I]['function'].'(...) in '.$StackTrace[$I]['file'].' on line '.$StackTrace[$I]['line'];
			}
		}
	}
	private static function LogError($Type, $Message, $Data) {
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
	private static function EMailError($Type, $Message, $Data) {
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
	private static function ShowErrorPage($Type, $Message, $Data) {
		try {
			require(Application::$ErrorPage);
		}
		catch(Exception $Exception) { }
	}
	private static function PrintError($Type, $Message, $Data) {
		if(Application::$DisplayErrorDetails) {
			echo '<b>', $Type, '</b><br />', htmlspecialchars($Message), Application::CreateErrorMessage(null, $Data);
		}
		else {
			echo '<b>', $Type, '</b><br />', htmlspecialchars($Message);
		}
	}
	private static function CreateErrorMessage($Section, $Data) {
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

	/* Methods */
	private function LoadConfig($FilePath) {
		$FilePath = 'Application/'.$FilePath.'.ini';
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
	private function LoadRoutes($FilePath) {
		$FilePath = 'Application/'.$FilePath.'.ini';
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
			include('Application/'.$Path);
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
			include('Application/'.$Path);
		}
		catch(\Exception $Exception) {
			ConfigError(
				'Unable to load page.',
				array(
					'Path' => $Path,
					'Class' => $Class,
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
		if(Application::$Layout === null) {
			global $Page;

			$Page = new Application::$Page(
				str_replace('\\', '/', substr(Application::$Page, 0, strrpos(Application::$Page, '\\'))),
				substr(Application::$Page, strrpos(Application::$Page, '\\'))
			);

			$Page->PreInitialize();
			$Page->Initialize();
			$Page->PostInitialize();

			$Page->PreLoad();
			$Page->Load();
			$Page->PostLoad();

			$Page->PreExecute();
			$Page->Execute();
			$Page->PostExecute();

			$Page->Render();
		}
		else {
			global $Layout, $Page;

			$Layout = new Application::$Layout(
				str_replace('\\', '/', substr(Application::$Layout, 0, strrpos(Application::$Layout, '\\'))),
				substr(Application::$Layout, strrpos(Application::$Layout, '\\'))
			);

			$Page = new Application::$Page(
				str_replace('\\', '/', substr(Application::$Page, 0, strrpos(Application::$Page, '\\'))),
				substr(Application::$Page, strrpos(Application::$Page, '\\'))
			);

			$Layout->PreInitialize();
			$Page->PreInitialize();
			$Layout->Initialize();
			$Page->Initialize();
			$Layout->PostInitialize();
			$Page->PostInitialize();

			$Layout->PreLoad();
			$Page->PreLoad();
			$Layout->Load();
			$Page->Load();
			$Layout->PostLoad();
			$Page->PostLoad();

			$Layout->PreExecute();
			$Page->PreExecute();
			$Layout->Execute();
			$Page->Execute();
			$Layout->PostExecute();
			$Page->PostExecute();

			$Layout->Render();
		}
	}
}
?>