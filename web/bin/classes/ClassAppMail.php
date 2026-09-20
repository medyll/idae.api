<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 06/05/2018
	 * Time: 15:45
	 */
	use PHPMailer\PHPMailer\PHPMailer;

	/**
	 * Outgoing mail: set the fields, then call sendMail().
	 *
	 * The plain-text part is derived from the HTML body automatically, so only the
	 * HTML needs setting.
	 */
	class AppMail {

		/**
		 * Starts a message with placeholder content, to be replaced through the setters.
		 */
		function __construct() {

			$this->body               = 'body';
			$this->body_strip         = strip_tags($this->body);
			$this->subject            = 'subject';
			$this->sender_email       = SMTPUSER;
			$this->destinataire_email = null;
			$this->destinataire_name = 'Mydde';
		}

		/**
		 * Sets the subject line.
		 *
		 * @param string $body
		 * @return void
		 */
		function set_subject($body) {
			$this->subject       = $body;
		}
		/**
		 * Sets the HTML body, and derives the plain-text part from it.
		 *
		 * @param string $body HTML
		 * @return void
		 */
		function set_body($body) {
			$this->body       = $body;
			$this->body_strip = strip_tags($body);
		}

		/**
		 * Sets the From address.
		 *
		 * @param string $body
		 * @return void
		 */
		function set_sender_email($body) {
			$this->sender_email = $body;
		}

		/**
		 * Sets the recipient address.
		 *
		 * @param string $destinataire_email
		 * @return void
		 */
		function set_destinataire_email($destinataire_email) {
			$this->destinataire_email = $destinataire_email;
		}
		/**
		 * Sets the recipient's display name.
		 *
		 * @param string $body
		 * @return void
		 */
		function set_destinataire_name($body) {
			$this->destinataire_name = $body;
		}
		/**
		 * Sends the message through PHPMailer over the configured SMTP server.
		 *
		 * Returns false without attempting to send when SMTPPASS is not configured,
		 * which is the case until the environment provides it.
		 *
		 * @return bool
		 */
		function sendMail() {
			if (!defined('SMTPPASS')) {
				error_log('AppMail: SMTPPASS is not configured, mail not sent');

				return false;
			}

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