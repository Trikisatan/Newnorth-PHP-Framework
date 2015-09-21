<?
namespace Framework\Newnorth;

/* Life cycle stage definitions */

define('LIFECYCLESTAGE_PREINITIALIZE', 1);

define('LIFECYCLESTAGE_INITIALIZE', 2);

define('LIFECYCLESTAGE_POSTINITIALIZE', 3);

define('LIFECYCLESTAGE_PRELOAD', 4);

define('LIFECYCLESTAGE_LOAD', 5);

define('LIFECYCLESTAGE_POSTLOAD', 6);

define('LIFECYCLESTAGE_PREEXECUTE', 7);

define('LIFECYCLESTAGE_EXECUTE', 8);

define('LIFECYCLESTAGE_POSTEXECUTE', 9);

define('LIFECYCLESTAGE_RENDER', 10);

require('Exception.php');
require('ConfigException.php');
require('RuntimeException.php');
require('RerouteException.php');
require('RedirectException.php');
require('ErrorHandler.php');
require('Logger.php');
require('EMailer.php');
require('Translator.php');
require('Routing.php');
require('Config.php');
require('Router.php');
require('Application.php');
require('Layout.php');
require('Page.php');
require('Action.php');
require('Actions.php');
require('EMail.php');
require('HtmlRenderer.php');
require('JsonRenderer.php');
require('Route.php');
require('Translations.php');
require('Control.php');
require('Controls.php');

require('Db.php');

require('ADataManager.php');

require('ATranslationDataManager.php');

require('DataType.php');

require('ADataMember.php');

require('AValueDataMember.php');

require('BoolValueDataMember.php');

require('FloatValueDataMember.php');

require('IntValueDataMember.php');

require('StringValueDataMember.php');

require('ReferenceDataMember.php');

require('DataReference.php');

require('DataList.php');

function Initialize() {
	session_start();

	ob_start();

	set_error_handler('\Framework\Newnorth\ConfigErrorHandler');

	$GLOBALS['Url'] = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/';

	$GLOBALS['Objects'] = [];

	try {
		$GLOBALS['Config'] = new \Framework\Newnorth\Config('Config.ini');

		$GLOBALS['Config']->Initialize();

		try {
			$GLOBALS['Routing'] = new \Framework\Newnorth\Routing('Routes.json');

			$GLOBALS['Routing']->Initialize();

			if(!\Framework\Newnorth\Router::ParseUrl($GLOBALS['Url'], $GLOBALS['Route'], $GLOBALS['RealRoute'], $GLOBALS['Parameters'])) {
				header('HTTP/1.0 404 Not Found');

				$GLOBALS['Route'] = null;

				$GLOBALS['RealRoute'] = null;

				$GLOBALS['Parameters'] = $GLOBALS['Config']->ErrorHandling['Pages']['NotFound'];

				$GLOBALS['Parameters']['Route'] = $GLOBALS['Parameters']['Page'];

				$GLOBALS['Parameters']['RealRoute'] = $GLOBALS['Parameters']['Page'];

				if(!isset($GLOBALS['Parameters']['Locale'])) {
					$GLOBALS['Parameters']['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
				}
			}
		}
		catch(\Framework\Newnorth\Exception $Exception) {
			$GLOBALS['Routing'] = new \Framework\Newnorth\Routing();

			header('HTTP/1.0 500 Internal Server Error');

			$GLOBALS['Route'] = null;

			$GLOBALS['RealRoute'] = null;

			$GLOBALS['Parameters'] = $GLOBALS['Config']->ErrorHandling['Pages']['Error'];

			$GLOBALS['Parameters']['Route'] = $GLOBALS['Parameters']['Page'];

			$GLOBALS['Parameters']['RealRoute'] = $GLOBALS['Parameters']['Page'];

			if(!isset($GLOBALS['Parameters']['Locale'])) {
				$GLOBALS['Parameters']['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
			}
var_dump(\Framework\Newnorth\ErrorHandler::FormatException($Exception));die();
			$GLOBALS['Parameters']['Error'] = \Framework\Newnorth\ErrorHandler::FormatException($Exception);
		}
	}
	catch(\Framework\Newnorth\Exception $Exception) {
		$GLOBALS['Config'] = new \Framework\Newnorth\Config();

		$GLOBALS['Routing'] = new \Framework\Newnorth\Routing();

		header('HTTP/1.0 500 Internal Server Error');

		$GLOBALS['Route'] = null;

		$GLOBALS['RealRoute'] = null;

		$GLOBALS['Parameters'] = $GLOBALS['Config']->ErrorHandling['Pages']['Error'];

		$GLOBALS['Parameters']['Route'] = $GLOBALS['Parameters']['Page'];

		$GLOBALS['Parameters']['RealRoute'] = $GLOBALS['Parameters']['Page'];

		if(!isset($GLOBALS['Parameters']['Locale'])) {
			$GLOBALS['Parameters']['Locale'] = $GLOBALS['Config']->Defaults['Locale'];
		}

		$GLOBALS['Parameters']['Error'] = \Framework\Newnorth\ErrorHandler::FormatException($Exception);
	}
}

function Run() {
	set_error_handler('\Framework\Newnorth\RuntimeErrorHandler');

	try
	{
		try
		{
			if(isset($GLOBALS['Parameters']['Locale'])) {
				$GLOBALS['Translations'] = new \Framework\Newnorth\Translations('Translations.'.$GLOBALS['Parameters']['Locale'].'.ini');
			}
			else {
				$GLOBALS['Translations'] = new \Framework\Newnorth\Translations('Translations.ini');
			}

			$GLOBALS['Translations']->Initialize();

			\Framework\Newnorth\Application::Instantiate($GLOBALS['Parameters']['Application'], $GLOBALS['Application']);

			\Framework\Newnorth\Layout::Instantiate($GLOBALS['Parameters']['Layout'], $GLOBALS['Layout']);

			\Framework\Newnorth\Page::Instantiate($GLOBALS['Parameters']['Page'], $GLOBALS['Page']);

			$GLOBALS['Application']->PreInitialize();

			$GLOBALS['Application']->Initialize();

			$GLOBALS['Application']->PostInitialize();

			$GLOBALS['Application']->PreLoad();

			$GLOBALS['Application']->Load();

			$GLOBALS['Application']->PostLoad();

			$GLOBALS['Application']->PreExecute();

			$GLOBALS['Application']->Execute();

			$GLOBALS['Application']->PostExecute();

			$GLOBALS['Application']->Render();
		}
		catch(\Framework\Newnorth\RedirectException $Exception) {
			throw $Exception;
		}
		catch(\Framework\Newnorth\RerouteException $Exception) {
			throw $Exception;
		}
		catch(\Exception $Exception) {
			\Framework\Newnorth\ErrorHandler::HandleException($Exception);
		}
	}
	catch(\Framework\Newnorth\RedirectException $Exception) {
		header('Location: '.$Exception->Url);
	}
	catch(\Framework\Newnorth\RerouteException $Exception) {
		$GLOBALS['Parameters'] = $Exception->Parameters;

		Run();
	}
}

function ParseIniFile($FilePath, $Split = true, $Combine = false) {
	$Data = parse_ini_file($FilePath, true);

	if($Split) {
		foreach($Data as $SectionKey => &$SectionValue) {
			$SectionKeys = explode('/', $SectionKey);

			if(1 < count($SectionKeys)) {
				unset($Data[$SectionKey]);

				$Var = &$Data[$SectionKeys[0]];

				for($I = 1; $I < count($SectionKeys); ++$I) {
					$Var = &$Var[$SectionKeys[$I]];
				}

				$Var = $SectionValue;

				$SectionValue = &$Var;
			}

			if(is_array($SectionValue)) {
				foreach($SectionValue as $Key => $Value) {
					$Keys = explode('/', $Key);

					if(1 < count($Keys)) {
						unset($SectionValue[$Key]);

						$Var = &$SectionValue[$Keys[0]];

						for($I = 1; $I < count($Keys); ++$I) {
							$Var = &$Var[$Keys[$I]];
						}

						$Var = $Value;
					}
				}
			}
		}
	}
	else if($Combine) {
		foreach($Data as $KeyLevel1 => $ValueLevel1) {
			if(is_array($ValueLevel1)) {
				unset($Data[$KeyLevel1]);

				foreach($ValueLevel1 as $KeyLevel2 => $ValueLevel2) {
					if(is_array($ValueLevel2)) {
						foreach($ValueLevel2 as $KeyLevel3 => $ValueLevel3) {
							$Data[$KeyLevel1.'/'.$KeyLevel2.'/'.$KeyLevel3] = $ValueLevel3;
						}
					}
					else {
						$Data[$KeyLevel1.'/'.$KeyLevel2] = $ValueLevel2;
					}
				}
			}
		}
	}

	return $Data;
}

function ConfigErrorHandler($Type, $Message, $File, $Line, $Variables) {
	if(error_reporting() !== 0) {
		throw new ConfigException($Message);
	}
}

function RuntimeErrorHandler($Type, $Message, $File, $Line, $Variables) {
	if(error_reporting() !== 0) {
		throw new RuntimeException($Message);
	}
}

function RegisterObject($Object) {
	$GLOBALS['Objects'][$Object->__toString()] = $Object;
}

function GetObject($Scope, $Name) {
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

	if(isset($GLOBALS['Objects'][$Name])) {
		return $GLOBALS['Objects'][$Name];
	}
	else {
		return null;
	}
}
?>