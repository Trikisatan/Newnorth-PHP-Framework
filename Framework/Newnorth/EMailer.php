<?
namespace Framework\Newnorth;

class EMailer {
	/* Static methods */

	public static function Log($Data) {
		$EMail = new EMail();

		$EMail->SetFrom(Config('EMailer/Log/From'));

		$EMail->SetSubject($Data['Type'].': '.$Data['Url']);

		$EMail->SetText(print_r($Data, true));

		@$EMail->Send(Config('EMailer/Log/To'));
	}
}
?>