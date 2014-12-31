<?php

/**
 * Responsible for deleting a large number of objects via TaskRunner
 * @author de
 * @package Tasks
 */
class DelayedTaggedItemDeletion extends DelayedTask {
	
	protected $_mail = null;
	
	/**
	 * Set the Taggable model that we'll be deleting 
	 *
	 * @param string $taggable
	 */
	public function setTaggable($taggable) {
		$this->data['taggable'] = $taggable;
	}
	
	/**
	 * Set the human-readable string describing the type of object we're working with
	 *
	 * @param string $for
	 */
	public function setFor($for) {
		$this->data['for'] = $for;
	}
	
	/**
	 * Set the tag(s) that we'll be deleting by
	 *
	 * @param array $tag
	 */
	public function setTag($tag) {
		$this->data['tag'] = $tag;
	}
	
	/**
	 * Associate this task with a particular mail object
	 *
	 * @param Zend_Mail $mail
	 */
	public function setMail(Zend_Mail $mail) {
		$this->_mail = $mail;
	}
	
	/**
	 * @return Zend_Mail
	 */
	public function getMail() {
		if (is_null($this->_mail)) {
			$this->_mail =  new Zend_Mail();
		}
		return $this->_mail;
	}
	
	/**
	 * Load the taggable object, grab the relevant SQL, process the deletion, and send an email with the results
	 *
	 */
	public function execute() {
		$this->logger->info('Executing delayed tagged item deletion');
		
		$taggable = DataObject::Construct($this->data['taggable']);
		$tagged = new TaggedItem($taggable);
		
		$db = DB::Instance();
		$tag_string = implode(', ', array_map(array($db, 'qstr'), $this->data['tag']));
		
		$query = $taggable->getQueryForTagSearch($tag_string, count($this->data['tag']));
		$query_string = $query->countQuery('ti.id');
		$count = $db->getOne($query_string);
		$this->logger->info("$count {$this->data['for']}, matching tag(s): {$tag_string} to be deleted");
		
		// Perform the deletion
		$success = $tagged->deleteAllByTags($this->data['tag'], count($this->data['tag']));
		if ($success) {
			// Do tag cleanup
			foreach ($this->data['tag'] as $tag) {
				$t = new Omelette_Tag();
				if (FALSE !== ($t->loadBy('name', $tag))) {
					Omelette_Tag::cleanOrphans($t);
				}
			}
			
			$this->logger->info("Deletion was successful");
		} else {
			$this->logger->warn("Deletion encountered errors: " . $db->ErrorMsg());
		}
		
		// All done, so send email
		$user = DataObject::Construct('Omelette_User');
		$user->load(EGS::getUsername());
		$email_address = $user->getEmail();
		
		if($email_address == false) {
			$this->logger->warn("Import couldn't find an email to send to for " . $user->username);
		}
		
		$mail = new Omelette_Mail('tagged_item_deletion_status', $this->getMail());
		$params = array(
			'success'	=> $success, 
			'count'		=> $count,
			'for'		=> $this->data['for'],
			'tag_string'=> $tag_string
		);
		
		foreach($params as $find=>$replace) {
			if(is_array($replace)) {
				$replace = implode(',', $replace);
			}
			$mail->getView()->set($find, $replace);
		}
		
		$mail->addTo($email_address)
			->setSubject('Tactile CRM: Status Update')
			->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
		$mail->send();
		$this->logger->info('Email sent to ' . $email_address);
		
		parent::cleanup();
	}
	
}
