<?php
class WhatDidYouThink extends EGSCLIApplication {
	public function go() {
		// Don't send it to signups before this date as we changed how we do the emails.
		$start_date = '2009-10-01';
		
		$db = DB::Instance();
	
		// Get those who have created an account more than two weeks ago
		// and have logged in, but not those who have been sent the reminder
		// email and/or not those who have received the welcome email already.
		$normal = $db->getArray(
			"SELECT ta.firstname, u.username, ta.email, ta.site_address
			FROM tactile_accounts ta
			LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
			WHERE ta.created <= (NOW() - '6 days'::interval)
			AND u.last_login IS NOT NULL
			AND ta.enabled = 't'
			AND ta.created >=".$db->qstr($start_date)."

			EXCEPT

			SELECT ta.firstname, u.username, ta.email, ta.site_address
			FROM tactile_accounts ta
			LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
			LEFT JOIN mail_log ml ON ml.username = u.username
			WHERE ml.name = 'your_feedback';
		");
		
		$recipients = $normal;
		
		if (count($recipients) == 0) return;
		
		foreach ($recipients as $recipient) {
			if (!empty($recipient['email'])) {
				EGS::setUsername($recipient['username']);
				
				$mail = new Omelette_Mail('your_feedback');
	
				$mail->getMail()->setSubject("What did you think of Tactile CRM?");
				$mail->getMail()->setFrom(TACTILE_EMAIL_ADDRESS, TACTILE_EMAIL_SENDER);
	
				if (defined('PRODUCTION') && PRODUCTION == true) {
					$mail->addBcc(TACTILE_DROPBOX);
					$mail->getMail()->addTo($recipient['email']);
				} else {
					$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
				}
	
				$mail->getView()->set('firstname', ucfirst(strtolower($recipient['firstname'])));
				$mail->getView()->set('username', str_replace('//'.$recipient['site_address'], '', $recipient['username']));
				$mail->getView()->set('site_address', $recipient['site_address']);
				try {
					$mail->send();
				} catch (Zend_Mail_Transport_Exception $e) {
					$this->logger->warn('Failed to send Your Feedback email to "'.$recipient['email'] . '": ' . $e->getMessage());
				}
			}
		}
	}
}
