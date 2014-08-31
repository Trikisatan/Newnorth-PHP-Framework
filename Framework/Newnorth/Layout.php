<?php
namespace Framework\Newnorth;

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
		Layout::$Actions->Load();
		Layout::$Controls->PreInitialize();
	}
	public abstract function Initialize();
	public function PostInitialize() {
		Layout::$Controls->Initialize();
		Layout::$Controls->PostInitialize();
	}
	public function PreLoad() {
		Layout::$Controls->PreLoad();
	}
	public abstract function Load();
	public function PostLoad() {
		Layout::$Controls->Load();
		Layout::$Controls->PostLoad();
	}
	public function PreExecute() {
		Layout::$Actions->Execute();
		Layout::$Controls->PreExecute();
	}
	public abstract function Execute();
	public function PostExecute() {
		Layout::$Controls->Execute();
		Layout::$Controls->PostExecute();
	}
	public function Render() {
		HtmlRenderer::Render(
			null,
			'Application/'.Layout::$Directory.Layout::$Name.'/Content.phtml',
			Layout::$Translations
		);
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
	public static function GetTranslation($Key, $DefaultValue = null) {
		return isset(Layout::$Translations[$Key]) ? Layout::$Translations[$Key] : $DefaultValue;
	}
	public static function SetTranslation($Key, $Value) {
		Layout::$Translations[$Key] = $Value;
	}
	public static function RenderControl($Alias) {
		Layout::$Controls[$Alias]->Render();
	}

	/* Methods */
	public function GetControl($Alias) {
		return Layout::$Controls[$Alias];
	}
}
?>