<?php
namespace Framework\Newnorth;

abstract class Page extends Validators {
	/* Static variables */
	private static $Instance = null;
	private static $Directory;
	private static $Name;
	private static $Translations;
	private static $Controls;
	private static $Actions;

	/* Magic methods */
	public function __construct($Directory, $Name) {
		if(Page::$Instance !== null) {
			ConfigError(
				'An instance of the layout has already been initialized.'
			);
		}

		Page::$Instance = $this;
		Page::$Directory = $Directory;
		Page::$Name = $Name;
		Page::$Translations = new Translations($Directory.$Name.'/');
		Page::$Controls = new Controls($Directory.$Name.'/');
		Page::$Actions = new Actions($this, $Directory.$Name.'/');
	}
	public function __toString() {
		return Page::$Directory.Page::$Name;
	}

	/* Events */
	public function PreInitialize() {
		Page::$Translations->Load();
		Page::$Controls->Load();
		Page::$Actions->Load();

		foreach(Page::$Controls as $Control) {
			$Control->PreInitialize();
		}
	}
	public abstract function Initialize();
	public function PostInitialize() {
		foreach(Page::$Controls as $Control) {
			$Control->Initialize();
		}

		foreach(Page::$Controls as $Control) {
			$Control->PostInitialize();
		}
	}
	public function PreLoad() {
		foreach(Page::$Controls as $Control) {
			$Control->PreLoad();
		}
	}
	public abstract function Load();
	public function PostLoad() {
		foreach(Page::$Controls as $Control) {
			$Control->Load();
		}

		foreach(Page::$Controls as $Control) {
			$Control->PostLoad();
		}
	}
	public function PreExecute() {
		Page::$Actions->Execute();

		foreach(Page::$Controls as $Control) {
			$Control->PreExecute();
		}
	}
	public abstract function Execute();
	public function PostExecute() {
		foreach(Page::$Controls as $Control) {
			$Control->Execute();
		}

		foreach(Page::$Controls as $Control) {
			$Control->PostExecute();
		}
	}
	public function Render($PlaceHolder) {
		$Application = Application::GetInstance();
		$Layout = Layout::GetInstance();

		$Output[0] = ob_get_contents();
		ob_clean();

		include('Application/'.Page::$Directory.Page::$Name.'/'.$PlaceHolder.'.phtml');

		$Output[1] = ob_get_contents();
		ob_clean();

		Page::$Translations->Translate($Output[1]);

		echo $Output[0].$Output[1];
	}

	/* Static methods */
	public static function GetInstance() {
		return Page::$Instance;
	}
	public static function GetDirectory() {
		return Page::$Directory;
	}
	public static function GetName() {
		return Page::$Name;
	}
	public static function GetTranslation($Key, $DefaultValue = null) {
		return isset(Page::$Translations[$Key]) ? Page::$Translations[$Key] : $DefaultValue;
	}
	public static function SetTranslation($Key, $Value) {
		Page::$Translations[$Key] = $Value;
	}
	public static function RenderControl($Alias) {
		Page::$Controls[$Alias]->Render();
	}
}
?>