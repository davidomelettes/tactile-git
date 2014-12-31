<?php

class UsageWarningHelper {
	public static function displayUsageWarning(&$view = null, $type = null) {
		if (is_null($view)) return;
		
		Autoloader::Instance()->addPath(APP_CLASS_ROOT.'usage/');

		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$plan = $account->getPlan();

		$warnings = array();
		$limit_reached = false;

		if ($type == 'contacts' || is_null($type)) {
			$contact_usage      = new ContactUsageChecker($account);
			$contact_limit      = $plan->contact_limit;
			$contacts_used      = $contact_usage->getUsage();
			$contacts_threshold = floor($contact_limit * 0.9);

			if ($contact_limit != 0 && $contacts_used >= $contacts_threshold) {
				$warnings['contacts'] = array(
					'used' => $contacts_used,
					'limit' => $contact_limit
				);

				if ($contacts_used >= $contact_limit) $limit_reached = true;
			}
		}

		if ($type == 'opportunities' || is_null($type)) {
			$opportunity_usage       = new OpportunityUsageChecker($account);
			$opportunity_limit       = $plan->opportunity_limit;
			$opportunities_used      = $opportunity_usage->getUsage();
			$opportunities_threshold = floor($opportunity_limit * 0.9);

			if ($opportunity_limit != 0 && $opportunities_used >= $opportunities_threshold) {
				$warnings['opportunities'] = array(
					'used' => $opportunities_used,
					'limit' => $opportunity_limit
				);

				if ($opportunities_used >= $opportunity_limit) $limit_reached = true;
			}
		}

		if (count($warnings) > 0) {
			$view->set('display_usage_warning', true);
			$view->set('usage_warnings', $warnings);
			$view->set('usage_warning_types', implode(' and ', array_keys($warnings)));

			if ($limit_reached) {
				$view->set('usage_warning_title', "You've reached your usage limit");
			} else {
				$view->set('usage_warning_title', "You're nearing your usage limit");
			}
		}
	}
}