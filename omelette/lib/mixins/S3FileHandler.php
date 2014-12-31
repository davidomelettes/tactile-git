<?php
/**
 * Responsible for the controller-actions that relate to file attachments
 * This class is used in the 'mixin' style, where $this refers to the Controller instance
 * 
 * @author gj
 */
class S3FileHandler {

	/**
	 * Display a form for a file upload
	 * $args[0] is the modelname
	 * 
	 * @param Array $args
	 */
	public function new_file($args) {
		$user = CurrentlyLoggedInUser::Instance();
		$plan = $user->getAccountPlan();
		
		$allowance = $plan->file_space;
		$usage = S3File::getUsage(EGS::getCompanyId());
		
		if($allowance > 0 && $usage>=$allowance) {
			Flash::Instance()->addError('You don\'t have enough space to upload any more files, either delete some or upgrade your account');
			return;
		}
		
		$formatter = new ReplacementFormatter(0,'Unlimited', new FilesizeFormatter());
		$this->view->set('allowance', $formatter->format($allowance));
		
		
		
		$modelname = $args[0];
		$url_part = $args[1];
		$model = DataObject::Construct($modelname);
		$model->load($this->_data['id']);
		$this->view->set('model', $model);
		$this->view->set('url_part',$url_part);
		
		$this->view->set('form_max_filesize', str_replace('M','000000',ini_get('upload_max_filesize')));
		
		$this->view->set('usage', $formatter->format($usage));
	}
	
	/**
	 * Creates a file-attachment to the appropriate model
	 * $args[0] = modelname, 1 = module-name, 2 = controller name
	 * 
	 * @param Array $args
	 */
	public function save_file($args) {
		$modelname = $args[0];
		$module = $args[1];
		$controller = $args[2];
		
		$sendto = !empty($this->_data['is_account_logo']) ? 'appearance' : "$controller/view/{$this->_data['id']}";
		
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		if ($plan->is_per_user()) {
			$allowance = $plan->file_space * $account->per_user_limit;
		} else {
			$allowance = $plan->file_space;
		}
		$usage = S3File::getUsage(EGS::getCompanyId());
		
		if($allowance > 0 && $usage>=$allowance) {
			Flash::Instance()->addError('You don\'t have enough space to upload any more files, either delete some or purchase more Users');
			sendTo($sendto);
			return;
		}
		
		if(!isset($_FILES['Filedata']) && !empty($_POST)) {
			Flash::Instance()->addError('You need to choose a file to upload');
			sendTo($sendto);
			return;
		}
		if(empty($_POST)) {
			Flash::Instance()->addError('The file you tried to upload is too large. The maximum size is '.ini_get('upload_max_filesize'));
			sendTo($sendto);
			return;
		}
		if($_FILES['Filedata']['error'] > 0) {
			switch($_FILES['Filedata']['error']) {
				case UPLOAD_ERR_FORM_SIZE:	//fall-through
				case UPLOAD_ERR_INI_SIZE:
					Flash::Instance()->addError('The file you tried to upload is too large, the maximum size is '.ini_get('upload_max_filesize'));
					break;
				case UPLOAD_ERR_NO_FILE:
					Flash::Instance()->addError('No file specified');
					break;
				default:
					Flash::Instance()->addError('There was a problem uploading your file. In case this is a temporary error, please try again');
					break;
			}
			sendTo($sendto);
			return;
		}
		//load the model to attach to
		$success = false;
		$model = DataObject::Construct($modelname);
		if ($modelname == 'TactileAccount') {
			$success = $model->load(Omelette::getAccount()->id);
		} else {
			$success = $model->load($this->_data['id']);
		}
		if (!$success) {
			Flash::Instance()->addError('There was a problem loading the model to attach to. Please try again.');
			sendTo($sendto);
			return;
		}
		$upload_data = $_FILES['Filedata'];
		
		
		// Did they upload a logo?
		$is_logo = false;
		$user = CurrentlyLoggedInUser::Instance();
		if (isset($this->_data['is_logo']) && $user->canEdit($model)) {
			switch ($upload_data['type']) {
				case 'image/gif':
				case 'image/jpeg':
				case 'image/pjpeg':
				case 'image/png':
				case 'image/x-png': {
					
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
						
						unset($tn);
						unset($tn2);
						
						$is_logo = true;
					} catch(Image_Thumbnailer_Exception $e) {
						Flash::Instance()->addError($e->getMessage());
					}
					
					break;
				}
				default:
					Flash::Instance()->addError($upload_data['type'] . ': Not an image. Supported formats are JPEG, GIF, and PNG.');
					sendTo($sendto);
					return;
			}
		}
		
		
		// Was it an account logo?
		$is_account_logo = false;
		if (isset($this->_data['is_account_logo']) && isModuleAdmin()) {
			switch ($upload_data['type']) {
				case 'image/gif':
				case 'image/jpeg':
				case 'image/pjpeg':
				case 'image/png':
				case 'image/x-png': {
					require_once 'Image/Thumbnailer.php';
					try {
						$width = 266;
						$height = 75;
						$tn = new Image_Thumbnailer($upload_data['tmp_name'], $upload_data['type']);
						$tn->resize($width, $height, false, false);
						$upload_data['name'] = $width . 'x' . $height . '_' . $upload_data['name'];
						$upload_data['bucket'] = S3_PUBLIC_BUCKET;
						$tn->save($upload_data['tmp_name']);
						
						unset($tn);
						
						$is_account_logo = true;
					} catch(Image_Thumbnailer_Exception $e) {
						Flash::Instance()->addError($e->getMessage());
					}
					break;
				}
				default:
					Flash::Instance()->addError($upload_data['type'] . ': Not an image. Supported formats are JPEG, GIF, and PNG.');
					sendTo('appearance');
					return;
			}
			
			try {
				$upload_data['account_id'] = Omelette::getAccount()->id;
				$attachment = new S3Attachment($model);
				$errors = array();
				$file = $attachment->attachFile($upload_data, $errors);
			} catch (Zend_Http_Client_Adapter_Exception $e) {
				$file = false;
				$errors[] = $e->getMessage();
			}
			
			if ($file !== FALSE) {
				Flash::Instance()->addMessage('File saved successfully');
			} else {
				Flash::Instance()->addError('Error attaching file to object');
				Flash::Instance()->addErrors($errors);
			}
			
		} else {
			//then make the attachment relationship
			try {
				$attachment = new S3Attachment($model);
				if(isset($this->_data['comment'])) {
					$upload_data['comment'] = $this->_data['comment'];
				}
				$errors = array();
				$file = $attachment->attachFile($upload_data, $errors);
				if ($is_logo) {
					$file2 = $attachment->attachFile($upload_data2, $errors);
				}
			} catch (Zend_Http_Client_Adapter_Exception $e) {
				$file = false;
				$errors[] = $e->getMessage();
			}
			
			//handle the result
			if($file!==false && (!isset($file2) || $file2 !== false)) {
				/* @var $file S3File */
				$file;
				if ($is_logo) {
					if ($file2 !== false) {
						$model->thumbnail_id = $file->id;
					}
					$model->logo_id = $file2->id;
					$model->save();
				}
				
				Flash::Instance()->addMessage('File saved successfully');
				$attached_to = $file->getAttachedTo();
				if (empty($attached_to)) {
					Flash::Instance()->addError('Error during redirection');
					sendTo($sendto);
				} else {
					sendTo(key($attached_to).'/view/'.current($attached_to));
				}
				return;
			}
		}
		
		Flash::Instance()->addErrors($errors);
		sendTo($sendto);
		return;
	}
	
}
