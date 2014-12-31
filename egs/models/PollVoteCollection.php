<?php
class PollVoteCollection extends DataObjectCollection {

	function __construct() {
		parent::__construct(new PollVote());
	}
}
?>