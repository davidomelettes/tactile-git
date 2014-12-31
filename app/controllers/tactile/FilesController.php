<?php
/**
 *
 */
class FilesController extends Controller {

	
	/**
	 * The 'used' S3File instance
	 *
	 * @var S3File
	 */
	protected $s3file;
	
	/**
	 * The Controller's S3_Service instance
	 *
	 * @var S3_Service
	 */	
	protected $s3;
	
	/**
	 * Constructor
	 *
	 * @param String $module
	 * @param View $view
	 */	
	public function __construct($module,$view) {
		parent::__construct($module,$view);
		$this->uses('S3File');
		$this->s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
	}
	
	public function get() {
		if(!isset($this->_data['id']) || $this->s3file->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid file requested');
			sendTo();
		}
		
		$url = $this->s3->object->getRequestURL($this->s3file->object, $this->s3file->bucket, null, $this->s3file->bucket == S3_PUBLIC_BUCKET);
		header("Location: $url");
		return;
	}
	
	public function delete() {
		if(!isset($this->_data['id']) || $this->s3file->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid file requested');
			sendTo();
		}
		$attached_to = $this->s3file->getAttachedTo();
		if($this->s3file->canDelete() === false) {
			Flash::Instance()->addError('Only the file\'s owner is allowed to delete it');
			sendTo('/'.key($attached_to).'/view/'.current($attached_to));
			return;
		}
		$db = DB::Instance();
		$db->StartTrans();
		$success = $this->s3->object->delete($this->s3file->object, $this->s3file->bucket);
		if($success!==false && $this->s3file->delete()!==false) {
			$db->CompleteTrans();
			Flash::Instance()->addMessage('File deleted successfully');
			sendTo('/'.key($attached_to).'/view/'.current($attached_to));
			return;
		}
		$db->FailTrans();
		$db->CompleteTrans();
		Flash::Instance()->addError((string)$this->s3->object->getError()->Code);
		$attached_to = $this->s3file->getAttachedTo();
		sendTo('/'.key($attached_to).'/view/'.current($attached_to));
		return;
	}
	
	public function delete_logo() {
		if (isset($this->_data['organisation_id'])) {
			$model = new Organisation();
			if (false == $model->load($this->_data['organisation_id'])) {
				return;
			}
		} else if (isset($this->_data['person_id'])) {
			$model = new Person();
			if (false == $model->load($this->_data['person_id'])) {
				return;
			}
		}
		if (isset($model)) {
			$db = DB::Instance();
			$db->StartTrans();
			
			if (false !== $this->s3file->load($model->logo_id)) {
				$attached_to = $this->s3file->getAttachedTo();
				// Delete the logo
				$success = $this->s3->object->delete($this->s3file->object, $this->s3file->bucket);
				if ($success !== false && $this->s3file->delete() !== false) {
					// Delete the thumbnail
					if (false !== $this->s3file->load($model->thumbnail_id)) {
						$success = $this->s3->object->delete($this->s3file->object, $this->s3file->bucket);
						if ($success) {
							$this->s3file->delete();
						}
					}
					$db->CompleteTrans();
					Flash::Instance()->addMessage('Image deleted successfully');
					sendTo('/'.key($attached_to).'/view/'.current($attached_to));
					return;
				}
			}
		}
		Flash::Instance()->addError('Image not deleted successfully');
		return;
	}
	
	public function download_export() {
		$timestamp = empty($this->_data['id']) ? '' : $this->_data['id'];
		$zip_filename = 'tactile_export_' . EGS::getCompanyId() . '_' . $timestamp . '.zip';
		$path = DATA_ROOT . 'exports/' . $zip_filename;
		
		if (empty($timestamp) || !is_readable($path)) {
			$msg = 'Failed to locate export file.';
			if (!empty($timestamp) && strtotime('-7 days') > $timestamp) {
				$msg .= ' Exported data is deleted after 7 days.';
			}
			Flash::Instance()->addError($msg);
			sendTo();
			return;
		}
		
		$size = filesize($path);
		$chunksize = 1*(1024*1024);
		$bytes_sent = 0;
		if ($fh = fopen($path, 'r')) {
			
			//@ob_end_clean();
			set_time_limit((int)($size/1000)); // Assuming minimum of 1k/sec
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="tactile-export-'.$timestamp.'.zip"');
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$size);
			
			while (!feof($fh) && 
				(!connection_aborted()) && 
				($bytes_sent < $size)) {
				
				$buffer = fread($fh, $chunksize);
				print($buffer);
				flush();
				$bytes_sent += strlen($buffer);
			}
			fclose($fh);
		} else {
			Flash::Instance()->addError('Error occurred attempting to read from export file. Please try again later.');
			sendTo();
			return;
		}
	}
	
	public function index() {
		$files = new S3FileCollection();
		$sh = new SearchHandler($files, false);
		$sh->extractOrdering();
		$this->_handleSearchFields($sh);
		Controller::index($files, $sh);
		
		if ($this->view->is_json) {
			$files_json = array();
			foreach ($files as $file) {
				$files_json[] = json_decode($file->asJson());
			}
			$this->view->set('files_json', json_encode($files_json));
		}
	}
	
	private function _handleSearchFields(SearchHandler $sh) {
		$query = array();
		$exact_fields = array('f.organisation_id' => 'organisation_id', 'f.person_id' => 'person_id', 'f.opportunity_id' => 'opportunity_id',  'f.activity_id' => 'activity_id', 'f.email_id' => 'email_id');
		foreach($exact_fields as $queryfield => $field) {
			if(!empty($this->_data[$field])) {
				$query[$field] = $this->_data[$field];
				$value = $this->_data[$field];
				if (!is_numeric($queryfield)) {
					$field = $queryfield;
				}
				$constraint = new Constraint($field, '=', $value);
				$sh->addConstraint($constraint);
			}
		}
		$this->view->set('current_query', http_build_query($query));
	}
	
}
