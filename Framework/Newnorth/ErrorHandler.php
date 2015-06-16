<?
namespace Framework\Newnorth;

class ErrorHandler {
	/* Static methods */

	public static function HandleException($Exception) {
		$Exception = ErrorHandler::FormatException($Exception);

		if($GLOBALS['Config']->ErrorHandling['Log']) {
			foreach($GLOBALS['Config']->ErrorHandling['LogMethods'] as $LogMethod) {
				call_user_func($LogMethod, $Exception);
			}
		}

		if($GLOBALS['Config']->ErrorHandling['Report']) {
			foreach($GLOBALS['Config']->ErrorHandling['ReportMethods'] as $ReportMethod) {
				call_user_func($ReportMethod, $Exception);
			}
		}

		ob_clean();

		if($GLOBALS['Parameters']['Page'] !== $GLOBALS['Config']->ErrorHandling['Pages']['Error']['Page']) {
			Router::RerouteErrorPage($Exception);
		}
		else {
			header('HTTP/1.0 500 Internal Server Error');

			die('500 Internal Server Error');
		}
	}

	public static function FormatException(\Exception $Exception) {
		if($Exception instanceof \Framework\Newnorth\Exception) {
			return [
				'Type' => $Exception->Type,
				'Message' => $Exception->getMessage(),
				'File' => $Exception->getFile(),
				'Line' => $Exception->getLine(),
				'Url' => $GLOBALS['Url'],
				'Referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
				'Data' => $Exception->Data,
				'StackTrace' => ErrorHandler::FormatStackTrace($Exception->getTrace()),
			];
		}
		else {
			return [
				'Type' => 'Unknown exception',
				'Message' => $Exception->getMessage(),
				'File' => $Exception->getFile(),
				'Line' => $Exception->getLine(),
				'Url' => $GLOBALS['Url'],
				'Referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
				'Data' => [],
				'StackTrace' => ErrorHandler::FormatStackTrace($Exception->getTrace()),
			];
		}
	}

	public static function FormatStackTrace(array $OldStackTrace) {
		$NewStackTrace = [];

		foreach($OldStackTrace as $Key => $Value) {
			$NewValue = '';

			if(isset($Value['class'])) {
				$NewValue .= $Value['class'].$Value['type'];
			}

			$NewValue .= $Value['function'].'(...)';

			if(isset($Value['file'])) {
				$NewValue .= ' in '.$Value['file'];
			}

			if(isset($Value['line'])) {
				$NewValue .= ' on line '.$Value['line'];
			}

			$NewStackTrace[$Key] = $NewValue;
		}

		return $NewStackTrace;
	}
}
?>