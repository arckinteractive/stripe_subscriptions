<?php

/**
 * Setup entity menus
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'menu:entity'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array			Updated menu
 */
function stripe_subscriptions_entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if ($entity instanceof SiteSubscriptionPlan) {

		$return = array();
		if (elgg_is_admin_logged_in()) {

			$return[] = ElggMenuItem::factory(array(
						'name' => 'edit',
						'text' => elgg_echo('edit'),
						'href' => 'admin/stripe_subscriptions/edit?guid=' . $entity->guid,
						'priority' => 200
			));

			$return[] = ElggMenuItem::factory(array(
						'name' => 'delete',
						'text' => elgg_echo('delete'),
						'href' => 'action/subscriptions/plans/delete?guid=' . $entity->guid,
						'is_action' => true,
						'class' => 'elgg-requires-confirmation',
						'rel' => elgg_echo('question:areyousure'),
						'priority' => 800
			));
		}
	}

	return $return;
}

/**
 * Create a Stripe object menu
 *
 * @param string $hook		Equals 'register'
 * @param string $type		Equals 'stripe-actions'
 * @param array $return		Current menu
 * @param array $params		Additional params
 * @return array
 */
function stripe_subscriptions_actions_menu($hook, $type, $return, $params) {

	$object = elgg_extract('object', $params);

	switch ($object->object) {

		case 'subscription' :

			$user = stripe_get_user_from_customer_id($object->customer);

			if (!elgg_instanceof($user) || !$user->canEdit()) {
				return $return;
			}

			if (!$object->cancel_at_period_end) {
				$return[] = ElggMenuItem::factory(array(
							'name' => 'cancel',
							'text' => elgg_echo('stripe:subscriptions:cancel'),
							'href' => "action/subscriptions/cancel?subscription_id={$object->id}&customer_id={$object->customer}",
							'is_action' => 800,
							'class' => 'elgg-requires-confirmation',
							'rel' => elgg_echo('question:areyousure'),
				));
			}

			break;
	}

	return $return;
}

/**
 * Update a wildcard list of pages exempt from router
 *
 * @param string $hook		Equals 'allowed_pages'
 * @param string $type		Equals 'stripe.subscriptions'
 * @param array $return		Current list of allowed pages
 * @param array $params		Additional params
 * @return array			Updated list of allowed pages
 */
function stripe_subscriptions_allowed_pages($hook, $type, $return, $params) {

	if (!is_array($return)) {
		$return = array();
	}

	$return[] = "billing/.*";
	$return[] = "subscriptions/.*";
	$return[] = "settings/.*";
	return $return;
}

/**
 * Route users to their membership plan settings page
 * This router will apply if the plugin settings are set to require active subscriptions
 *
 * @param string $hook		Equals 'route'
 * @param string $type		Equals 'all'
 * @param array $return		Current page handler and URL segments
 * @param array $params		Additional params
 * @return array			Updated page handler and URL segments
 */
function stripe_subscriptions_router($hook, $type, $return, $params) {

	if (!elgg_is_logged_in()) {
		return $return;
	}

	$user = elgg_get_logged_in_user_entity();

	$url = current_page_url();
	if ($pos = strpos($url, '?')) {
		$url = substr($url, 0, $pos);
	}

	$site_url = elgg_get_site_url();
	if ($url == $site_url) {
		return $return;
	}

	$site = elgg_get_site_entity();
	if ($site->isPublicPage($url)) {
		return $return;
	}

	$public_pages = elgg_trigger_plugin_hook('allowed_pages', 'stripe.subscriptions', array(), array());

	foreach ($public_pages as $public) {
		$pattern = "`^{$site_url}{$public}/*$`i";
		if (preg_match($pattern, $url)) {
			return $return;
		}
	}

	$require_subscriptions = (bool) elgg_get_plugin_setting('require_subscriptions', 'stripe_subscriptions');
	$require_subscriptions_exempt = elgg_trigger_plugin_hook('require_subscriptions.exempt', 'stripe.subscriptions', array(
		'entity' => $user
			), false);

	if ($require_subscriptions && !$require_subscriptions_exempt && !stripe_subscriptions_has_membership_subscription($user->guid)) {
		forward('subscriptions/membership/' . $user->username);
	}

	return $return;
}

/**
 * Exempt select users from subscription requirements
 * 
 * @param string $hook		Equals 'require_subscriptions.exempt'
 * @param string $type		Equals 'stripe.subscriptions'
 * @param boolean $return	Current exemption status
 * @param array $params		Additional params
 * @param boolean			Updated exemption status
 */
function stripe_subscriptions_exempt_from_subscriptions_requirement($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (!elgg_instanceof($entity, 'user')) {
		return $return;
	}

	if ($entity->isAdmin()) {
		return true;
	}

	return $return;
}

/**
 * Handle Stripe webhook when a new subscription is created or an existing subscription is updated
 *
 * @param string $hook		Equals 'customer.subscription.created' or 'customer.subscription.updated'
 * @param string $type		Equals 'stripe.events'
 * @param mixed $return		Information to return to Stripe
 * @param array $params		Additional params
 * @uses Stripe_Event $params['event']
 * @return mixed			Information to return to Stripe
 */
function stripe_subscriptions_event_susbscription_updated($hook, $type, $return, $params) {

	$event = elgg_extract('event', $params);

	if (!$event instanceof Stripe_Event) {
		return $return;
	}

	$subscription = $event->data->object;
	$customer_id = $subscription->customer;
	$plan = $subscription->plan;

	$user = stripe_get_user_from_customer_id($customer_id);
	$site_plan = stripe_subscriptions_get_plan_from_id($plan->id);

	if (!$user instanceof ElggUser || !$site_plan instanceof SiteSubscriptionPlan) {
		return array(
			'success' => false,
			'message' => "Corresponding plan or user were not found"
		);
	}

	if ($site_plan->isSubscribed($user->guid)) {
		$result = true;
	} else {
		if ($site_plan->isMembershipPlan()) {
			elgg_set_plugin_user_setting('stripe_membership_subscription_id', $subscription->id, $user->guid, 'stripe_subscriptions');
		}

		$result = $site_plan->subscribe($user->guid);
	}

	if ($result) {
		$site = elgg_get_site_entity();

		$subject = elgg_echo('subscriptions:notify:updated:title', array($plan->name));
		$message = elgg_echo('subscriptions:notify:updated:body', array(
			$user->name,
			elgg_view('stripe/objects/subscription', array(
				'object' => $subscription,
			)),
			elgg_view('output/url', array(
				'href' => elgg_normalize_url('subscriptions/membership'),
			)),
		));

		notify_user($user->guid, $site->guid, $subject, $message, array(), 'email');

		return array(
			'success' => true,
			'message' => 'Subscription has been updated successfully',
		);
	}

	return array(
		'success' => false,
		'message' => 'Unknown error has occurred',
	);
}

/**
 * Handle Stripe webhook when a subscription is canceled
 *
 * @param string $hook		Equals 'customer.subscription.deleted'
 * @param string $type		Equals 'stripe.events'
 * @param mixed $return		Information to return to Stripe
 * @param array $params		Additional params
 * @uses Stripe_Event $params['event']
 * @return mixed			Information to return to Stripe
 */
function stripe_subscriptions_event_susbscription_deleted($hook, $type, $return, $params) {

	$event = elgg_extract('event', $params);

	if (!$event instanceof Stripe_Event) {
		return $return;
	}

	$subscription = $event->data->object;
	$customer_id = $subscription->customer;
	$plan = $subscription->plan;

	$user = stripe_get_user_from_customer_id($customer_id);
	$site_plan = stripe_subscriptions_get_plan_from_id($plan->id);

	if (!$user instanceof ElggUser || !$site_plan instanceof SiteSubscriptionPlan) {
		return array(
			'success' => false,
			'message' => "Corresponding plan or user were not found"
		);
	}

	if ($site_plan->isSubscribed($user->guid)) {
		$result = $site_plan->unsubscribe($user->guid);
		if ($site_plan->isMembershipPlan() && $result) {
			elgg_unset_plugin_user_setting('stripe_membership_subscription_id', $user->guid, 'stripe_subscriptions');
		}
	} else {
		$result = true;
	}

	if ($result) {
		$site = elgg_get_site_entity();

		$subject = elgg_echo('subscriptions:notify:deleted:title', array($plan->name));
		$message = elgg_echo('subscriptions:notify:deleted:body', array(
			$user->name,
			elgg_view('stripe/objects/subscription', array(
				'object' => $subscription,
			)),
			elgg_view('output/url', array(
				'href' => elgg_normalize_url('subscriptions/membership'),
			)),
		));

		notify_user($user->guid, $site->guid, $subject, $message, array(), 'email');

		return array(
			'success' => true,
			'message' => 'Subscription has been deleted successfully',
		);
	}

	return array(
		'success' => false,
		'message' => 'Unknown error has occurred',
	);
}

/**
 * Handle Stripe webhook when a subscription trial is ending
 *
 * @param string $hook		Equals 'customer.subscription.trial_will_end'
 * @param string $type		Equals 'stripe.events'
 * @param mixed $return		Information to return to Stripe
 * @param array $params		Additional params
 * @uses Stripe_Event $params['event']
 * @return mixed			Information to return to Stripe
 */
function stripe_subscriptions_event_susbscription_trial_ending($hook, $type, $return, $params) {

	$event = elgg_extract('event', $params);

	if (!$event instanceof Stripe_Event) {
		return $return;
	}

	$subscription = $event->data->object;
	$customer_id = $subscription->customer;
	$plan = $subscription->plan;

	$user = stripe_get_user_from_customer_id($customer_id);
	$site_plan = stripe_subscriptions_get_plan_from_id($plan->id);

	if (!$user instanceof ElggUser || !$site_plan instanceof SiteSubscriptionPlan) {
		return array(
			'success' => false,
			'message' => "Corresponding plan or user were not found"
		);
	}

	if ($card = stripe_has_card($user->guid)) {
		$payment = elgg_echo('subscriptions:notify:trial_ending:card', array($card->type, $card->last4));
	} else {
		$payment = elgg_echo('subscriptions:notify:trial_ending:no_card');
	}

	$site = elgg_get_site_entity();

	$subject = elgg_echo('subscriptions:notify:trial_ending:title', array($plan->name));
	$message = elgg_echo('subscriptions:notify:trial_ending:body', array(
		$user->name,
		elgg_view('stripe/objects/subscription', array(
			'object' => $subscription,
		)),
		$payment,
		elgg_view('output/url', array(
			'href' => elgg_normalize_url('billing/cards'),
		)),
		elgg_view('output/url', array(
			'href' => elgg_normalize_url('subscriptions/membership'),
		)),
	));

	notify_user($user->guid, $site->guid, $subject, $message, array(), 'email');

	return array(
		'success' => true,
		'message' => 'Subscription has been deleted successfully',
	);
}
