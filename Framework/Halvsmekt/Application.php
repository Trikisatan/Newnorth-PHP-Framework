<?php
namespace Framework\Halvsmekt;

class Application {
	/* Static variables */
	private static $Instance = null;
	private static $Url = null;
	private static $Routes = array();
	private static $Parameters = null;
	private static $Locale = null;
	private static $Page = null;
	private static $Layout = null;
	private static $Connections = array();
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
	public static function HasParameter($Name) {
		return isset(Application::$Parameters[$Name]);
	}
	public static function GetParameter($Name) {
		if(!isset(Application::$Parameters[$Name])) {
			throw new \exception('Unable to get the parameter "'.$Name.'", it does not exist.');
		}

		return Application::$Parameters[$Name];
	}
	public static function GetLocale() {
		return Application::$Locale;
	}
	public static function GenerateUrl($Route = null, $Parameters = array()) {
		$Locale = isset($Parameters['Locale']) ? $Parameters['Locale'] : Application::$Locale;

		$Route = Application::$Routes[$Route];

		$Route->SetDefaults($Parameters);

		$Route->ReversedTranslate($Parameters, $Locale);

		if(!$Route->ReversedMatch($Parameters, $Url)) {
			throw new \exception('Unable to generate URL.');
		}

		return $Url;
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

			Application::$Routes[$Name] = new \Framework\Halvsmekt\Route(
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
				str_replace('\\', '/', substr(Application::$Page, 0, strrpos(Application::$Page, '\\') + 1)),
				substr(Application::$Page, strrpos(Application::$Page, '\\') + 1)
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
				str_replace('\\', '/', substr(Application::$Layout, 0, strrpos(Application::$Layout, '\\') + 1)),
				substr(Application::$Layout, strrpos(Application::$Layout, '\\') + 1)
			);

			$Page = new Application::$Page(
				str_replace('\\', '/', substr(Application::$Page, 0, strrpos(Application::$Page, '\\') + 1)),
				substr(Application::$Page, strrpos(Application::$Page, '\\') + 1)
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