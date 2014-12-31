<?php
require_once 'Zend/Mail.php';

class DelayedExport extends DelayedTask {
	
	public static $allowed_types = array('organisation', 'person', 'opportunity', 'activity');
	
	public static $allowed_query_keys = array('town', 'county', 'jobtitle', 'assigned_to', 'open');
	
	public static $output_fields = array(
		'organisation' => array(
			'id', 'name',
			'created', 'lastupdated',
			'accountnumber',
		 	'status', 'source', 'classification', 'industry', 'type',
			'website', 'phone', 'fax','email',
			'street1', 'street2', 'street3', 'town', 'county','postcode', 'country_code',
			'description', 'tags'
		),
		'person' => array(
			'id',
			'title', 'firstname', 'surname', 'suffix',
			'created', 'lastupdated',
			'jobtitle','organisation', 'organisation_id',
			'dob', 'can_call', 'can_email', 'language_code',
			'phone', 'mobile', 'email',
			'street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country_code',
			'description', 'tags'
		),
		'opportunity' => array(
			'id',
			'name',
			'created', 'lastupdated',
			'status',
			'organisation_id',
			'organisation',
			'person_id',
			'person',
			'description',
			'cost',
			'probability',
			'enddate',
			'type',
			'source',
			'owner',
			'assigned_to',
			'tags'
		),
		'activity' => array(
			'id',
			'name',
			'created', 'lastupdated',
			'organisation_id',
			'organisation',
			'person_id',
			'person',
			'opportunity_id',
			'opportunity',
			'description',
			'class',
			'location',
			'later',
			'date',
			'time',
			'end_date',
			'end_time',
			'completed',
			'type',
			'assigned_to',
			'owner',
			'tags'
		)
	);
	/**
	 * @var Omelette_Mail
	 */
	protected $mail;
	
	public function setType($type) {
		if(!in_array($type, self::$allowed_types)) {
			throw new Exception("Invalid type specified: ".$type);
		}
		$this->data['export_type'] = $type;
	}
	
	public function setQuery($key, $value) {
		if(!in_array($key, self::$allowed_query_keys)) {
			throw new Exception("Invalid key specified: ".$key);
		}
		$this->data['key'] = $key;
		$this->data['value'] = $value;
	}
	
	public function setTags($tags) {
		$this->data['tags'] = $tags;
	}
	
	/**
	 * Decides what to do with the exported data
	 */
	private function _attachExportData($mail, $filename) {
		$size = filesize($filename);
		$export_filename = 'tactile_export_' . $this->data['export_type'] . '_' . date('YmdHis');
		switch (1) {
			case $size > 1048576 * 1: {
				// Possibly too big to mail. Compress, save to disk, and send link
				$now = time();
				
				$zip = new ZipArchive();
				$zip_filename = 'tactile_export_' . EGS::getCompanyId() . '_' . $now . '.zip';
				if (TRUE !== ($error = $zip->open(DATA_ROOT . 'exports/' . $zip_filename, ZipArchive::OVERWRITE))) {
					throw new Exception('Failed to open Zip archive for writing during export. Error code was: ' . $error);
				}
				$zip->addFile($filename, $export_filename.'.csv');
				$zip->close();
				
				$mail->getView()->set('export_url', 'http://' . Omelette::getUserspace() . '.tactilecrm.com/files/download_export/' . $now);
				break;
			}
			case $size > 1048576 * 0.5: {
				// A little on the beefy side, zip and attach to mail
				$zip = new ZipArchive();
				$tmp_zip_filename = tempnam('/tmp', 'exportzip_');
				$zip->open($tmp_zip_filename, ZipArchive::OVERWRITE);
				$zip->addFromString($export_filename.'.csv', file_get_contents($filename));
				$zip->close();
				
				$mail->createAttachment(
					file_get_contents($tmp_zip_filename),
					'application/zip',
					Zend_Mime::DISPOSITION_ATTACHMENT,
					Zend_Mime::ENCODING_BASE64,
					$export_filename.'.zip'
				);
				break;
			}
			default: {
				// Nice and slim, attach as CSV file
				$mail->createAttachment(
					file_get_contents($filename),
					'text/csv',
					Zend_Mime::DISPOSITION_ATTACHMENT,
					Zend_Mime::ENCODING_BASE64,
					$export_filename.'.csv'
				);
			}
		}
	}
	
	public function execute() {
		$this->logger->debug("Exporting " . $this->data['export_type']);
		$exporter = $this->getExporter();
		$exporter->setUserCompanyId(EGS::getCompanyId());
		$exporter->setUsername(EGS::getUsername());
		
		$tempfile_name = tempnam("/tmp", "export_" . Omelette::getUserSpace() . '_' . $this->data['export_type']. '_');
		$stream = fopen($tempfile_name, "w+");
		
		$formatter = $this->getFormatter();
		$formatter->setStream($stream);
		$formatter->setOrder($this->getOrder());
		$formatter->addHeadings($stream);
		$exporter->setFormatter($formatter);
		
		if (isset($this->data['tags']) && count($this->data['tags']) > 0) {
			$this->logger->debug("Exporting by tags: ".print_r($this->data['tags'], true));
			$exporter->getByTag($this->data['tags']);
		}
		elseif (!empty($this->data['key']) && !empty($this->data['value'])) {
			$this->logger->debug("Exporting query: ".$this->data['key']." = ".$this->data['value']);
			$exporter->getBy($this->data['key'], $this->data['value']);
		}
		else {
			$this->logger->debug("Exporting everything");
			$exporter->getAll();	
		}
		fclose($stream);
		
		$mail = $this->getMail();
		$to = $this->getRecipientAddress();
		$this->logger->debug("Emailing results to " . $to);
		$mail->addTo($to)
			->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME)
			->setSubject("Tactile CRM: Your Exported Data");

		try {
			$this->_attachExportData($mail, $tempfile_name);
			$mail->send();
		} catch (Exception $e) {
			// Warn user there was a problem
			$err_mail = new Omelette_Mail('error_delayed_task');
			$err_mail->addTo($to)
				->addBcc('support@tactilecrm.com')
				->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME)
				->setSubject("An error occurred while exporting your Tactile CRM data");
			$err_mail->getView()->set('error_message', $e->getMessage());
		}
		$this->cleanup();
	}
	
	public function getOrder() {
		switch($this->data['export_type']) {
			case 'organisation':
				$cf_where = "organisations";
				$key = $this->data['export_type'];
				break;
			case 'person':
				$cf_where = "people";
				$key = $this->data['export_type'];
				break;
			case 'opportunity':
				$cf_where = "opportunities";
				$key = $this->data['export_type'];
				break;
			case 'activity':
				$cf_where = "activities";
				$key = $this->data['export_type'];
				break;
			default:
				$cf_where = "organisations";
				$key = 'organisation';
		}
		
		$order = self::$output_fields[$key];
		
		// Add custom fields
		$db = DB::Instance();
		$fields = $db->getCol("SELECT name FROM custom_fields WHERE $cf_where AND usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name");
		if (!empty($fields)) {
			$order = array_merge($order, $fields);
		}
		
		return $order;
	}
	
	/**
	 * @return CSVExportFormatter
	 */
	public function getFormatter() {
		return new CSVExportFormatter();
	}
	
	/**
	 * @return CompanyExporter
	 */
	public function getExporter() {
		switch($this->data['export_type']) {
			case 'organisation':
				$exporter = new OrganisationExporter();
				break;
			case 'person':
				$exporter = new PersonExporter();
				break;
			case 'opportunity':
				$exporter = new OpportunityExporter();
				break;
			case 'activity':
				$exporter = new ActivityExporter();
				break;
			default:
				throw new Exception("Unknown export-type: ".$this->data['export_type']);
		}
		return $exporter;
	}
	
	/**
	 * @param Omelette_Mail $mail
	 */
	public function setMail($mail) {
		$this->mail = $mail;
	}
	
	/**
	 * @return Omelette_Mail
	 */
	public function getMail() {
		if(is_null($this->mail)) {
			$this->mail =  new Omelette_Mail('csv_export');
		}
		return $this->mail;
	}
	
	/**
	 * @return String
	 */
	public function getRecipientAddress() {
		$user = new Omelette_User();
		$user->load(EGS::getUsername());
		return $user->getEmail();
	}
}
