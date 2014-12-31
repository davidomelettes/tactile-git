<?php

class TestOfEncoding extends ControllerTest {
	
	function setup() {
		parent::setup();
	}
	
	function teardown() {
		parent::teardown();
	}

	function testCharacterEscapingProducesValidUTF8() {
		$text = "http://cl.exct.net/?qsc75ed6575ab99897d393020d6beb6f2cedf0a5b59b19634a454f995243c013";
		$expected = "http://cl.exct.net/?qsc75ed6575ab99897d393020d6beb6f2cedf0a5b59b19634a454f995243c013";
		$this->assertEqual($expected, h($text));
		
		$strings = array(
			"A string without any fancy foreign characters in",
		    "mais coisas a pensar sobre diário ou dois!",
		    "plus de choses à penser à journalier ou à deux!",
		    "¡más cosas a pensar en diario o dos!",
		    "più cose da pensare circa giornaliere o due!",
		    "flere ting å tenke på hver dag eller to!",
		    "Další věcí, přemýšlet o každý den nebo dva!",
		    "mehr über Spaß spät schönen",
		    "më vonë gjatë fun bukur",
		    "több mint szórakozás késő csodálatos kenyér"
		);
		foreach ($strings as $string) {
			$this->assertEqual($string, h($string));
		}
	}
	
}