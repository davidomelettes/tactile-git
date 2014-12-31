<?php
class HelpController extends Controller {
	
	/*
	 * @var String A reqular expression for matching email addresses
	 */
	protected $_email_regexp = '\b^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$\b';
		
	public function __construct($module=null,$view) {
		parent::__construct($module,$view);
	}
	
	function index() {
		sendTo("/");
	}
	
	function submit() {
		$flash=Flash::Instance();
		
		// Check the required fields are present 
		$fields = array(
			'email'				=> 'Email Address is a required field',
			'support_request'	=> 'Support Request is a required field',
			'subject'			=> 'Subject is a required field'
		);
		$data = array();
		foreach ($fields as $field => $msg) {
			if (!empty($this->_data[$field])){
				$data[$field] = trim($this->_data[$field]);
			} else {
				$flash->addError($msg, $field);
			}
		}
		
		$user = EGS::getUsername();
		$ua_string = $_SERVER['HTTP_USER_AGENT'];
		$extra_info = "\n\n--\nUser: {$user}\nUA String: {$ua_string}";
		
		if (!$flash->hasErrors()){
			// Validate email address
			if (preg_match('/' . $this->_email_regexp . '/i', $data['email'])) {
				$headers = "From: {$data['email']}" . "\r\n" .
					"Reply-To: {$data['email']}" . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
				
				require_once 'Zend/Mail.php';
				$mail = new Zend_Mail();
				$mail->addTo(NOTIFICATIONS_TO)
					->setFrom($data['email'])
					->setSubject($data['subject'])
					->setBodyText($data['support_request'] . $extra_info)
					->addHeader('X-Mailer', 'PHP/' . phpversion());
				$mail->send();
				
			} else {
				$flash->addError('The email address supplied is not valid, please try again.');
			}
		}
	}
	
	function setup(){
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$this->view->set('email', $user->getEmail());
	}
	
}
