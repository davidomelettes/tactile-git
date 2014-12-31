<?php
interface CheckoutProcess {
	function step(Controller $controller,$step=1);

	function numSteps();

}
?>