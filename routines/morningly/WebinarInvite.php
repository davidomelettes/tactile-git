<?php

class WebinarInvite extends EGSCLIApplication {
	// Date the script went live so we don't email old users
	private $went_live = '2009-10-14';

	public function go() {
		return false; // Don't want to sent webinar invites anymore
		$db = DB::Instance();
		$recipients = $db->getArray(
			"SELECT ta.firstname, ta.surname, u.username, ta.email, ta.site_address
			FROM tactile_accounts ta
			LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
			WHERE u.last_login IS NOT NULL
			AND ta.enabled IS TRUE
			AND ta.created > {$db->qstr($this->went_live)}

			EXCEPT

			SELECT ta.firstname, ta.surname, u.username, ta.email, ta.site_address
			FROM tactile_accounts ta
			LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
			LEFT JOIN mail_log ml ON ml.username = u.username
			WHERE ml.name = 'webinar_invite';"
		);

		// Nobody to tell? Just return
		if (count($recipients) == 0) return;

		// Calculate next webinar dates
		if(date('N') <= 3) {
			$next_wednesday = strtotime('this wednesday');
		} else {
			$next_wednesday      = strtotime('next wednesday');
		}
		$a_week_on_wednesday = strtotime('+1 week', $next_wednesday);
		$another_week_on_wednesday    = strtotime('+1 week', $a_week_on_wednesday);
		
		$date_format = 'jS F (l)';
		

		foreach ($recipients as $recipient) {
			EGS::setUsername($recipient['username']);
			
			$mail = new Omelette_Mail('webinar_invite');

			$mail->getMail()->setSubject("Tactile CRM: Why not join us for a free online webinar/Q&A.");
			$mail->getMail()->setFrom(TACTILE_EMAIL_ADDRESS, TACTILE_EMAIL_SENDER);

			if (defined('PRODUCTION') && PRODUCTION == true) {
				$mail->addBcc(TACTILE_DROPBOX);
				$mail->getMail()->addTo($recipient['email']);
			} else {
				$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
			}

			$mail->getView()->set('firstname', ucwords(strtolower($recipient['firstname'])));
			$mail->getView()->set('surname', $recipient['surname']);
			$mail->getView()->set('site_address', $recipient['site_address']);
			$mail->getView()->set('email_address', $recipient['email']);
			
			$mail->getView()->set('webinar1', date($date_format, $next_wednesday));
			$mail->getView()->set('webinar2', date($date_format, $a_week_on_wednesday));
			$mail->getView()->set('webinar3', date($date_format, $another_week_on_wednesday));
			
			try {
				$mail->send();
			} catch (Zend_Mail_Transport_Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor cancellation subscription problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}
	}
}
