<?php

abstract class SimpleEGlet extends EGlet {
	function render() {
		$this->renderer->render($this,$this->smarty);
	}

	function getClassName() {
		return 'eglet';
	}
	
	static function getRenderer() {
		return new SimpleRenderer();
	}

}
?>