<?
namespace Framework\Newnorth;

class ErrorHandler {
	/* Static methods */

	static public function HandleError($Type, $Message, $File, $Line, $Data, $StackTrace) {
		ob_clean();

		header('HTTP/1.0 500 Internal Server Error');

		$Data = [
			'Type' => $Type,
			'Message' => $Message,
			'File' => $File,
			'Line' => $Line,
			'Url' => isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/',
			'Referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
			'Data' => $Data,
			'StackTrace' => ErrorHandler::FormatStackTrace($StackTrace),
		];

		if($GLOBALS['Application']->IsExecuting) {
			if($GLOBALS['Config']->ErrorHandling['Log']) {
				foreach($GLOBALS['Config']->ErrorHandling['LogMethods'] as $LogMethod) {
					call_user_func($LogMethod, $Data);
				}
			}

			if($GLOBALS['Config']->ErrorHandling['Report']) {
				foreach($GLOBALS['Config']->ErrorHandling['ReportMethods'] as $ReportMethod) {
					call_user_func($ReportMethod, $Data);
				}
			}

			if(0 < count($GLOBALS['Config']->ErrorHandling['Pages']['Error'])) {
				if($GLOBALS['Parameters']['Page'] !== $GLOBALS['Config']->ErrorHandling['Pages']['Error']['Page']) {
					$Parameters = $GLOBALS['Config']->ErrorHandling['Pages']['Error'];

					$Parameters['Error'] = $Data;

					throw new RerouteException($Parameters);
				}
				else if($GLOBALS['Config']->ErrorHandling['DisplayErrorMessages']) {
					if(isset($GLOBALS['Parameters']['Page']['Error'])) {
						echo ErrorHandler::FormatErrorMessageHtml(
							$GLOBALS['Parameters']['Page']['Error'],
							$GLOBALS['Config']->ErrorHandling['DisplayErrorMessageDetails']
						);
					}
					else {
						echo ErrorHandler::FormatErrorMessageHtml(
							$Data,
							$GLOBALS['Config']->ErrorHandling['DisplayErrorMessageDetails']
						);
					}
				}
			}
			else if($GLOBALS['Config']->ErrorHandling['DisplayErrorMessages']) {
				echo ErrorHandler::FormatErrorMessageHtml(
					$Data,
					$GLOBALS['Config']->ErrorHandling['DisplayErrorMessageDetails']
				);
			}
		}

		exit();
	}

	static private function FormatStackTrace($OldStackTrace) {
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

	static public function FormatErrorMessageHtml($Data, $DisplayDetails) {
		$Message = '<p class="Type">'.$Data['Type'].'</p><p class="Message">'.htmlspecialchars($Data['Message']).'</p>';

		if($DisplayDetails) {
			$Message .= ErrorHandler::FormatErrorMessageDetailsHtml(null, null, $Data);
		}

		return Translator::EscapeTranslations($Message);
	}

	static private function FormatErrorMessageDetailsHtml($Indentation, $Section, $SectionData) {
		if($Section === null) {
			$Message = '';

			foreach($SectionData as $SubSection => $SubSectionData) {
				if($SubSection === 'Type') {
					continue;
				}
				else if($SubSection === 'Message') {
					continue;
				}
				else {
					$Message .= ErrorHandler::FormatErrorMessageDetailsHtml(0, $SubSection, $SubSectionData);
				}
			}

			if(isset($Message[0])) {
				return '<div class="Details">'.$Message.'</div>';
			}
			else {
				return '';
			}
		}
		else {
			if(is_int($Section)) {
				if(is_int($SectionData)) {
					return '<p class="IntegerValue">'.$SectionData.'</p>';
				}
				else if(isset($SectionData[0])) {
					return '<p class="StringValue">"'.htmlspecialchars($SectionData).'"</p>';
				}
				else {
					return '<p class="EmptyStringValue">empty string</p>';
				}
			}
			else if(is_array($SectionData)) {
				if(count($SectionData) === 0) {
					return '';
				}
				else {
					$Message = '';

					foreach($SectionData as $SubSection => $SubData) {
						$Message .= ErrorHandler::FormatErrorMessageDetailsHtml($Indentation + 1, $SubSection, $SubData);
					}

					return '<p class="Section">'.$Section.':</p><div class="Section">'.$Message.'</div>';
				}
			}
			else if(is_int($SectionData)) {
				return '<p class="Section">'.$Section.':</p><div class="Section"><p class="IntegerValue">'.$SectionData.'</p></div>';
			}
			if(isset($SectionData[0])) {
				return '<p class="Section">'.$Section.':</p><div class="Section"><p class="StringValue">"'.htmlspecialchars($SectionData).'"</p></div>';
			}
			else {
				return '<p class="Section">'.$Section.':</p><div class="Section"><p class="EmptyStringValue">empty string</p></div>';
			}
		}
	}
}
?>