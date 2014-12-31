<?php
class AttachmentsController extends Controller {
	protected $_templateobject;
	protected $attachmentModule;
	protected $attachmentController;
	protected $attachmentTable;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new EntityAttachment();
		$this->uses($this->_templateobject);
		
		$this->view->set('controller','Attachments');
	}
	
	protected function setModule($module) {
		$this->attachmentModule = $module;
	}
	
	protected function setController($controller) {
		$this->attachmentController = $controller;
	}
	
	protected function setTable($table) {
		$this->attachmentTable = $table;
	}
	//This is the controller that is been attached to.
	protected function setAttachedController($attached_controller) {
		$this->attachedController = $attached_controller;
	}
	
	public function __call($method,$args) {
		$_GET['entity_id'] = $_GET[$this->attachmentTable . '_id'];
		unset($_GET[$this->attachmentTable . '_id']);
		$_GET['entity_table'] = $this->attachmentTable;
		parent::__call($method,$args);
	}
	
	public function index(){
		$this->view->set('clickaction', 'view');
		
		$entityAttachments = new EntityAttachmentCollection($this->_templateobject);
		$sh = new SearchHandler($entityAttachments, false);
		$sh->AddConstraint(new Constraint('entity_table', '=', $this->attachmentTable));
		parent::index($ticketAttachments);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>$this->attachmentModule,'controller'=>$this->attachmentController,'action'=>'new'),
					'tag'=>'New Attachment'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function view() {
$entityattachment = $this->_uses['EntityAttachment'];
		$entityattachment->load($this->_data['id']);
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'back'=>array(
					'link'=>array('module'=>$this->attachmentModule,'controller'=>$this->attachedController,'action'=>'view','id' => $entityattachment->entity_id),
					'tag'=>'Go Back'
				),
				'download'=>array(
					'link'=>array('module'=>$this->attachmentModule,'controller'=>$this->attachmentController,'action'=>'download', 'id' => $this->_data['id']),
					'tag'=>'Download Attachment'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		
		$this->_uses['File'] = new File();
		$this->_uses['File']->load($entityattachment->file_id);
	}
	
	public function download() {
        // Grab attachment
		$attachment = new EntityAttachment();
		$attachment->load($this->_data['id']);
		
		// Load file
		$file = new File();
		$file->load($attachment->file_id);
		
		// Upload to browser
		$file->SendToBrowser();
		
		// Prevent standard smarty output from occuring. FIXME: Is this the best way of achieving this?
		exit(0);
	}
	
	public function _new() {
	    $this->view->set('entity_id', $this->_data['entity_id']);
		$this->view->set('entity_table', $this->_data['entity_table']);
		$this->view->set('attachmentController', $this->attachmentController);
	}
	
	public function save() {
		$errors = array();
		$file = File::Factory($_FILES['file'],$errors, new File());
		$file->note = $this->_data['note'];
		$file->save();
		
		$attachment = EntityAttachment::Factory(
		    array(
		        'entity_id' => $this->_data['entity_id'],
				'entity_table' => $this->_data['entity_table'],
		        'file_id' => $file->id
		    ),
		    $errors,
		    new EntityAttachment()
		);
		$attachment->save();
		
	    sendBack();
	}
}
?>
