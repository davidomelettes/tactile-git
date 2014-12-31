<?php

/**
 * Responsible for determining what reminders need to be sent, and then sending them
 * 
 * @author gj
 */
class ActivityReminderSender extends EGSCLIApplication {

	/**
	 * The time the email wants to be sent
	 *
	 * @var String
	 */
	protected $send_time;
	
	/**
	 * The date used in calculating 'today'
	 *
	 * @var String
	 */
	protected $today = 'today';

	/**
	 * The time 'now'- this is settable for testing or catchups
	 *
	 * @var String
	 */
	protected $time_now;

	/**
	 * The instance of Zend_Log used by the application
	 *
	 * @var Zend_Log
	 */
	protected $logger;

	/**
	 * The people who should be sent an email
	 *
	 * @var Array
	 */
	protected $recipients = array();

	/**
	 * The number of emails sent - this is incremented each time send() is called
	 * - used for checking that mails_sent=num_recipients
	 * @var Integer
	 */
	protected $emails_sent = 0;

	/**
	 * If config has been sent for send_time, then we want to use it
	 *
	 */
	protected function checkConfig() {
		if(isset($this->config['send_time'])) {
			$this->setSendTime($this->config['send_time']);
			if(isset($this->config['production']) && $this->config['production'] === false) {
				$this->setTimeNow($this->send_time);
			}
		}
	}

	/**
	 * Set the time that emails should be sent - this is used in conjunction with user timezones
	 *
	 * @param String $time
	 * @return void
	 */
	public function setSendTime($time) {
		$this->send_time = $time;
		$this->logger->debug('SendTime set to ' . $time);
	}
	
	/**
	 * Set a specific date to use instead of 'today' - used for testing
	 * 
	 * @param String $today
	 * @return void
	 */
	public function setToday($today) {
		if (FALSE === strtotime($today)) {
			throw new Exception('Error parsing Today string: ' . $today);
		}
		$this->logger->debug('Manually setting "today" to be ' . $today);
		$this->today = $today;
	}

	/**
	 * Allow the setting of the time to use for 'now' when comparing to user-timezone times (mainly for testing)
	 * 
	 * @param String $time_now
	 * @return void
	 */
	public function setTimeNow($time_now) {
		$this->time_now = $time_now;
		$this->logger->debug('Manually setting "now" to be ' . $time_now);
	}

	/**
	 * Send reminder emails to all people with activities today who haven't already been sent one
	 *
	 * @return void
	 */
	public function go() {
		if(!isset($this->send_time)) {
			$this->logger->crit('No Send-Time set, exiting');
			return;
		}
		if(!isset($this->time_now)) {
			$this->time_now = date('H:i');
		}
		$this->recipients = $this->findRecipients();
		$this->logger->info('There are ' . count($this->recipients) . ' people to send reminders to');
		
		foreach($this->recipients as $recipient) {
			$should_send = EmailPreference::getSendStatus('activity_reminder', $recipient['username']);
		
			$mail = new Omelette_Mail('activity_reminder');
			$mail->getMail()->addTo($recipient['email']);
			$mail->getMail()->setFrom(TACTILE_EMAIL_FROM_NO_REPLY, TACTILE_EMAIL_NAME);
			$mail->getMail()->setSubject("Tactile CRM: Activity Reminder");
			
			$user_space = array_pop(explode('//', $recipient['username']));
			$mail->getView()->set('user_space', $user_space);
			
			$mail->getView()->set('recipient', $recipient);
			
			$mail->send($should_send);
			if($should_send) {
				$this->logger->info('Mail sent to ' . $recipient['email']);
			}
			else {
				$this->logger->info('Skipping '.$recipient['email'].' because of their mail-preferences');
			}
			$this->emails_sent++;
		}
		
		if($this->emails_sent != count($this->recipients)) {
			$this->logger->err(count($this->recipients) . ' recipients, but ' . $this->emails_sent . ' emails sent');
		}
	}

	/**
	 * Returns an array for each user who has activities due today, who hasn't yet been sent an email. 
	 * The array contains the person details, as well as an array each for overdue and today's activities
	 *
	 * @return Array
	 */
	protected function findRecipients() {
		$db = DB::Instance();
		$people = array();
		$query = 'SELECT DISTINCT u.username, p.firstname, p.surname, e.contact AS email,
				a.*
			FROM users u
			JOIN people p ON 
				(u.person_id=p.id)
			JOIN person_contact_methods e ON 
				(p.id=e.person_id AND e.main AND e.type=\'E\')
			JOIN (
				SELECT
				a.assigned_to, a.later, a.completed, a.created, a.date, a.end_date, a.time, a.id AS activity_id, a.name AS activity_name,a.date AS activity_date, a.time AS activity_time, a.class, a.end_date as activity_end_date, a.end_time as activity_end_time, o.name AS activity_organisation, o.id AS activity_organisation_id, p.firstname || \' \' || p.surname AS activity_person, p.id AS activity_person_id , pp.contact AS activity_person_phone, pm.contact AS activity_person_mobile, pe.contact AS activity_person_email,op.contact AS activity_organisation_phone, om.contact AS activity_organisation_mobile, oe.contact AS activity_organisation_email
				FROM
				tactile_activities a LEFT OUTER JOIN organisations o ON a.organisation_id=o.id
				LEFT OUTER JOIN people p ON a.person_id=p.id
				LEFT OUTER JOIN person_contact_methods pp ON (pp.person_id=p.id AND pp.type = \'T\' AND pp.main)
				LEFT OUTER JOIN person_contact_methods pm ON (pm.person_id=p.id AND pm.type = \'M\' AND pm.main)
				LEFT OUTER JOIN person_contact_methods pe ON (pe.person_id=p.id AND pe.type = \'E\' AND pe.main)
				LEFT OUTER JOIN organisation_contact_methods op ON (op.organisation_id=o.id AND op.type = \'T\' AND op.main)
				LEFT OUTER JOIN organisation_contact_methods om ON (om.organisation_id=o.id AND om.type = \'M\' AND om.main)
				LEFT OUTER JOIN organisation_contact_methods oe ON (oe.organisation_id=o.id AND oe.type = \'E\' AND oe.main)
			) a ON 
				(a.assigned_to=u.username AND NOT later AND completed IS NULL AND a.created<' . $db->qstr($this->today) . '::date 
					AND (
						(date<=' . $db->qstr($this->today) . '::date AND a.class=\'todo\')
						OR
						(date <= ' . $db->qstr($this->today) . '::date AND end_date >= ' . $db->qstr($this->today) . '::date AND a.class=\'event\')
					)
				)
			LEFT JOIN mail_log ml ON 
				(e.contact = ml.recipient AND ml.name=\'activity_reminder\' AND (ml.time_sent::date AT TIME ZONE u.timezone) >= (' . $db->qstr($this->today) . '::date AT TIME ZONE u.timezone))
			WHERE ml.id IS NULL 
				AND ' . $db->qstr($this->today . ' ' . $this->time_now) . '>=(' . $db->qstr($this->today . ' ' . $this->send_time) . ' AT TIME ZONE u.timezone)
				AND u.enabled
			ORDER BY a.date, a.time
		';
		$rows = $db->GetArray($query);
		$this->logger->debug($query);
		if($rows === false) {
			$this->logger->err('Query Error: ' . $db->ErrorMsg());
			return $people;
		}
		$formatters = array(
				'activity_date'=>new PrettyTimestampFormatter(
						false));
		//filter the results so we have one row per person, and then sub-arrays for activities
		foreach($rows as $row) {
			$activities_today = array();
			$activities_overdue = array();
			if(strtotime($row['activity_date']) < strtotime($this->today)) {
				$act_array = 'activities_overdue';
			} else {
				$act_array = 'activities_today';
			}
			foreach($row as $key=>$val) {
				if(substr($key, 0, 8) == 'activity') {
					if(isset($formatters[$key])) {
						$val = $formatters[$key]->format($val);
					}
					${$act_array}[str_replace('activity_', '', $key)] = $val;
					unset($row[$key]);
				}
			}
			if(!isset($people[$row['username']])) {
				$people[$row['username']] = $row;
				$people[$row['username']]['activities_today'] = array();
				$people[$row['username']]['activities_overdue'] = array();
			}
			$people[$row['username']][$act_array][] = $$act_array;
		}
		
		//we're not going to send emails unless people have an activity due, so filter (except on mondays):
		if(date('w') != 1) {
			$people = array_filter($people, array(
					'ActivityReminderSender', 'TodayFilter'));
		}
		return $people;
	}

	/**
	 * A callback for array_filter, returns true when the value contains an array 'activities_today' with one or nore entries
	 *
	 * @param Array $row
	 * @return Boolean
	 */
	private static function TodayFilter($row) {
		return count($row['activities_today']) > 0;
	}

}

?>
