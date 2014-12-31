<?php
/* Author: Jake */ 

class LogoChecker extends EGSCLIApplication {
	
	private $db;
	private $file_info;
	protected $logger;
	
	public function go() {
		$this->db = DB::Instance();
		
		require_once 'Zend/Log.php';
		require_once 'Zend/Exception.php';
		
		// First up we get a list of contacts we haven't got a logo for
		// We limit to 50 so it doesn't run for ages
		$query = '
			SELECT p.id, c.contact, c.type, p.usercompanyid 
			FROM people p, person_contact_methods c, tactile_accounts a 
			WHERE 
				p.id=c.person_id AND 
				p.usercompanyid=a.organisation_id AND 
				a.enabled=true AND 
				c.main=true AND 
				(
					c.type=\'E\' OR 
					c.type=\'I\'
				) AND 
				p.last_logo_check IS NULL AND 
				p.thumbnail_id IS NULL AND 
				p.logo_id IS NULL
				AND p.usercompanyid=1074191
				ORDER BY p.id, c.type ASC
				LIMIT 50
		';

		$people = $this->db->getArray($query);

		// Now iterate over the list
		foreach ($people as $person) {
			// Check that there isn't already a logo as we may have already uploaded one in the loop
			if(!$this->hasLogo('person', $person['id'])) {
				// If it's an email check gravatar
				if($person['type'] == 'E') {
					$file = $this->checkGravatar($person);
				} else if($person['type'] == 'I') {
					// If it's twitter check their logo
					$file = $this->checkTwitter($person);
				}
				
				$this->updatePerson($person['id']);
			}
		}
	}
	
	function hasLogo($modelname, $id) {
		$query = '
			SELECT id FROM people
			WHERE
				id='.$id.' AND
				logo_id IS NULL
		';
		
		$person_id = $this->db->getOne($query);

		return empty($person_id);
	}
	
	function updatePerson($id) {
		$query = '
			UPDATE people
			SET last_logo_check=now()
			WHERE
				id='.$id.'
		';
		
		$person_id = $this->db->getOne($query);

		return empty($person_id);
	}
	
	function checkGravatar($person) {		
		$this->logger->info("Checking Gravatar for: ".$person['contact']. " [".$person['id']."]");
		// Form the hash
		$gravatar_hash = md5(strtolower(trim($person['contact'])));
		
		$file = 'http://www.gravatar.com/avatar/'.$gravatar_hash.'?s=50&r=g&d=404';
		
		return $this->processFile($file, $person);
	}
	
	function checkTwitter($person) {
		$this->logger->info("Checking Twitter for: ".$person['contact']." [".$person['id']."]");
		// Get the file from the twitter API request
		$file = 'http://api.twitter.com/1/users/profile_image/'.$person['contact'].'.json?size=bigger';
			
		return $this->processFile($file, $person);
	}
	
	
	function processFile($file, $person) {

		// Check file exists (we make the request so gravatar returns a 404 if none availble)
		$handle = fopen($file, 'rb');

		$type = 'Gravatar';
		
		// If file exists
		if($handle !== false) {
			// Because of redirects from twitter we loop to check it's not the normal file
			if(strpos($file, 'twitter.com') !== false) {
				// Get file info from HTTP headers
				$http_info = stream_get_meta_data($handle);
				
				$image_found = false;
				
				// Now iterate over to check that it doesn't have a default_profile
				while(($http_header = array_pop($http_info['wrapper_data'])) && !$image_found ) {
					// Check each header line to see if it's location
					if(strpos($http_header, 'Location:') !== false) {
						// Check it doesn't have default_profile
						if(strpos($http_header, 'default_profile_images') === false) {
							$image_found = true;
							$type = 'Twitter';
						}
					}
				}
				
				// If we are here, no image, or it was the default
				if(!$image_found) {
					fclose($handle);
					return false;
				}
			}

			// Check we can create a local file
			$tmp_file = '/tmp/'.md5($file);

			if(!file_exists($tmp_file)) {
				// Create the temp file
				$avatar = fopen($tmp_file, 'w+');
			
				// Download the file
				$avatar_file = fwrite($avatar, stream_get_contents($handle));
				
				if($avatar_file !== false) {
					fclose($avatar);

					// Thumbnail and upload the logo
					$file_saved = $this->save_logo('person', $tmp_file, $person['id'], $person['usercompanyid']);
					
					if($file_saved) {
						$this->logger->info("Updating with image from ". $type." for: ".$person['contact']." [".$person['id']."]");
						// Delete the file
						unlink($tmp_file);

						// Update Person
						$this->updatePerson($person['id']);

						// Return 
						return true;
					} else {
						// Issue saving file
						return false;
					}
				} else {
					fclose($avatar);
				}
			} else {
				$this->logger->crit("No image to upload from ".$type." for: ".$person['contact']." [".$person['id']."]");
			}
		}
		
		fclose($handle);
		return false;
	}
	
	public function save_logo($modelname, $file, $id, $usercompanyid) {
		EGS::setCompanyId($usercompanyid);

		//load the model to attach to
		$success = false;
		$model = DataObject::Construct($modelname);
		$success = $model->load($id);

		if (!$success) {
			$this->logger->crit('Saving logo: '. $file.' for '. $modelname.' with id '. $id);
			return;
		}

		// Todo Get file type
		$image_file = getimagesize($file);
		
		$upload_data['type'] = $image_file['mime'];
		$upload_data['tmp_name'] = $file;
		$upload_data['name'] = str_replace('/tmp/', '', $upload_data['tmp_name']);
		
		switch ($upload_data['type']) {
			case 'image/gif':
			case 'image/jpeg':
			case 'image/pjpeg':
			case 'image/png': {
				
				require_once 'Image/Thumbnailer.php';
				try {
					$upload_data2 = $upload_data;
					
					$tn = new Image_Thumbnailer($upload_data['tmp_name'], $upload_data['type']);
					$tn2 = new Image_Thumbnailer($upload_data['tmp_name'], $upload_data['type']);
					$tn->resize(IMAGE_THUMBNAILER_WIDTH, IMAGE_THUMBNAILER_HEIGHT, true);
					$upload_data['name'] = IMAGE_THUMBNAILER_WIDTH . 'x' . IMAGE_THUMBNAILER_HEIGHT . '_' . $upload_data['name'];
					$upload_data['bucket'] = S3_PUBLIC_BUCKET;

					$upload_data2['name'] = '400x50_' . $upload_data2['name'];
					$upload_data2['tmp_name'] = $upload_data2['tmp_name'] . '_400x50'; 
					$upload_data2['bucket'] = S3_PUBLIC_BUCKET;
					$tn2->resize(50, 50, true);
					$tn2->save($upload_data2['tmp_name']);
					$tn->save($upload_data['tmp_name']);
					
					$upload_data['size'] = filesize($upload_data['tmp_name']);
					$upload_data2['size'] = filesize($upload_data2['tmp_name']);
					
					unset($tn);
					unset($tn2);
				} catch(Image_Thumbnailer_Exception $e) {
					Flash::Instance()->addError($e->getMessage());
				}
				
				break;
			}
			default:
				$this->logger->crit('Saving logo: '. $file.' for '. $modelname.' with id '. $id . ' it was not a valid format');
				return;
		}
		
		//then make the attachment relationship
		try {
			$attachment = new S3Attachment($model);
			$errors = array();
			$file = $attachment->attachFile($upload_data, $errors);
			$file2 = $attachment->attachFile($upload_data2, $errors);
		} catch (Zend_Http_Client_Adapter_Exception $e) {
			$file = false;
			$errors[] = $e->getMessage();
		}
		
		//handle the result
		if($file!==false && (!isset($file2) || $file2 !== false)) {
			/* @var $file S3File */
			$file;
				if ($file2 !== false) {
					$model->thumbnail_id = $file->id;
				}
			
				$model->logo_id = $file2->id;
				$model->save();
				
				unlink($upload_data['tmp_name']);
				unlink($upload_data2['tmp_name']);
			
			Flash::Instance()->addMessage('File saved successfully');
			$attached_to = $file->getAttachedTo();
			return true;
		} else {
			return false;
		}
	}
}