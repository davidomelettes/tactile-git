<?php

class TestOfOpportunityExports extends ControllerTest {
	
	function setup() {
		parent::setup();
		$this->setDefaultLogin();
		$this->loadFixtures('opp_exports');
		EGS::setCompanyId(1);
		EGS::setUsername('greg//tactile');
	}
	
	function testExportAllOpportunitiesAddsTask() {
		$this->setURL('opportunities/export');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'opportunity',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);		
	}
	
	function testOpportunityExporter() {
		$this->_addDefaultOpps(1);
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM opportunities';
		$row = $db->getRow($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$first = current($actual_rows);
				
		$this->assertEqual($first['id'], $row['id']);
		$this->assertEqual($first['name'], $row['name']);
	}
	
	function testOpportunityExporterForTwoEntries() {
		$this->_addDefaultOpps(2);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM opportunities';
		$rows = $db->getArray($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		
		$i=0;
		foreach($actual_rows as $actual_row) {
			$row = $rows[$i];
			$this->assertEqual($actual_row['id'], $row['id']);
			$this->assertEqual($actual_row['name'], $row['name']);
			$i++;
		}
	}
	
	function testRowWithAllValues() {
		$this->saveFixtureRows('alternative_company', 'organisations');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveMultiFixture('lookup_defaults');
		$this->saveFixtureRows('basic_with_lookups', 'opportunities');
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$db = DB::Instance();
		$query = 'SELECT * FROM opportunities_overview';
		$expected = $db->getRow($query);
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$actual = current($actual_rows);
				
		$this->assertEqual($actual['id'], $expected['id']);
		$this->assertEqual($actual['name'], $expected['name']);
		$this->assertEqual($actual['enddate'], $expected['enddate']);
		$this->assertEqual($actual['status'], $expected['status']);
		$this->assertEqual($actual['source'], $expected['source']);
		$this->assertEqual($actual['type'], $expected['type']);
		$this->assertEqual($actual['organisation_id'], $expected['organisation_id']);
		$this->assertEqual($actual['organisation'], $expected['organisation']);
		$this->assertEqual($actual['person_id'], $expected['person_id']);
		$this->assertEqual($actual['person'], $expected['person']);
		$this->assertEqual($actual['description'], $expected['description']);
		$this->assertEqual($actual['cost'], $expected['cost']);
		$this->assertEqual($actual['probability'], $expected['probability']);
		$this->assertEqual($actual['enddate'], $expected['enddate']);
		
		
	}
	
	function testExportWithTag() {
		$this->_addDefaultOpps(1);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$actual = current($actual_rows);

		$this->assertEqual($actual['tags'], array('foo'));
	}
	
	function testExportWithTwoTags() {
		$this->_addDefaultOpps(1);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (2,'bar', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,1, 'org1')";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$actual = current($actual_rows);

		$this->assertEqual($actual['tags'], array('bar', 'foo'));
	}
	
	function testExportWithUnusedTags() {
		$this->_addDefaultOpps(1);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (2,'bar', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (3,'baz', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,1, 'org1')";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 1);
		$actual = current($actual_rows);

		$this->assertEqual($actual['tags'], array('bar', 'foo'));
	}
	
	function testTwoOppsWithTags() {
		$this->_addDefaultOpps(2);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (2,'bar', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (3,'baz', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,2, 'org2')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (3,2, 'org2')";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getAll();
		
		$this->assertEqual(count($actual_rows), 2);
		$actual = array_shift($actual_rows);

		$this->assertEqual($actual['tags'], array('bar', 'foo'));
		
		$actual = array_shift($actual_rows);

		$this->assertEqual($actual['tags'], array('bar', 'baz'));
	}
	
	function testTaskExecutionForOpps() {
		$this->_addDefaultOpps(1);
		$task_data = array(
			'export_type' => 'opportunity',
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
		
		$attachment = trim(base64_decode($mail->getPartContent(0)));
		
		$expected = <<<EOD
id,name,created,lastupdated,status,organisation_id,organisation,person_id,person,description,cost,probability,enddate,type,source,owner,assigned_to,tags
1,"Opp 1","2011-01-01 01:01:01","2011-01-01 01:01:01",,,,,,,0.00,0,2009-03-01,,,greg//tactile,,
EOD;
		$this->assertEqual($expected, $attachment);
	}
	
	function testTaskCreationWithMineRestriction() {
		$this->setURL('opportunities/export?restriction=mine');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'opportunity',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key' => 'assigned_to',
			'value' => 'greg//tactile'
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testRestrictionOnAssignedWorksForExport() {
		$this->saveFixtureRows('alternative_company', 'organisations');
		$this->saveFixtureRows('alternative_person', 'people');
		$this->saveFixtureRows('user2', 'users');
		
		$this->_addDefaultOpps(6);
		$db = DB::Instance();
		$query = "UPDATE opportunities SET assigned_to='user2//tactile' WHERE id>3";
		$db->Execute($query);
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('user2//tactile');
		
		$actual_rows = $exporter->getBy('assigned_to', 'user2//tactile');
		
		$this->assertEqual(count($actual_rows), 3);
	}
	
	function testTaskCreationWithOpenRestriction() {
		$this->setURL('opportunities/export?restriction=open');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'opportunity',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'key' => 'open',
			'value' => true
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testRestrictionOnOpenWorksForExport() {
		$this->saveMultiFixture('lookup_defaults');

		$this->_addDefaultOpps(6);
		$db = DB::Instance();
		$query = "UPDATE opportunities SET status_id=2 WHERE id>3";
		$db->Execute($query);
		
		$query = "UPDATE opportunities SET status_id=1 WHERE id<=3";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('user2//tactile');
		
		$actual_rows = $exporter->getBy('open', true);
		
		$this->assertEqual(count($actual_rows), 3);
	}
	
	function testExportByTag() {
		$this->_addDefaultOpps(2);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (2,'bar', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (3,'baz', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,2, 'org2')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (3,2, 'org2')";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag(array('foo'));
				
		$this->assertEqual(count($actual_rows), 1);
		$actual = array_shift($actual_rows);

		$this->assertEqual($actual['id'], 1);
		$this->assertEqual($actual['tags'], array('bar', 'foo'));
	}
	
	function testTaskCreationForTag() {
		$this->setURL('opportunities/export');
		$_GET['tag'] = array('foo');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'opportunity',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags' => array('foo')
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testTaskCreationForTwoTags() {
		$this->setURL('opportunities/export');
		$_GET['tag'] = array('foo', 'bar');
		
		$task_storage = new DelayedTaskTemporaryStorage();
		DelayedTask::setDefaultStorage($task_storage);
		
		$this->app->go();

		$r = $this->injector->instantiate('Redirection');
		$this->assertTrue($r->willRedirect());
		
		$f = Flash::Instance();
		$this->assertFalse($f->hasErrors());
		$this->assertEqual(1, count($f->messages));
		
		$expected = array(
			'export_type' => 'opportunity',
			'task_type' => 'DelayedExport',
			'iteration'		=> '1',
			'EGS_USERNAME' => 'greg//tactile',
			'EGS_COMPANY_ID' => 1,
			'USER_SPACE' => 'tactile',
			'tags' => array('foo', 'bar')
		);
		
		$task_data = $task_storage->read(0,false);
		$this->assertEqual($task_data, $expected);	
	}
	
	function testExportByTwoTags() {
		$this->_addDefaultOpps(2);
		$db = DB::Instance();
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (1,'foo', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (2,'bar', 1)";
		$db->Execute($query);
		$query = "INSERT INTO tags (id, name, usercompanyid) VALUES (3,'baz', 1)";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (1,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,1, 'org1')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (2,2, 'org2')";
		$db->Execute($query);
		
		$query = "INSERT INTO tag_map (tag_id, opportunity_id, hash) VALUES (3,2, 'org2')";
		$db->Execute($query);
		
		$exporter = new OpportunityExporter();
		$exporter->setUsercompanyid(1);
		$exporter->setUsername('greg//tactile');
		
		$actual_rows = $exporter->getByTag(array('bar', 'baz'));
				
		$this->assertEqual(count($actual_rows), 1);
		$actual = array_shift($actual_rows);

		$this->assertEqual($actual['id'], 2);
		$this->assertEqual($actual['tags'], array('bar', 'baz'));
	}
	
	function _addDefaultOpps($n = 1) {
		$db = DB::Instance();
		$query = "INSERT INTO opportunities(id, name, owner, enddate, usercompanyid, alteredby, created, lastupdated)
				VALUES(?, ?, 'greg//tactile', '2009-03-01', 1, 'greg//tactile', '2011-01-01 01:01:01', '2011-01-01 01:01:01')";
		$stmt = $db->Prepare($query);
		for($i = 1; $i <= $n; $i++) {
			$db->execute($stmt, array($i, 'Opp ' . $i));
		}
	}
}
