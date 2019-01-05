<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 06/05/2018
	 * Time: 15:45
	 */
	use PHPMailer\PHPMailer\PHPMailer;

	class AppMail {

		function __construct() {

			$this->body               = 'body';
			$this->body_strip         = strip_tags($this->body);
			$this->subject            = 'subject';
			$this->sender_email       = SMTPUSER;
			$this->destinataire_email = null;
			$this->destinataire_name = 'Mydde';
		}

		function set_subject($body) {
			$this->subject       = $body;
		}
		function set_body($body) {
			$this->body       = $body;
			$this->body_strip = strip_tags($body);
		}

		function set_sender_email($body) {
			$this->sender_email = $body;
		}

		function set_destinataire_email($destinataire_email) {
			$this->destinataire_email = $destinataire_email;
		}
		function set_destinataire_name($body) {
			$this->destinataire_name = $body;
		}
		function sendMail() {
			include_once(APPCLASSES . 'ClassSMTP.php');

			$mail = new PHPMailer();

			$mail->IsSMTP();
			$mail->IsHTML();
			$mail->WordWrap    = 50;
			$mail->SMTPDebug   = 0;
			$mail->SMTPAuth    = true;
			$mail->SMTPOptions = [ // PREPROD ?
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true
				]
			];
			$mail->CharSet     = 'UTF-8';
			$mail->Hostname    = SMTPDOMAIN;
			$mail->Helo        = SMTPDOMAIN;
			$mail->Host        = SMTPHOST;
			$mail->Username    = SMTPUSER;
			$mail->Password    = SMTPPASS;
			$mail->SetFrom(SMTPUSER, 'postmaster tac-tac');
			// $mail->AddReplyTo($_POST['emailFrom'] , $_POST['emailFromName']);
			$mail->Subject = $this->subject;
			$mail->AltBody = strip_tags($this->body);
			$this->destinataire_email;
			$mail->AddAddress($this->destinataire_email, $this->destinataire_name);

			$mail->MsgHTML($this->body);

			if (!$mail->Send()) {
				AppSocket::send_cmd('act_notify', ['msg' => 'Erreur envoi email'], session_id());

				return false;
			} else {
				return true;
			}
		}
	}