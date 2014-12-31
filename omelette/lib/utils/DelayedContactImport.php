<?php
/**
 * Responsible for carrying out a CSV import from TaskRunner
 * @author gj
 * @package Tasks
 */
class DelayedContactImport extends DelayedTask {

	/**
	 * Pass in the name of the uploaded file
	 * @param String $filename
	 * @return void
	 */
	public function setFile($filename) {
		$this->data['filename'] = $filename;
		
	}

	/**
	 * Pass in an assoc-array of read=>[ids] and write=>[ids]
	 * @param Array $sharing
	 * @return void
	 */
	public function setSharing($sharing) {
		$this->data['sharing'] = $sharing;
	}

	/**
	 * Pass in a comma-separated list of tags to apply to the imported entries
	 *
	 * @param String $tags
	 */
	public function setTags($tags) {
		$this->data['tags'] = $tags;
	}

	/**
	 * Set whether the import file is CSV or VCF
	 *
	 * @param string $type
	 */
	public function setFileType($type) {
		if (!in_array($type, array('csv', 'vcf', 'gdata', 'freshbooks', 'cloud', 'shoeboxed'))) {
			throw new Exception('File Type must be one of (csv, vcf, gdata, freshbooks), was: ' . $type);
		}
		$this->data['file_type'] = $type;
	}
	
	public function setCSVFieldMapping($csv_mappings) {
		$this->data['csv_mappings'] = $csv_mappings;
	}
	
	/**
	 * Runs the import and schedules a mailing to the user
	 * 
	 * The mailing has attached to it a text file of the rows that caused errors
	 * @return void
	 */
	public function execute() {
		$this->logger->info('Executing DelayedContactImport');
		
		$importer = new ContactImporter($this->data['filename']);
		$importer->setLogger($this->logger);
		
		switch ($this->data['file_type']) {
			case 'vcf':
				$this->logger->debug('Operating on a VCF file');
				$importer->setExtractor(new VCardExtractor());
				break;
			case 'gdata':
				$this->logger->debug('Operating on Gdata feed');
				$importer->setExtractor(new GDataExtractor());
				break;
			case 'freshbooks':
				$this->logger->debug("Operating on Freshbooks feed");
				$importer->setExtractor(new FreshbooksExtractor());
				break;
			case 'csv':
				$this->logger->debug('Operating on a CSV file');
				if(isset($this->data['csv_mappings'])) $importer->setExtractor(new OutlookCSVExtractor($this->data['csv_mappings']));
				else $importer->setExtractor(new OutlookCSVExtractor());
				break;
			case 'cloud':
				$this->logger->debug('Operating on a Cloud Contacts CSV file');
				$importer->setExtractor(new OutlookCSVExtractor());
				break;
			case 'shoeboxed':
				$this->logger->debug('Operating on a Shoeboxed XML file');
				$importer->setExtractor(new ShoeboxedExtractor());
				break;
			default:
				throw new Exception('Unknown file type received for import!');
				break;
		}
		
		$importer->prepare();
		
		$sharing = Omelette_OrganisationRoles::normalize($this->data['sharing']);
		$importer->setOrganisationRolesRead($sharing['read']);
		$importer->setOrganisationRolesWrite($sharing['write']);
		$importer->setTags($this->data['tags']);
		
		if (is_array($this->data['tags'])) {
			if (!empty($this->data['tags'])) {
				$suggested_tag = $this->data['tags'][0];
			} else {
				$suggested_tag = '';
			}
		} else {
			if (!empty($this->data['tags'])) {
				$tags = array_map('trim', explode(',', $this->data['tags']));
				$suggested_tag = $tags[0];
			} else {
				$suggested_tag = '';
			}
		}
		
		//do the import
		$importer->import($errors);
		$organisation_ids = $importer->get_organisation_ids();
		
		//all done, so send email
		$user = DataObject::Construct('User');
		$user->load(EGS::getUsername());
		$email_address = $user->getEmail();
		//we can only send if there is an email address
		if($email_address !== false) {
			$mail = new Omelette_Mail('contact_import_status');

			$params = array(
				'num_errors'	=> $importer->num_records_with_errors(), 
				'num_imported'	=> $importer->num_records_imported(),
				'import_link'	=> $importer->num_records_imported() > 0 && !empty($suggested_tag) ? ('http://' . Omelette::getUserSpace() . '.tactilecrm.com/tags/by_tag/?tag[0]='.urlencode($suggested_tag)) : ''
			);
			$this->logger->info('There were ' . $params['num_errors'] . ' errors');
			$this->logger->info('There were ' . $params['num_imported'] . ' records inserted');
			
			foreach($params as $find=>$replace) {
				if(is_array($replace)) {
					$replace = implode(',', $replace);
				}
				$mail->getView()->set($find, $replace);
			}
			
			$mail->getMail()
				->addTo($email_address)
				->setSubject('Tactile CRM: Status Update')
				->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
			
			//if there are errors, we send a text file showing the row numbers and error messages
			if(count($errors) > 0) {
				$explanations = '';
				foreach($errors as $row_num=>$error_msgs) {
					$explanations .= ($row_num + 2) . ': ' . implode('. ', $error_msgs) . "\n";
				}
				
				$this->logger->debug($explanations);
				$size = strlen($explanations);
				switch (1) {
					case $size > 1048576 * 0.5: {
						// A little on the beefy side, zip and attach to mail
						$zip = new ZipArchive();
						$tmp_zip_filename = tempnam('/tmp', 'importzip_');
						$zip->open($tmp_zip_filename, ZipArchive::OVERWRITE);
						$zip->addFromString('tactile-import-errors.txt', $explanations);
						$zip->close();
						
						$mail->getMail()->createAttachment(
							file_get_contents($tmp_zip_filename),
							'application/zip',
							Zend_Mime::DISPOSITION_ATTACHMENT,
							Zend_Mime::ENCODING_BASE64,
							'tactile-import-errors.zip'
						);
						break;
					}
					default: {
						// Nice and slim, attach as CSV file
						$mail->getMail()->createAttachment(
							$explanations,
							'text/plain',
							Zend_Mime::DISPOSITION_ATTACHMENT,
							Zend_Mime::ENCODING_BASE64,
							'tactile-import-errors.txt'
						);
					}
				}
			}
			$mail->send();
			$this->logger->info('Email sent to ' . $email_address);
		} else {
			$this->logger->warn("Import couldn't find an email to send to for " . $user->username);
		}
		$this->cleanup();
	}

	/**
	 * We want to remove the CSV file on top of the job file
	 * @return void
	 */
	protected function cleanup() {
		unlink($this->data['filename']);
		parent::cleanup();
	}
}
