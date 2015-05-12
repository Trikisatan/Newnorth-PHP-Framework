<?
namespace Framework\Newnorth;

class EMailer {
	/* Static methods */

	public static function ErrorReport($Data) {
		$EMail = new EMail();

		if(isset($GLOBALS['Config']->EMailer['ErrorReport']['From'][0])) {
			$EMail->SetFrom($GLOBALS['Config']->EMailer['ErrorReport']['From']);
		}

		$EMail->SetSubject($Data['Type'].': '.$Data['Url']);

		$EMail->SetText(print_r($Data, true));

		@$EMail->Send($GLOBALS['Config']->EMailer['ErrorReport']['To']);
	}
}
?>