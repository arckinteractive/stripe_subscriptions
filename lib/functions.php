<?php

/**
 * Get an instance of site subscription plan from its id
 * @param string $plan_id
 * @return SiteSubscriptionPlan
 */
function stripe_subscriptions_get_plan_from_id($plan_id) {

	$plans = elgg_get_entities_from_metadata(array(
		'types' => 'object',
		'subtypes' => SiteSubscriptionPlan::SUBTYPE,
		'limit' => 1,
		'metadata_names' => 'plan_id',
		'metadata_values' => $plan_id,
	));

	return ($plans) ? $plans[0] : false;
}

/**
 * Get site subscription plans
 * 
 * @param string $plan_type		Filter by plan type
 * @param mixed $role_names		Role name or an array of role names
 * @param array $params			Additional options
 * @return ElggBatch
 */
function stripe_subscriptions_get_plans($plan_type = null, $role_names = null, $params = array()) {

	$defaults = array(
		'types' => 'object',
		'subtypes' => SiteSubscriptionPlan::SUBTYPE,
		'limit' => 0
	);
	$getter = 'elgg_get_entities';

	if ($plan_type) {
		$defaults['metadata_name_value_pairs'][] = array(
			'name' => 'plan_type',
			'value' => $plan_type
		);
		$getter = 'elgg_get_entities_from_metadata';
	}

	if ($role_names) {
		$defaults['metadata_name_value_pairs'][] = array(
			'name' => 'role',
			'value' => $role_names
		);
		$getter = 'elgg_get_entities_from_metadata';
	}

	if (is_array($params)) {
		$defaults = array_merge($params, $defaults);
	}

	return new ElggBatch($getter, $defaults);
}

/**
 * Subscribe user to a plan
 * 
 * @param integer $user_guid
 * @param integer $plan_guid
 * @return boolean
 */
function stripe_subscriptions_subscribe_to_plan($user_guid = 0, $plan_guid = 0) {

	if (!$user_guid) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	$user = get_entity($user_guid);
	$plan = get_entity($plan_guid);

	if (!$user instanceof ElggUser || !$plan instanceof SiteSubscriptionPlan) {
		return false;
	}

	if ($plan->isSubscribed($user->guid)) {
		return true;
	}

	$stripe = new StripeClient($user->guid);

	if ($plan->isMembershipPlan()) {

		$subscription = stripe_subscriptions_get_membership_subscription($user->guid);
		if ($subscription) {
			$subscription = $stripe->updateSubscription($subscription->id, array(
				'plan' => $plan->getPlanId()
			));
		} else {
			$subscription = $stripe->createSubscription(array(
				'plan' => $plan->getPlanId()
			));
		}

		if ($subscription) {
			elgg_set_plugin_user_setting('stripe_membership_subscription_id', $subscription->id, $user_guid, 'stripe_subscriptions');
			return $plan->subscribe($user->guid);
		}
	} else {
		$subscription = $stripe->createSubscription(array(
			'plan' => $plan->getPlanId()
		));
		if ($subscription) {
			return $plan->subscribe($user->guid);
		}
	}

	return false;
}

/**
 * Unsubscribe user from a plan
 * 
 * @param integer $user_guid
 * @param integer $subscription_id		Stripe subscription ID
 * @param boolean $at_period_end
 * @return boolean
 */
function stripe_subscriptions_cancel_subscription($user_guid = 0, $subscription_id = '', $at_period_end = true) {

	if (!$user_guid) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	$user = get_entity($user_guid);

	if (!$user instanceof ElggUser) {
		return false;
	}

	$stripe = new StripeClient($user->guid);
	$subscription = $stripe->getSubscription($subscription_id);

	if ($subscription) {
		$plan = stripe_subscriptions_get_plan_from_id($subscription->plan->id);
		$subscription = $stripe->cancelSubscription($subscription->id, $at_period_end);
	}

	if ($plan instanceof SiteSubscriptionPlan && $subscription->status == 'canceled') {
		if ($plan->unsubscribe($user->guid)) {
			if ($plan->isMembershipPlan()) {
				elgg_unset_plugin_user_setting('stripe_membership_subscription_id', $user_guid, 'stripe_subscriptions');
			}
		}
	}

	if ($subscription->status == 'canceled' || $subscription->cancel_at_period_end) {
		return true;
	}
	
	return false;
}

/**
 * Check if the user has a membership subscription
 *
 * @param integer $user_guid	GUID of the user
 * @param boolean $refresh		A flag to reload data from Stripe
 * @return boolean
 */
function stripe_subscriptions_has_membership_subscription($user_guid = 0, $refresh = false) {
	if (!$refresh) {
		return (bool) elgg_get_plugin_user_setting('stripe_membership_subscription_id', $user_guid, 'stripe_subscriptions');
	}

	return (bool) stripe_subscriptions_get_membership_subscription($user_guid);
}

/**
 * Get current mmbership subscription
 *
 * @param integer $user_guid
 * @return boolean
 */
function stripe_subscriptions_get_membership_subscription($user_guid = 0) {

	if (!$user_guid) {
		$user_guid = elgg_get_logged_in_user_guid();
	}

	$subscription_id = elgg_get_plugin_user_setting('stripe_membership_subscription_id', $user_guid, 'stripe_subscriptions');
	if ($subscription_id) {
		$stripe = new StripeClient($user_guid);
		$subscription = $stripe->getSubscription($subscription_id);
		if ($subscription) {
			return $subscription;
		}
	}

	elgg_unset_plugin_user_setting('stripe_membership_subscription_id', $user_guid, 'stripe_subscriptions');
	return false;
}

/**
 * Get user's current active membership plan
 *
 * @param integer $user_guid
 * @return SiteSubscriptionPlan|false
 */
function stripe_subscriptions_get_membership_plan($user_guid = 0) {

	$subscription = stripe_subscriptions_get_membership_subscription($user_guid);

	if ($subscription && $subscription->status !== 'canceled' && $subscription->status !== 'unpaid') {
		return stripe_subscriptions_get_plan_from_id($subscription->plan->id);
	}

	return false;
}
