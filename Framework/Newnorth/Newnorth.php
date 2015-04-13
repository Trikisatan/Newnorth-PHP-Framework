<?
namespace Framework\Newnorth;

require('Exception.php');
require('ConfigException.php');
require('RuntimeException.php');
require('RerouteException.php');
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
require('DataManager.php');
require('DataType.php');
require('Db.php');

/* Miscellaneous methods */

function ParseIniFile($FilePath, $Split = true) {
	$Data = parse_ini_file($FilePath, true);

	if($Split) {
		foreach($Data as $SectionKey => &$SectionValue) {
			if(is_array($SectionValue)) {
				foreach($SectionValue as $Key => $Value) {
					$Keys = explode('_', $Key);

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

	return $Data;
}
?>