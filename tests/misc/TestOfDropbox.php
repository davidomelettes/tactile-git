<?php

require_once 'Zend/Mail/Message.php';
require_once 'mail/NewEmailParser.php';
require_once LIB_ROOT.'spyc/spyc.php';

class TestOfDropBox extends UnitTestCase {
	
	protected $_parser;
	
	public function __construct() {
		echo "Running ".get_class($this)."\n";
		parent::UnitTestCase();
	}
	
	function saveFixtureRows($fixture_name, $tablename) {
		$db = DB::Instance();
		$fixture = $this->fixtures[$fixture_name];
		foreach($fixture as $row) {
			$result = $db->Replace($tablename, $row, 'id', true);
			if ($result == false) {
				throw new Exception("Inserting fixture rows for $fixture_name into $tablename failed: ".$db->ErrorMsg());
			}
		}
	}
	
	function setup() {
		parent::setup();
		
		global $injector;
		$injector = new Phemto();
		$injector->register('OmeletteModelLoader');
		$this->injector = $injector;
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'mail/');
		Omelette::setUserSpace('tactile');
		$this->view = new TestView($injector);
		$this->app = new Tactile($injector, $this->view);
		$injector->register(new Singleton('DummyRedirectHandler'));
		$injector->register(new Singleton('TestModelLoading'));
		$injector->register('NonSessionFlash');
		
		Mock::generate('Zend_Mail_Transport_Abstract','MockZend_Mail_Transport_Abstract',array('_sendMail'));
		$this->transport = new MockZend_Mail_Transport_Abstract();		
		Zend_Mail::setDefaultTransport($this->transport);
		
		$this->_parser = new NewEmailParser();
		// Uncomment these two lines if you want useful logging
		//$logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
		//$this->_parser->logger = $logger;
		
		$db = DB::Instance();
		
		$query = 'DELETE FROM emails';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM person_contact_methods";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM people WHERE id > 1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM opportunities";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$path = TEST_ROOT.'misc/fixtures/dropbox.yml';
		$fixtures = Spyc::YAMLLoad($path);
		$this->fixtures = $fixtures;
		
		date_default_timezone_set('Europe/London');
	}
	
	function teardown() {
		parent::teardown();
		$db = DB::Instance();
		
		$query = 'DELETE FROM emails';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM person_contact_methods";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM people WHERE id > 1";
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = "DELETE FROM opportunities";
		$db->Execute($query) or die($db->ErrorMsg());
		
		date_default_timezone_set('Europe/London');
	}
	
	function testInstantiation() {
		$this->assertIsA($this->_parser, 'NewEmailParser');
	}
	
	function testApplyWithEmptyMail() {
		$mail = new Zend_Mail_Message(array('raw' => ''));
		$result = $this->_parser->apply($mail);
		$this->assertFalse($result);
	}
	
	function testApplyWithGarbageMail() {
		$db = DB::Instance();
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '0');
		
		$mail = new Zend_Mail_Message(array('raw' => 'vxcvxsdftgdgdZXXTY536'));
		$result = $this->_parser->apply($mail);
		$this->assertFalse($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '0');
	}
	
	function testWithBadDropboxKey() {
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/badkey.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		$this->assertIsA($mail, 'Zend_Mail_Message');
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertFalse($result);
	}
	
	function testDropboxWithOutgoingMailBcc() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbcc.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		$this->assertIsA($mail, 'Zend_Mail_Message');

		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($email->getDirection(), 'outgoing');
		$this->assertEqual($email->received, '2009-07-23 14:42:21');
	}
	
	function assertNow($datetime) {
		$this->assertTrue(abs(strtotime($datetime) - time()) < 1200, $datetime." isn't close enough to 'now' (".date('Y-m-d H:i:s').')');
	}
	
	function testDropboxWithOutgoingMailBccNoContact() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbcc.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		// Should send an email
		$this->transport->expectOnce('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$owner = $email->owner;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($email->getDirection(), 'outgoing');
		$this->assertEqual($owner, 'greg//tactile');
	}
	
	function testDropboxWithDirectionlessMailBccNoContact() {
		$this->saveFixtureRows('nonmatching_user_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbcc.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		// Should send an email
		$this->transport->expectOnce('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($email->getDirection(), '');
	}
	
	function testDropboxWithOutgoingMailForward() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingforward.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		$this->assertIsA($mail, 'Zend_Mail_Message');

		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$subject = $email->subject;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($subject, 'FW: Drop Box Test');
	}
	
	function testDropboxWithOutgoingMailForwardNoContact() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingforward.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		$this->assertIsA($mail, 'Zend_Mail_Message');

		$this->transport->expectOnce('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertIdentical($person_id, null);
	}
	
	function testDropboxWithIncomingMailForward() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/incomingforward.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($email->getDirection(), 'incoming');
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertIdentical($person_id, '200');
		$this->assertEqual($email->received, '2009-10-06 12:30:00');
	}
	
	function testDropboxWithIncomingMailForwardNoContact() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/incomingforward.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectOnce('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($email->getDirection(), 'incoming');
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertIdentical($person_id, null);
	}
	
	function testDropboxWithDirectionlessMailForwardNoContact() {
		$this->saveFixtureRows('nonmatching_user_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/incomingforward.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		// Should send a SINGLE email
		$this->transport->expectCallCount('send', 1);
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertEqual($email->getDirection(), '');
	}
	
	function testDropboxOutgoingMailAttachToOpportunityAndContact() {
		$this->saveFixtureRows('attach_opp', 'opportunities');
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbccopportunity.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$opp_id = $email->opportunity_id;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($opp_id, '100');
		$this->assertEqual($email->getDirection(), 'outgoing');
	}
	
	function testDropboxIncomingMailAttachToOpportunityAndContact() {
		$this->saveFixtureRows('attach_opp', 'opportunities');
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/incomingforwardopportunity.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$opp_id = $email->opportunity_id;
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertEqual($opp_id, '100');
		$this->assertEqual($email->getDirection(), 'incoming');
	}
	
	function testDropboxOutgoingMailAttachToOpportunityMissingContact() {
		$this->saveFixtureRows('attach_opp', 'opportunities');
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbccopportunity.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectNever('send'); // Don't send missing contact emails when attaching to opps
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$opp_id = $email->opportunity_id;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($opp_id, '100');
		$this->assertEqual($email->getDirection(), 'outgoing');
	}
	
	function testDropboxForwardedMailAttachToOpportunityDontNotKnowNobody() {
		$this->saveFixtureRows('attach_opp', 'opportunities');
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/forwardednobodyopportunity.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));

		$this->transport->expectNever('send'); // Don't send missing contact emails when attaching to opps
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$opp_id = $email->opportunity_id;
		$this->assertEqual($from, 'lol@hat.com');
		$this->assertEqual($to, 'rofl@headshot.com');
		$this->assertEqual($opp_id, '100');
		$this->assertEqual($email->getDirection(), ''); // Directionless
	}
	
	function testDropboxForwardedMailIncomingViaCc() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/incomingforwardcc.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertEqual($person_id, '200');
	}
	
	function testSwedishCharacters() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/outgoingbccswedish.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($person_id, '200');
		
		$body = $email->body;
		$this->assertEqual($body, 'Jeg vil blot høre om der stadig. Beskrivelse eller kan vi på anden måde hjælp');
	}
	
	function testForwardedMailWithMissingForwardFromMatchByName() {
		// Common Outlook case
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/forwardedfromoutlook.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'archie@omelett.es');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertEqual($person_id, '200');
	}
	
	function testForwardedMailWithMissingForwardFromNoMatchByName() {
		// Should still save, but be unassigned
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/forwardedfromoutlook.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectOnce('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'Archie Dog');
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertEqual($person_id, NULL);
	}
	
	function testBase64Encoding() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/base64.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$body = $email->body;
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertPattern('/^Hi David,/', $body);
	}
	
	function testMultiLevelBcc() {
		// Emails with mutliple exchanges in will likely contain the user's email address in both a header and the body,
		// so check we don't incorrectly identify a back-and-forth reply BCC'ed to the dropbox as a forward
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/bccmultilevelreply.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$this->assertEqual($from, 'greg@tactilecrm.com');
		$this->assertEqual($to, 'archie@omelett.es');
		$this->assertEqual($person_id, '200');
	}
	
	public function testISO88592() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/iso_8859_2.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$body = $db->getOne("SELECT body FROM emails WHERE subject = 'FW: Polish fonts in emails'");
		$this->assertPattern('/zażółć gęślą jaźń\s+ąężźłćńóś/', $body);
	}
	
	function testCrazyBase64Encoding() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/megalong_base64.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$email = new Email();
		$result = $email->load($db->getOne('SELECT id FROM emails order by created desc limit 1'));
		$this->assertNotIdentical($result, false);
		
		$from = $email->email_from;
		$to = $email->email_to;
		$person_id = $email->person_id;
		$body = $email->body;
		$this->assertEqual($to, 'greg@tactilecrm.com');
		$this->assertPattern('/^Hi David/', $body);
	}
	
	function testFranksEmail() {
		$this->saveFixtureRows('default_user_contact', 'person_contact_methods');
		$this->saveFixtureRows('archie', 'people');
		$this->saveFixtureRows('archie_contact', 'person_contact_methods');
		
		$db = DB::Instance();
		
		$raw = file_get_contents(FILE_ROOT . '/tests/misc/fixtures/frank.mail');
		$mail = new Zend_Mail_Message(array('raw' => $raw));
		
		$this->transport->expectNever('send');
		$result = $this->_parser->apply($mail);
		$this->assertTrue($result);
		
		$count = $db->getOne("SELECT count(*) FROM emails");
		$this->assertIdentical($count, '1');
		
		$body = $db->getOne("SELECT body FROM emails WHERE subject = 'FW: hi'");
		$this->assertPattern('/Swift House Business Centre/', $body);
	}
	
}