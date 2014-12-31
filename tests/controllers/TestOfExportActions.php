<?php

class TestOfExportActions extends ControllerTest {
	
	protected $crm_bits = array(
			'company_industries'=>'industry_id',
			'company_classifications'=>'classification_id',
			'company_sources'=>'source_id',
			'company_statuses'=>'status_id',
			'company_types'=>'type_id'
		);

	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		
		$db = DB::Instance();
		
		$query = 'DELETE FROM notes';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM users WHERE person_id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM organisations WHERE id>1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM opportunities';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM tactile_activities';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'DELETE FROM custom_fields';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$this->loadFixtures('exports');
		EGS::setCompanyId(1);
		EGS::setUsername('greg//tactile');
	}
	
	function tearDown() {
		$db = DB::Instance();
				
		$query = 'UPDATE people SET jobtitle=\'\'';
		$db->Execute($query) or die($db->ErrorMsg());
		
		foreach($this->crm_bits as $tablename=>$blah) {
			$query = 'DELETE FROM '.$tablename;
			$db->Execute($query);
		}
		
		$query = 'DELETE FROM tags';
		$db->Execute($query);
		
		parent::tearDown();	
	}
	
	function testExportAllClientsAddsTask() {
		$this->setURL('organisations/export');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);		
	}
	
	function testOrganisationExporterForClients() {
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM organisations WHERE id=1';
		$row = $db->getRow($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['id'], $row['id']);
		$this->assertEqual($first['name'], $row['name']);
		$this->assertEqual($first['accountnumber'], $row['accountnumber']);
		//$this->assertEqual($first['creditlimit'], $row['creditlimit']); // no longer a field
		$this->assertEqual($first['description'], $row['description']);
	}
	
	function testOrganisationExporterForTwoClients() {
		$this->saveFixture('all_main_fields', 'organisations');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM organisations order by name';
		$rows = $db->getArray($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		
		$i=0;
		foreach($actual_rows as $actual_row) {
			$row = $rows[$i];
			$this->assertEqual($actual_row['id'], $row['id']);
			$this->assertEqual($actual_row['name'], $row['name']);
			$this->assertEqual($actual_row['accountnumber'], $row['accountnumber']);
			$this->assertEqual($actual_row['description'], $row['description']);
			$i++;
		}
	}
	
	function testCSVOutputFormatter() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'accountnumber'=>'ABC01', 'website'=>'http://www.example.com','employees'=>12)
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name', 'accountnumber', 'creditlimit', 'vatnumber',
			'companynumber', 'website', 'employees', 'description'));
		$formatter->output($rows);

		$expected = '1,"Test Company",ABC01,,,,http://www.example.com,12,';
		
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testOutputWithTwoRows() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'accountnumber'=>'ABC01', 'website'=>'http://www.example.com','employees'=>12),
			array('id'=>2,'name'=>'Another Company', 'accountnumber'=>'CDE04', 'website'=>'http://www.google.com','employees'=>0)
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name', 'accountnumber', 'website', 'description'));
		$formatter->output($rows);
		
		$expected = <<<EOD
1,"Test Company",ABC01,http://www.example.com,
2,"Another Company",CDE04,http://www.google.com,

EOD;
		$actual = stream_get_contents($formatter->getStream());
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testOutputWithValuesMissingOnEnd() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'accountnumber'=>'ABC01')
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name', 'accountnumber', 'foo'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company",ABC01,';
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testOutputWithValuesMissingInMiddle() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'accountnumber'=>'ABC01')
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','foo', 'accountnumber'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company",,ABC01';
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testExportWithContactMethods() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('contact_methods', 'organisation_contact_methods');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		$exporter->setOrder('id');
		
		$actual_rows = $exporter->getAll();
		next($actual_rows);
		$row = current($actual_rows);
		
		$this->assertEqual($row['name'], 'Client 100');
		$this->assertEqual($row['phone'], '0121 234 5543');
		$this->assertEqual($row['email'], 'sales@tactilecrm.com');
	}
	
	function testExporterOutputIntoFormatter() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('contact_methods', 'organisation_contact_methods');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		$exporter->setOrder('id');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		
		$memory_stream = fopen('php://memory', 'w+');
		$formatter = new CSVExportFormatter($actual_rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name', 'accountnumber', 'phone', 'fax', 'email'));
		$formatter->output($actual_rows);
		
		$expected = <<<EOD
1,"Default Company",ABC02,,,
100,"Client 100",CXXX,"0121 234 5543",,sales@tactilecrm.com

EOD;
		$actual = stream_get_contents($formatter->getStream());
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testWithCRMValues() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('contact_methods', 'organisation_contact_methods');
		$this->saveMultiFixture('crm_defaults');
		$db = DB::Instance();
		foreach(array('source_id', 'status_id', 'classification_id') as $table => $field) {
			$query = 'UPDATE organisations SET ' . $field . '= 1 WHERE id=100';
			$db->Execute($query) or die($db->ErrorMsg());
		}
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		$exporter->setOrder('id');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		
		$memory_stream = fopen('php://memory', 'w+');
		$formatter = new CSVExportFormatter($actual_rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name', 'accountnumber', 'source', 'status', 'industry', 'classification'));
		$formatter->output($actual_rows);
		
		$expected = <<<EOD
1,"Default Company",ABC02,,,,
100,"Client 100",CXXX,"Test Source","Test Status",,"Test Classification"

EOD;
		$actual = stream_get_contents($formatter->getStream());
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testOutputWithNewlines() {
		$description = <<<EOD
A description
with newlines.
Not just one, but
a few
EOD;
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'accountnumber'=>'ABC01', 'description'=>$description)
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','foo', 'accountnumber', 'description'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company",,ABC01,"'.$description.'"';
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testFormatterWithArrayDataValues() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'foo'=>array('a','b','c'))
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','foo'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company",a|b|c';
		
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testExporterReturningTags() {
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();

		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertTrue(is_array($first['tags']));
		$this->assertEqual(count($first['tags']), 2);
		$this->assertEqual($first['tags'], array('Bar', 'Foo'));
	}
	
	function testFormattingTags() {
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
				
		$actual_rows = $exporter->getAll();

		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($actual_rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','tags'));
		$formatter->output($actual_rows);
		
		$expected = '1,"Default Company",Bar|Foo';
		
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testTwoCompaniesWithTags() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		
		$exporter = new OrganisationExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
				
		$actual_rows = $exporter->getAll();

		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($actual_rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','tags'));
		$formatter->output($actual_rows);
		
		$expected = <<<EOD
1,"Default Company",Bar|Foo
100,"Client 100",Bar|Baz

EOD;
		
		$actual = stream_get_contents($formatter->getStream());
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testWithTagsThatHaveCommas() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'tags'=>array('a,b','c','d'))
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','tags'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company","a,b|c|d"';
		
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testWithTagsThatHavePipes() {
		$rows = array(
			array('id'=>1,'name'=>'Test Company', 'tags'=>array('a|b','c','d'))
		);
		
		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','tags'));
		$formatter->output($rows);
		
		$expected = '1,"Test Company","""a|b""|c|d"';
		
		$actual = trim(stream_get_contents($formatter->getStream()));
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testExporterWithTagRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getByTag('Foo');
		
		$this->assertEqual(count($actual_rows), 1);
		$row = current($actual_rows);
		
		$this->assertEqual($row['name'], 'Default Company');
		$this->assertEqual($row['tags'], array('Bar', 'Foo'));
	}

	function testTagRestrictionForTwoMatching() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		$this->saveFixtureRows('client_access', 'organisation_roles');
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag('Bar');		
		$this->assertEqual(count($actual_rows), 2);
		
		$row = current($actual_rows);
		$this->assertEqual($row['name'], 'Default Company');
		$this->assertEqual($row['tags'], array('Bar', 'Foo'));
		next($actual_rows);
		
		$row2 = current($actual_rows);
		$this->assertEqual($row2['name'], 'Client 100');
		$this->assertEqual($row2['tags'], array('Bar', 'Baz'));
	}
	
	function testTwoTagRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		$this->saveFixtureRows('client_access', 'organisation_roles');
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag(array('Bar','Baz'));		
		$this->assertEqual(count($actual_rows), 1);
		
		$row = current($actual_rows);
		
		$this->assertEqual($row['name'], 'Client 100');
		$this->assertEqual($row['tags'], array('Bar', 'Baz'));
	}	
	
	function testAccess() {
		TestAdminChecker::$return_value = false;
		$this->injector->register('TestAdminChecker');
	
		$this->saveFixture('all_main_fields', 'organisations');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$db = DB::Instance();				
		$query = 'INSERT INTO hasrole(roleid,username) VALUES (2,\'user2//tactile\')';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$exporter = new OrganisationExporter();
		$exporter->setUsername('user2//tactile');
		$exporter->setUserCompanyId(1);
		
		$query = 'SELECT * FROM organisations where id=1';
		$rows = $db->getArray($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		
		$i=0;
		foreach($actual_rows as $actual_row) {
			$row = $rows[$i];
			$this->assertEqual($actual_row['id'], $row['id']);
			$this->assertEqual($actual_row['name'], $row['name']);
			$this->assertEqual($actual_row['accountnumber'], $row['accountnumber']);
			$this->assertEqual($actual_row['description'], $row['description']);
			$i++;
		}
	}
	
	function testTaskExecution() {
		$task_data = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		
		$attachment = base64_decode($mail->getPartContent(0));
		
		$expected = <<<EOD
id,name,created,lastupdated,accountnumber,status,source,classification,industry,type,website,phone,fax,email,street1,street2,street3,town,county,postcode,country_code,description,tags
1,"Default Company","2007-04-19 14:00:05","2007-10-25 15:31:03",ABC02,,,,,,,,,,"45 Acacia Avenue",,,Bananaville,Bananashire,"BA1 3HT",GB,"Default Company specialise in the sourcing and distribution of default values, covering everything from Toasters to Kettles.

There's information about them on http://www.google.com",

EOD;
		$this->assertEqual($expected, $attachment);
	}
	
	function testTaskExecutionWithTagRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		$this->saveFixtureRows('client_access', 'organisation_roles');
		
		$task_data = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags'=>array('Bar')
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		$attachment = base64_decode($mail->getPartContent(0));
		$stream = fopen('php://memory','w+');
		fwrite($stream, $attachment);
		rewind($stream);
		$expected_rows = array(
			array(
				'1',
				'Default Company',
				'2007-04-19 14:00:05','2007-10-25 15:31:03',
				'ABC02',
				'','','','','','','','','','45 Acacia Avenue','','','Bananaville','Bananashire','BA1 3HT','GB',
				"Default Company specialise in the sourcing and distribution of default values, covering everything from Toasters to Kettles.\n\nThere's information about them on http://www.google.com",
				'Bar|Foo'
			),	
			array(
				'100',
			    'Client 100',
				'2007-04-19 14:00:09','2007-04-19 14:00:09',
			    'CXXX',
				'','','','','',
			    '',
				'','','','','','','','','','','',
				'Bar|Baz'
		    )	
		);
		$headings = fgetcsv($stream);
		$row_1 = fgetcsv($stream);
		$row_2 = fgetcsv($stream);
		fclose($stream);		
		$this->assertEqual(array($row_1,$row_2), $expected_rows);
		
	}
	
	function testTaskExecutionWithTwoTagRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('client_tag_map', 'tag_map');
		$this->saveFixtureRows('client_tag_map2', 'tag_map');
		$this->saveFixtureRows('client_access', 'organisation_roles');
		
		$task_data = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags'=>array('Bar','Baz')
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		$attachment = base64_decode($mail->getPartContent(0));
		$stream = fopen('php://memory','w+');
		fwrite($stream, $attachment);
		rewind($stream);
		$expected_rows = array(
			array(
				'100',
			    'Client 100','2007-04-19 14:00:09','2007-04-19 14:00:09',
			    'CXXX',
				'','','','','',
			    '',
				'','','','','','','','','','','',
				'Bar|Baz'
		    )
		);
		$headings = fgetcsv($stream);
		$row_1 = fgetcsv($stream);
		$row_2 = fgetcsv($stream);
		fclose($stream);
		$this->assertFalse($row_2);
		$this->assertEqual(array($row_1), $expected_rows);
		
	}
	
	function testExporterWithByTownRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('org_address', 'organisation_addresses');
		$db = DB::Instance();
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getBy('town', 'Clienttown');
		
		$this->assertEqual(count($actual_rows), 1);
		$row = current($actual_rows);
		
		$this->assertEqual($row['name'], 'Client 100');
		$this->assertEqual($row['town'], 'Clienttown');
	}
	
	function testExporterWithByCountyRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getBy('county', 'Bananashire');
		
		$this->assertEqual(count($actual_rows), 1);
		$row = current($actual_rows);
		
		$this->assertEqual($row['name'], 'Default Company');
		$this->assertEqual($row['town'], 'Bananaville');
		$this->assertEqual($row['county'], 'Bananashire');
	}
	
	function testControllerActionWithTownRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		
		ob_start();
		$this->setURL('organisations/export?query=by_town&q=Clienttown');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key'=>'town',
			'value'=>'Clienttown'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testTaskExecutionWithTownRestriction() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('org_address', 'organisation_addresses');
		$db = DB::Instance();
		$task_data = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key'=>'town',
			'value'=>'Clienttown'
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		$this->assertEqual($mail->getPartCount(), 1);
		$attachment = base64_decode($mail->getPartContent(0));
		$stream = fopen('php://memory','w+');
		fwrite($stream, $attachment);
		rewind($stream);
		$expected_rows = array(
			array(
				'100',
			    'Client 100','2007-04-19 14:00:09','2007-04-19 14:00:09',
			    'CXXX',
				'','','','','',
			    '',
				'','','',
				'100 Client Road',
				'Clientburb',
				'',
				'Clienttown',
				'Clientshire',
				'CL34 5LK',
				'GB',
				'',
				''
		    )
		);
		$headings = fgetcsv($stream);
		$row_1 = fgetcsv($stream);
		$row_2 = fgetcsv($stream);
		fclose($stream);
		$this->assertFalse($row_2);
		$this->assertEqual(array($row_1), $expected_rows);
	}
	
	function testTagsAsControllerDataAreUsed() {
		ob_start();
		$this->setURL('organisations/export?tag[0]=Public+Sector');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'organisation',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags' => array('Public Sector')
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);		
	}
	
	function testPagingLimitsAreIgnoredForExport() {
		$db = DB::Instance();
		$query = 'INSERT INTO organisations(name, accountnumber, usercompanyid, owner) VALUES (?,?,?,?)';
		$stmt = $db->Prepare($query);
		for($i=2;$i<50;$i++) {
			$data = array('Company '.$i, 'AC'.$i, 1,'greg//tactile');
			$db->execute($stmt, $data);
		}
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 49);
	}
	
	function testLimitsIgnoredWithTagSearch() {
		$this->saveFixtureRows('client_tags', 'tags');
		$db = DB::Instance();
		$query = 'INSERT INTO organisations(id,name, accountnumber, usercompanyid, owner) VALUES (?,?,?,?,?)';
		$stmt = $db->Prepare($query);
		
		$query2 = 'INSERT INTO tag_map (tag_id, organisation_id) VALUES (100,?)';
		$stmt2 = $db->prepare($query2);
		
		$query3 = 'INSERT INTO organisation_roles (organisation_id, roleid, read) VALUES (?,2, true)';
		$stmt3 = $db->prepare($query3);
		
		for($i=2;$i<50;$i++) {
			$data = array($i,'Company '.$i, 'AC'.$i, 1,'greg//tactile');
			$db->execute($stmt, $data) or die($db->ErrorMsg());
			
			$data2 = array($i);
			$db->Execute($stmt2, $data2) or die($db->ErrorMsg());
			
			$data3 = array($i);
			$db->Execute($stmt3, $data3) or die($db->ErrorMsg());
		}
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getByTag(array('Foo'));
		
		$this->assertEqual(count($actual_rows), 48);
	}
	
	function testLimitsIgnoredWithTownRestriction() {
		$this->saveFixtureRows('client_tags', 'tags');
		$db = DB::Instance();
		$query = 'INSERT INTO organisations(id,name, accountnumber, usercompanyid, owner) VALUES (?,?,?,?,?)';
		$stmt = $db->Prepare($query);
		
		$query2 = 'INSERT INTO organisation_roles (organisation_id, roleid, read) VALUES (?,2, true)';
		$stmt2 = $db->prepare($query2);
		
		$query3 = "INSERT INTO organisation_addresses (main, organisation_id, town) VALUES ('true',?,?)";
		$stmt3 = $db->prepare($query3);
		
		for($i=2;$i<50;$i++) {
			$data = array($i,'Company '.$i, 'AC'.$i, 1,'greg//tactile');
			$db->execute($stmt, $data) or die($db->ErrorMsg());
			
			$data2 = array($i);
			$db->Execute($stmt2, $data2) or die($db->ErrorMsg());
			
			$data3 = array($i, 'Testtown');
			$db->Execute($stmt3, $data3) or die($db->ErrorMsg());
		}
		
		$exporter = new OrganisationExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getBy('town', 'Testtown');
		
		$this->assertEqual(count($actual_rows), 48);
	}
	
	function testExportingPersonData() {
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM people WHERE id=1';
		$row = $db->getRow($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['id'], $row['id']);
		$this->assertEqual($first['firstname'], $row['firstname']);
		$this->assertEqual($first['surname'], $row['surname']);
	}
	
	function testExportingTwoPeople() {
		$this->saveFixtureRows('default_people', 'people');
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM people';
		$expected_rows = $db->getArray($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		$first_actual = current($actual_rows);
		$first_expected = current($expected_rows);
				
		$this->assertEqual($first_actual['id'], $first_expected['id']);
		$this->assertEqual($first_actual['firstname'], $first_expected['firstname']);
		$this->assertEqual($first_actual['surname'], $first_expected['surname']);
		
		next($actual_rows);
		next($expected_rows);
		
		$this->assertEqual(count($actual_rows), 2);
		$second_actual = current($actual_rows);
		$second_expected = current($expected_rows);
				
		$this->assertEqual($second_actual['id'], $second_expected['id']);
		$this->assertEqual($second_actual['firstname'], $second_expected['firstname']);
		$this->assertEqual($second_actual['surname'], $second_expected['surname']);
	}
	
	function testExportingPeopleWithCompanies() {
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT p.*, org.name AS organisation FROM people p join organisations org on org.id=p.organisation_id
		 WHERE p.id=1';
		$row = $db->getRow($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['id'], $row['id']);
		$this->assertEqual($first['firstname'], $row['firstname']);
		$this->assertEqual($first['surname'], $row['surname']);
		$this->assertEqual($first['organisation'], $row['organisation']);
	}
	
	function testExportingPeopleWithContactDetails() {
		$this->saveFixtureRows('person_contact_details', 'person_contact_methods');
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['phone'], '0123 345 3455');
		$this->assertEqual($first['email'], 'hello@world.com');
		$this->assertEqual($first['mobile'], '07784 543 544');
	}
	
	function testExportingPeopleWithAddresses() {
		$this->saveFixtureRows('person_address', 'person_addresses');
		$db = DB::Instance();
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
		$this->assertEqual($first['street1'], 'Flat 13');
		$this->assertEqual($first['street2'], '12 Person Road');
		$this->assertEqual($first['street3'], 'Someburb');
		$this->assertEqual($first['town'], 'Peopletown');
		$this->assertEqual($first['county'], 'Peopleshire');
		$this->assertEqual($first['postcode'], 'PE3 3PG');
		$this->assertEqual($first['country_code'], 'GB');
	}
	
	function testExportingPersonDataRestrictedByTag() {
		$this->saveFixtureRows('default_people', 'people');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('default_person_tags', 'tag_map');
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag('Foo');
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['firstname'], 'Greg');
		$this->assertEqual($first['surname'], 'Jones');
		$this->assertEqual($first['tags'], array('Bar','Foo'));
	}
	function testExportingPersonRestrictedByTwoTags() {
		$this->saveFixtureRows('default_people', 'people');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('default_person_tags', 'tag_map');
		$this->saveFixtureRows('default_person_tags_2', 'tag_map');
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag(array('Bar','Baz'));
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['firstname'], 'Sam');
		$this->assertEqual($first['surname'], 'Sparro');
		$this->assertEqual($first['tags'], array('Bar','Baz'));
	}
	
	function testExportingPersonDataRestrictedByJobTitle() {
		$this->saveFixtureRows('default_people', 'people');
		$db = DB::Instance();
		$query = 'UPDATE people SET jobtitle=\'Senior Walrus\' WHERE id=1';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$query = 'UPDATE people SET jobtitle=\'Junior Walrus\' WHERE id=100';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getBy('jobtitle', 'Senior Walrus');
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['firstname'], 'Greg');
		$this->assertEqual($first['surname'], 'Jones');
	}
	
	function testFormattingPersonExport() {
		$this->saveFixtureRows('default_people', 'people');
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$rows = $exporter->getAll();
		
		$stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($rows);
		$formatter->setStream($stream);
		$formatter->setOrder(array('id', 'firstname', 'surname'));
		$formatter->output($rows);
		$output_csv = stream_get_contents($formatter->getStream());
		fclose($stream);
		
		$expected = <<<EOD
1,Greg,Jones
100,Sam,Sparro

EOD;
		
		$this->assertEqual($expected, $output_csv);
	}
	
	function testExecutionForPeople() {
		$this->saveFixtureRows('default_people', 'people');
		$task_data = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		
		$attachment = base64_decode($mail->getPartContent(0));
		
		$expected = <<<EOD
id,title,firstname,surname,suffix,created,lastupdated,jobtitle,organisation,organisation_id,dob,can_call,can_email,language_code,phone,mobile,email,street1,street2,street3,town,county,postcode,country_code,description,tags
1,,Greg,Jones,,"2007-04-19 14:00:11","2007-04-19 14:00:11",,"Default Company",1,,f,f,EN,,,,,,,,,,,,
100,,Sam,Sparro,,"2007-04-19 14:00:17","2007-04-19 14:00:17",,,,,t,t,EN,,,,,,,,,,,,

EOD;
		$this->assertEqual($expected, $attachment);
	}
	
	function testExecutionForPeopleWithTagRestriction() {
		$this->saveFixtureRows('default_people', 'people');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('default_person_tags', 'tag_map');
		$task_data = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags' => array('Foo')
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		
		$attachment = base64_decode($mail->getPartContent(0));
		
		$expected = <<<EOD
id,title,firstname,surname,suffix,created,lastupdated,jobtitle,organisation,organisation_id,dob,can_call,can_email,language_code,phone,mobile,email,street1,street2,street3,town,county,postcode,country_code,description,tags
1,,Greg,Jones,,"2007-04-19 14:00:11","2007-04-19 14:00:11",,"Default Company",1,,f,f,EN,,,,,,,,,,,,Bar|Foo

EOD;

		$this->assertEqual($expected, $attachment);
	}
	
	function testExecutionForPeopleWithJobTitleRestriction() {
		$db = DB::Instance();
		$query = 'UPDATE people SET jobtitle=\'Senior Walrus\' WHERE id=1';
		$db->Execute($query) or die($db->ErrorMsg());
		$this->saveFixtureRows('default_people', 'people');
		$task_data = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key'=>'jobtitle',
			'value'=>'Senior Walrus'
		);
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		$task_storage->write($task_data);
		$mail = new Zend_Mail();
		$task = new DelayedExport();
		$task->setMail($mail);
		
		$this->transport->expectOnce('send');
		$task->load(0);
		$task->execute();
		
		$this->assertEqual($mail->getPartCount(), 1);
		
		$attachment = base64_decode($mail->getPartContent(0));
		$expected = <<<EOD
id,title,firstname,surname,suffix,created,lastupdated,jobtitle,organisation,organisation_id,dob,can_call,can_email,language_code,phone,mobile,email,street1,street2,street3,town,county,postcode,country_code,description,tags
1,,Greg,Jones,,"2007-04-19 14:00:11","2007-04-19 14:00:11","Senior Walrus","Default Company",1,,f,f,EN,,,,,,,,,,,,

EOD;
		$this->assertEqual($expected, $attachment);
	}
	
	function testExportActionViaController() {
		ob_start();
		$this->setURL('people/export');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testExportWithTagRestrictViaController() {
		ob_start();
		$this->setURL('people/export?tag[]=Foo&tag[]=Bar');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags' => array('Foo', 'Bar')
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testExportWithJobTitleRestrictViaController() {
		ob_start();
		$this->setURL('people/export?query=by_jobtitle&q=Senior+Walrus');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'person',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key' =>'jobtitle',
			'value' => 'Senior Walrus'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testExportIgnoresPagingLimits() {
		$db = DB::Instance();
		$query = 'INSERT INTO people(firstname, surname, language_code,usercompanyid, owner) VALUES (?,?,\'EN\',1,\'greg//tactile\')';
		$stmt = $db->Prepare($query);
		for($i=2;$i<50;$i++) {
			$data = array('Bob '.$i, 'Smith'.$i);
			$db->execute($stmt, $data);
		}
		
		$exporter = new PersonExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 49);
	}
	
	function testExportIgnoresPagingLimitsWithJobTitleRestriction() {
		$db = DB::Instance();
		$query = 'INSERT INTO people(id,firstname, surname,jobtitle, language_code,usercompanyid, owner) VALUES (?,?,?,\'Junior Walrus\',\'EN\',1,\'greg//tactile\')';
		$stmt = $db->Prepare($query);
		for($i=2;$i<50;$i++) {
			$data = array($i,'Bob '.$i, 'Smith'.$i);
			$db->execute($stmt, $data) or die($db->ErrorMsg());
		}
		
		$exporter = new PersonExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getBy('jobtitle', 'Junior Walrus');
		
		$this->assertEqual(count($actual_rows), 48);
	}
	
	function testExportIgnoresPagingLimitsWithTagRestriction() {
		$db = DB::Instance();
		$query = 'INSERT INTO people(id,firstname, surname,jobtitle, language_code,usercompanyid, owner) VALUES (?,?,?,\'Junior Walrus\',\'EN\',1,\'greg//tactile\')';
		$stmt = $db->Prepare($query);
		
		$this->saveFixtureRows('client_tags', 'tags');
		
		$query2 = 'INSERT INTO tag_map (tag_id, person_id) VALUES (100,?)';
		$stmt2 = $db->prepare($query2);
		
		for($i=2;$i<50;$i++) {
			$data = array($i,'Bob '.$i, 'Smith'.$i);
			$db->execute($stmt, $data)or die($db->ErrorMsg());
			
			$data2 = array($i);
			$db->Execute($stmt2, $data2) or die($db->ErrorMsg());
		}
		
		$exporter = new PersonExporter();
		$exporter->setUserCompanyId(1);
		$exporter->setUsername('greg//tactile');
		$actual_rows = $exporter->getByTag('Foo');
		
		$this->assertEqual(count($actual_rows), 48);
	}
	
	function testPersonExportWithAccessOnCompanies() {
		$this->saveFixtureRows('client_defaults', 'organisations');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		// We should have two people, one for each user, and a new org
		
		$db = DB::Instance();
		
		// Set alt person to work for the new org
		$query = 'UPDATE people SET organisation_id=100 WHERE id=100';
		$db->Execute($query) or die($db->ErrorMsg());
		
		// Create a new role
		$db->Execute("DELETE FROM roles WHERE id > 2") or die($db->ErrorMsg());
		$query = 'INSERT INTO roles (id, name, usercompanyid) VALUES (3,\'Foo\',1)';
		$db->Execute($query) or die($db->ErrorMsg());
		
		// Give the alt user access to this role
		$query = 'INSERT INTO hasrole(roleid,username) VALUES (3,\'user2//tactile\')';
		$db->Execute($query) or die($db->ErrorMsg());
		
		// Make the new org readable via the new role
		$query = 'INSERT INTO organisation_roles (organisation_id, roleid, read) VALUES (100,3,true)';
		$db->Execute($query) or die($db->ErrorMsg());
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('user2//tactile');		
		
		$query = 'SELECT * FROM people';
		
		$this->assertEqual(count($db->GetArray($query)), 2);	//sanity-check...
		
		$actual_rows = $exporter->getAll();
		
		// Should be able to see everyone
		$this->assertEqual(count($actual_rows), 2);
		$first_actual = current($actual_rows);
				
		$this->assertEqual($first_actual['firstname'], 'Greg');
		$this->assertEqual($first_actual['surname'], 'Jones');
	}
	
	function testPersonExportWithPrivatePeople() {
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_user', 'users');
		
		$db = DB::Instance();
		$query = 'UPDATE people SET private=true, owner=\'user2//tactile\' WHERE id=100'; 
		$db->Execute($query) or die($db->ErrorMsg());
		
		$exporter = new PersonExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');		
		
		$query = 'SELECT * FROM people';
		$this->assertEqual(count($db->GetArray($query)), 2);	//sanity-check...
		
		$actual_rows = $exporter->getAll();
		
		// We are an admin, so we should be able to see everything
		$this->assertEqual(count($actual_rows), 2);
		$first_actual = current($actual_rows);
				
		$this->assertEqual($first_actual['firstname'], 'Greg');
		$this->assertEqual($first_actual['surname'], 'Jones');
	}
	
	function testActivityExport() {
		$this->saveFixtureRows('activities', 'tactile_activities');
		
		$db = DB::Instance();
		
		$exporter = new ActivityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');

		$query = 'SELECT * FROM tactile_activities';
		$this->assertEqual(count($db->GetArray($query)), 3);	//sanity-check...
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 3);
		$first_actual = current($actual_rows);
				
		$this->assertEqual($first_actual['name'], 'Activity 100');
	}
	
	function testActivityExportByTag() {
		$this->saveFixtureRows('activities', 'tactile_activities');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('activities_tag_map', 'tag_map');
		
		$exporter = new ActivityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
				
		$actual_rows = $exporter->getByTag('Foo');

		$memory_stream = fopen('php://memory', 'w+');
		
		$formatter = new CSVExportFormatter($actual_rows);
		$formatter->setStream($memory_stream);
		$formatter->setOrder(array('id', 'name','tags'));
		$formatter->output($actual_rows);
		
		$expected = <<<EOD
100,"Activity 100",Foo
300,"Activity 300",Foo

EOD;
		
		$actual = stream_get_contents($formatter->getStream());
		
		$this->assertEqual($actual, $expected);
		fclose($memory_stream);
	}
	
	function testCampaignMonitorExport() {
		$db = DB::Instance();
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_person_contact_details', 'person_contact_methods');
		
		$expected_rows = $db->getArray("SELECT * FROM people WHERE can_email");
		$this->assertEqual(count($expected_rows), 1);
		
		$exporter = new SubscribablePersonExporter();
		$exporter->setUserCompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		$this->assertEqual(count($actual_rows), 1);

		$first_actual = current($actual_rows);
		$first_expected = current($expected_rows);
				
		$this->assertEqual($first_actual['id'], $first_expected['id']);
		$this->assertEqual($first_actual['firstname'], $first_expected['firstname']);
		$this->assertEqual($first_actual['surname'], $first_expected['surname']);
	}
	
	function testCampaignMonitorExportByTag() {
		$db = DB::Instance();
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('alternative_person_contact_details', 'person_contact_methods');
		$this->saveFixtureRows('client_tags', 'tags');
		$this->saveFixtureRows('default_person_tags', 'tag_map');
		
		$exporter = new SubscribablePersonExporter();
		$exporter->setUserCompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag('Foo');
		$this->assertEqual(count($actual_rows), 1);

		$first_actual = current($actual_rows);
				
		$this->assertEqual($first_actual['id'], '1');
		$this->assertEqual($first_actual['firstname'], 'Greg');
		$this->assertEqual($first_actual['surname'], 'Jones');
	}
}
