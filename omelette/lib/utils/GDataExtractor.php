<?php

require_once 'Zend/Gdata/AuthSub.php';
require_once 'Gdata/Contacts.php';
require_once 'Gdata/Contacts/Query.php';
require_once 'Gdata/Contacts/Feed.php';

class GDataExtractor {
	
	/**
	 * The Feed wrapper
	 *
	 * @var Gdata_Contacts_Feed
	 */
	protected $feed;
	
	public function __construct($feed = null) {
		$this->feed = $feed;
	}
	
	/**
	 * Counts the number of lines in the file
	 *
	 * @param SPLFileObject $file
	 * @return int
	 */
	public function countRecords($file) {
		if($this->feed == null) {
			$this->feed = self::FeedFromFile($file);
		}
		return $this->feed->count();
	}
	
	public function iterate($file) {
		if($this->feed == null) {
			$this->feed = self::FeedFromFile($file);
		}
		if($this->feed->valid()) {
			$entry =  $this->feed->current();
			$this->feed->next();
			return $entry;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Take an Entry from the feed and pull out the bits
	 *
	 * @param Gdata_Contacts_Entry $entry
	 * @return array
	 */
	public function extract($entry) {
		$person_data = array();
				
		$fullname = $entry->title->text;
		$splitname = explode(' ',$fullname,2);
		if(count($splitname) < 2) {
			$people[] = null;
			return array(false, false);	//can't continue without enough name parts
		}
		$person_data['firstname'] = $splitname[0];
		$person_data['surname'] = $splitname[1];
		
		$emails = $entry->emails;
		foreach($emails as $email) {
			$email_data = array(
				'contact' => $email->address,
				'name' => ucfirst(str_replace($this->feed->lookupNamespace('gd').'#','',$email->rel))
			);
			if(!isset($person_data['emails'])) {
				$person_data['emails'] = array();
			}
			$person_data['emails'][] = $email_data;
		}
		
		$numbers = $entry->phoneNumbers;
		foreach($numbers as $number) {
			$phone_data = array(
				'contact' => $number->text,
				'name' => $number->label
			);
			if(!isset($person_data['phones'])) {
				$person_data['phones'] = array();
			}
			$person_data['phones'][] = $phone_data;
		}
		
		$addresses = $entry->postalAddresses;
		foreach ($addresses as $address) {
			$address_data = array(
				'name'	=> $address->label,
				'main'	=> ('false' !== $address->primary),
				'street1'	=> $address->text
			);
			if (!isset($person_data['addresses'])) {
				$person_data['addresses'] = array();
			}
			$person_data['addresses'][] = $address_data;
		}
		
		$org = $entry->organization;
		if(!is_null($org)) {
			$orgName = $org->orgName->text;
			$company_data = array('name'=>$orgName, 'created'=>date('Y-m-d H:i:s'));

			$jobTitle = $org->orgTitle->text;
			$person_data['jobtitle'] = $jobTitle;
		}
		else {
			$company_data = false;
		}
		return array($company_data, $person_data);
	}
	
	public static function FeedFromFile(SPLFileObject $file) {
		$filename = $file->getPathname();
		$feed = new Gdata_Contacts_Feed();
		$feed->transferFromXML(file_get_contents($filename));
		return $feed;
	}
	
}
?>