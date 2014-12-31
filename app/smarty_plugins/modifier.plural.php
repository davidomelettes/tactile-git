<?php

function smarty_modifier_plural($string) {
	if (preg_match('/s$/i', $string)) {
		return $string;
	}
	switch ($string) {
		case 'Activity':
			return 'Activities';
		case 'activity':
			return 'activities';
		case 'Opportunity':
			return 'Opportunities';
		case 'opportunity':
			return 'opportunities';
		case 'Person':
			return 'People';
		case 'person':
			return 'people';
		case 'Organisation':
		case 'organisation':
			return $string . 's';
		default:
			return $string;
	}
}
