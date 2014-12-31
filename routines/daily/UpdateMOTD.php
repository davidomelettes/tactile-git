<?php

class UpdateMOTD extends EGSCLIApplication {

        public function go() {
		// Get a list of post via RSS
		require_once 'Zend/Feed.php';
		require_once 'Zend/Feed/Atom.php';
		require_once 'Zend/Log.php';

		$db = DB::Instance();

		$motds = '';

		try {
			$feed = Zend_Feed::import('http://blog.tactilecrm.com/tag/motd/feed/');

			// Iterate over posts
			foreach($feed AS $entry) {
				// Check post doesn't exist in MOTD
				$q = 'SELECT id FROM motds WHERE content LIKE '.$db->quote("%".$entry->link."%");

				$r = $db->GetOne($q);

				// link doesn't exist in motds
				if($r === false) {
				//echo $entry->title;
				//echo $entry->link;
				// Get end date of last MOTD
				$q = "SELECT max(message_end) AS end_date FROM motds WHERE active=true";

				$end_date = $db->GetOne($q);

				// Set start date to today or last MOTD + 1 day (which ever is highest)
				if(strtotime($end_date) + 86400 > time()) {
					$start = date('Y-m-d H:i', strtotime($end_date) + 86400);
				} else {
					$start = date('Y-m-d H:i');
				}

				// Set end date to start date + 2 days
				$q = "INSERT INTO motds (
					message_start,
					message_end,
					active,
					content)
					VALUES (
					date '".$start."',
					date '".$start."' + interval '2 days',
					true,
					".$db->qstr($entry->title.' (<a href="'.$entry->link.'">read more</a>).').")";
				// Put MOTD in database
				$db->Execute($q);

				$motds .= $entry->title.' ,';
				}

			}
				if($motds != '') {
					// Email to say it has been added
					$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'New MOTD Added'));
					$logger->warn(print_r($motds,true));
				}
		} catch(Exception $e) {
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Syncing MOTD Problem'));
		}

	}
}
?>
