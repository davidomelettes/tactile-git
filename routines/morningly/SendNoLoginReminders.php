<?php
class SendNoLoginReminders extends EGSCLIApplication {
	public function go() {
		// This is a new Email so we don't want to send it to old accounts
		$start_date = '2009-10-01';
		
		$db = DB::Instance();
		
		$no_logins = $db->GetArray(
			"SELECT
				(initcap(ta.firstname::text) || ' '::text) || initcap(ta.surname::text) AS full_name,
				initcap(ta.firstname::text) AS firstname,
				lower(ta.site_address::text) AS site_address,
				ta.email,
				ta.username
			FROM tactile_accounts ta
			LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
				WHERE u.last_login IS NULL
				AND ta.email::text LIKE '%@%'
				AND ta.email::text NOT LIKE '%senokian.com'::text
				AND ta.created <= (now() - '1 day'::interval)
				AND ta.created >= ".$db->qstr($start_date).";"
		);
		
		// Noone who hasn't logged in? Just finish up.
		if (count($no_logins) == 0) {
			return;
		}

		foreach ($no_logins as $no_login) {
			EGS::setUsername($no_login['username'] . '//' . $no_login['site_address']);
			
			// Check that we haven't ready sent this email
			if (
				$db->getOne(
					"SELECT COUNT(*) FROM mail_log
					WHERE username = {$db->qstr($no_login['username'] . '//' . $no_login['site_address'])}
					AND name = {$db->qstr('no_login_reminder')}") != 0)
			{
				continue;
			}
			
			$mail = new Omelette_Mail('no_login_reminder');

			$mail->getMail()->setSubject("Anything we can do to help with Tactile CRM?");
			$mail->getMail()->setFrom(TACTILE_EMAIL_ADDRESS, TACTILE_EMAIL_SENDER);

			if (defined('PRODUCTION') && PRODUCTION == true) {
				$mail->addBcc(TACTILE_DROPBOX);
				$mail->getMail()->addTo($no_login['email']);
			} else {
				$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
			}

			$mail->getView()->set('firstname', $no_login['firstname']);
			$mail->getView()->set('site_address', $no_login['site_address']);
			$mail->getView()->set('username', $no_login['username']);
			
			if ($db->getOne(
				"SELECT ap.cost_per_month > 0
				FROM tactile_accounts ta
				LEFT JOIN account_plans ap ON ap.id = ta.current_plan_id
				WHERE ta.site_address = {$db->qstr($no_login['site_address'])};") == 't'
			) {
				$mail->getView()->set('paid_plan', true);
			}

			$mail->send();
		}
	}
}
