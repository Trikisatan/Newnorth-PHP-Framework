<?php
namespace Framework\Halvsmekt;

abstract class Layout extends Validators {
	/* Static variables */
	private static $Instance = null;
	private static $Directory;
	private static $Name;
	private static $Translations;
	private static $Controls;
	private static $Actions;

	/* Magic methods */
	public function __construct($Directory, $Name) {
		if(Layout::$Instance !== null) {
			ConfigError(
				'An instance of the layout has already been initialized.'
			);
		}

		Layout::$Instance = $this;
		Layout::$Directory = $Directory;
		Layout::$Name = $Name;
		Layout::$Translations = new Translations($Directory.$Name.'/');
		Layout::$Controls = new Controls($Directory.$Name.'/');
		Layout::$Actions = new Actions($this, $Directory.$Name.'/');
	}
	public function __toString() {
		return Layout::$Directory.Layout::$Name;
	}

	/* Events */
	public function PreInitialize() {
		Layout::$Translations->Load();
		Layout::$Controls->Load();
		Layout::$Actions->Load();

		foreach(Layout::$Controls as $Control) {
			$Control->PreInitialize();
		}
	}
	public abstract function Initialize();
	public function PostInitialize() {
		foreach(Layout::$Controls as $Control) {
			$Control->Initialize();
		}

		foreach(Layout::$Controls as $Control) {
			$Control->PostInitialize();
		}
	}
	public function PreLoad() {
		foreach(Layout::$Controls as $Control) {
			$Control->PreLoad();
		}
	}
	public abstract function Load();
	public function PostLoad() {
		foreach(Layout::$Controls as $Control) {
			$Control->Load();
		}

		foreach(Layout::$Controls as $Control) {
			$Control->PostLoad();
		}
	}
	public function PreExecute() {
		Layout::$Actions->Execute();

		foreach(Layout::$Controls as $Control) {
			$Control->PreExecute();
		}
	}
	public abstract function Execute();
	public function PostExecute() {
		foreach(Layout::$Controls as $Control) {
			$Control->Execute();
		}

		foreach(Layout::$Controls as $Control) {
			$Control->PostExecute();
		}
	}
	public function Render() {
		$Application = Application::GetInstance();
		$Page = Page::GetInstance();

		$Output[0] = ob_get_contents();
		ob_clean();

		try {
			include('Application/'.Layout::$Directory.Layout::$Name.'/Content.phtml');
		}
		catch(\Exception $Exception) {
			ConfigError(
				'Unable to load layout\'s content.',
				array(
					'Path' => Layout::$Directory.Layout::$Name.'/Content.phtml',
				)
			);
		}

		$Output[1] = ob_get_contents();
		ob_clean();

		Layout::$Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}

	/* Static methods */
	public static function GetInstance() {
		return Layout::$Instance;
	}
	public static function GetDirectory() {
		return Layout::$Directory;
	}
	public static function GetName() {
		return Layout::$Name;
	}
	public static function GetTranslation($Key) {
		return Layout::$Translations[$Key];
	}
	public static function SetTranslation($Key, $Value) {
		Layout::$Translations[$Key] = $Value;
	}
	public static function RenderControl($Alias) {
		Layout::$Controls[$Alias]->Render();
	}
}
?>