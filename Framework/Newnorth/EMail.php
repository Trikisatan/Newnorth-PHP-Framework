<?
namespace Framework\Newnorth;

class EMail {
	/* Instance variables */

	private $SMTP = [
		'Host' => null,
		'Port' => null,
		'Username' => null,
		'Password' => null,
	];

	private $From = [
		'EMail' => null,
		'Name' => null,
	];

	private $ReplyTo = [];

	private $Subject = null;

	private $Text = null;

	private $Html = null;

	private $Attachments = [];

	/* Instance methods */

	public function GetSMTP() {
		return [
			'Host' => $this->GetSMTPHost(),
			'Port' => $this->GetSMTPPort(),
			'Username' => $this->GetSMTPUsername(),
			'Password' => $this->GetSMTPPassword(),
		];
	}

	public function GetSMTPHost() {
		if($this->SMTP['Host'] !== null) {
			return $this->SMTP['Host'];
		}
		else {
			return Config('SMTP/Host', null);
		}
	}

	public function GetSMTPPort() {
		if($this->SMTP['Port'] !== null) {
			return $this->SMTP['Port'];
		}
		else {
			return Config('SMTP/Port', null);
		}
	}

	public function GetSMTPUsername() {
		if($this->SMTP['Username'] !== null) {
			return $this->SMTP['Username'];
		}
		else {
			return Config('SMTP/Username', null);
		}
	}

	public function GetSMTPPassword() {
		if($this->SMTP['Password'] !== null) {
			return $this->SMTP['Password'];
		}
		else {
			return Config('SMTP/Password', null);
		}
	}

	public function SetSMTP($Host = null, $Port = null, $Username = null, $Password = null) {
		$this->SMTP['Host'] = $Host;

		$this->SMTP['Port'] = $Port;

		$this->SMTP['Username'] = $Username;

		$this->SMTP['Password'] = $Password;
	}

	public function SetSMTPHost($Host = null) {
		$this->SMTP['Host'] = $Host;
	}

	public function SetSMTPPort($Port = null) {
		$this->SMTP['Port'] = $Port;
	}

	public function SetSMTPUsername($Username = null) {
		$this->SMTP['Username'] = $Username;
	}

	public function SetSMTPPassword($Password = null) {
		$this->SMTP['Password'] = $Password;
	}

	public function SetFrom($EMail, $Name) {
		$this->From['EMail'] = $EMail;

		$this->From['Name'] = $Name;
	}

	public function AddReplyTo($EMail, $Name) {
		$this->ReplyTo[] = [
			'EMail' => $EMail,
			'Name' => $Name,
		];
	}

	public function SetTemplate($Template, $Variables = []) {
		$this->SetSubjectTemplate($Template, $Variables);
		$this->SetTextTemplate($Template, $Variables);
		$this->SetHtmlTemplate($Template, $Variables);
	}

	public function SetSubject($Subject) {
		$this->Subject = $Subject;
	}

	public function SetSubjectTemplate($Template, $Variables = []) {
		if(isset($GLOBALS['Parameters']['Locale'][0])) {
			$FilePath = Config('Files/EMailTemplates').$Template.'.subject.'.$GLOBALS['Parameters']['Locale'].'.phtml';
		}
		else {
			$FilePath = Config('Files/EMailTemplates').$Template.'.subject.phtml';
		}

		$this->Subject = file_get_contents($FilePath);

		foreach($Variables as $Key => $Value) {
			$this->Subject = str_replace('%'.$Key.'%', $Value, $this->Subject);
		}
	}

	public function SetText($Text) {
		$this->Text = $Text;
	}

	public function SetTextTemplate($Template, $Variables = []) {
		if(isset($GLOBALS['Parameters']['Locale'][0])) {
			$FilePath = Config('Files/EMailTemplates').$Template.'.text.'.$GLOBALS['Parameters']['Locale'].'.phtml';
		}
		else {
			$FilePath = Config('Files/EMailTemplates').$Template.'.text.phtml';
		}

		$this->Text = file_get_contents($FilePath);

		foreach($Variables as $Key => $Value) {
			$this->Text = str_replace('%'.$Key.'%', $Value, $this->Text);
		}
	}

	public function SetHtml($Html) {
		$this->Html = $Html;
	}

	public function SetHtmlTemplate($Template, $Variables = []) {
		if(isset($GLOBALS['Parameters']['Locale'][0])) {
			$FilePath = Config('Files/EMailTemplates').$Template.'.html.'.$GLOBALS['Parameters']['Locale'].'.phtml';
		}
		else {
			$FilePath = Config('Files/EMailTemplates').$Template.'.html.phtml';
		}

		$this->Html = file_get_contents($FilePath);

		foreach($Variables as $Key => $Value) {
			$this->Html = str_replace('%'.$Key.'%', $Value, $this->Html);
		}
	}

	public function Send($To) {
		$mail = new \PHPMailer();

		$SMTP = $this->GetSMTP();

		if($SMTP['Host'] !== null && $SMTP['Port'] !== null) {
			$mail->isSMTP();

			$mail->Host = $SMTP['Host'];

			$mail->Port = $SMTP['Port'];

			if($SMTP['Username'] !== null && $SMTP['Password'] !== NULL) {
				$mail->SMTPAuth = true;

				$mail->Username = $SMTP['Username'];

				$mail->Password = $SMTP['Password'];
			}
		}
		else {
			$mail->isMail();
		}

		$mail->CharSet = 'UTF-8';

		$mail->From = $this->From['EMail'];

		$mail->FromName = $this->From['Name'];

		foreach($this->ReplyTo as $ReplyTo) {
			$mail->addReplyTo($ReplyTo['EMail'], $ReplyTo['Name']);
		}

		$mail->Subject = $this->Subject;

		$mail->msgHTML($this->Html);

		$mail->AltBody = $this->Text;

		//$mail->AddAttachment("images/phpmailer.gif");             // attachment

		if(is_array($To)) {
			for($I = 0; $I < count($To); ++$I) {
				$mail->AddAddress($To[$I]);
			}
		}
		else {
			$mail->AddAddress($To);
		}

		return $mail->Send();
	}
}
?>