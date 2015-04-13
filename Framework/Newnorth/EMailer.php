<?
namespace Framework\Newnorth;

class EMailer {
	/* Static methods */

	static public function ErrorReport($Data) {
		$EMail = new EMail();

		if(isset($GLOBALS['Config']->EMailer['ErrorReport']['From'][0])) {
			$EMail->SetFrom($GLOBALS['Config']->EMailer['ErrorReport']['From']);
		}

		$EMail->SetSubject($Data['Type'].': '.$Data['Url']);

		$EMail->SetHtml(ErrorHandler::FormatErrorMessageHtml($Data, true));

		@$EMail->Send($GLOBALS['Config']->EMailer['ErrorReport']['To']);
	}
}
?>