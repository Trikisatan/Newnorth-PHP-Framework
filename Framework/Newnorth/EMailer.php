<?
namespace Framework\Newnorth;

class EMailer {
	/* Static methods */

	public static function ErrorReport($Data) {
		$EMail = new EMail();

		if(Config»IsNotEmpty('EMailer/ErrorReport/From')) {
			$EMail->SetFrom(Config('EMailer/ErrorReport/From'));
		}

		$EMail->SetSubject($Data['Type'].': '.$Data['Url']);

		$EMail->SetText(print_r($Data, true));

		@$EMail->Send(Config('EMailer/ErrorReport/To'));
	}
}
?>