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
		Page::$Actions->Load();
		Page::$Controls->PreInitialize();
	}
	public abstract function Initialize();
	public function PostInitialize() {
		Page::$Controls->Initialize();
		Page::$Controls->PostInitialize();
	}
	public function PreLoad() {
		Page::$Controls->PreLoad();
	}
	public abstract function Load();
	public function PostLoad() {
		Page::$Controls->Load();
		Page::$Controls->PostLoad();
	}
	public function PreExecute() {
		Page::$Actions->Execute();
		Page::$Controls->PreExecute();
	}
	public abstract function Execute();
	public function PostExecute() {
		Page::$Controls->Execute();
		Page::$Controls->PostExecute();
	}
	public function Render($PlaceHolder) {
		HtmlRenderer::Render(
			null,
			'Application/'.Page::$Directory.Page::$Name.'/'.$PlaceHolder.'.phtml',
			Page::$Translations
		);
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

	/* Methods */
	public function GetControl($Alias) {
		return Page::$Controls[$Alias];
	}
}
?>