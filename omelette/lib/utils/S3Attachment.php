<?php

/**
 * Responsible for handling the relationship between models and files
 * 
 * @author gj
 */
class S3Attachment {

	/**
	 * The DataObject the file is to be attached to
	 *
	 * @var DataObject
	 */
	protected $model;
	
	/**
	 * The name of the foreign-key column for the model
	 *
	 * @var String
	 */
	protected $fkey;
	
	/**
	 * Constructor
	 * Give the class a model that wants to have a file attached to it
	 * 
	 * @param DataObject $model
	 */
	function __construct(DataObject $model) {
		$this->model = $model;
		$this->fkey = str_replace('tactile_', '', strtolower($model->get_name()).'_id');
	}
	
	/**
	 * Does the attaching- this created an S3File model, which is saved to the database and used to \
	 * build an S3_Value_Object which is sent to the S3 service
	 *
	 * @param Array $upload_data
	 * @return S3File|Boolean
	 */
	public function attachFile($upload_data, &$errors = array()) {
		$db = DB::Instance();
		$db->StartTrans();
		$upload_data[$this->fkey] = $this->model->id;
		$file = S3File::Create($upload_data, $errors);
		
		if($file !== false && false !== $file->save()) {
			$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
			$object = $file->getValueObject($upload_data['tmp_name']);
			$success = $s3->object->put($object, $file->bucket == S3_PUBLIC_BUCKET);
			if($success!==false) {
				$db->CompleteTrans();
				return $file;
			}
			Flash::Instance()->addError('File storage error: ' . (string)$s3->object->getError()->Code);
		}
		$db->FailTrans();
		$db->CompleteTrans();
		return false;		
	}
}

?>
