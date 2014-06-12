<?php

/**
 * Setup menus at page setup
 */
function stripe_subscriptions_pagesetup() {

	elgg_register_menu_item('page', array(
		'name' => 'stripe_subscriptions',
		'href' => '#',
		'text' => elgg_echo('admin:stripe_subscriptions'),
		'context' => 'admin',
		'section' => 'stripe'
	));

	elgg_register_menu_item('page', array(
		'name' => 'stripe_subscriptions:settings',
		'parent_name' => 'stripe_subscriptions',
		'href' => 'admin/plugin_settings/stripe_subscriptions',
		'text' => elgg_echo('admin:stripe_subscriptions:settings'),
		'context' => 'admin',
		'section' => 'stripe',
	));

	elgg_register_menu_item('page', array(
		'name' => 'stripe_subscriptions:create',
		'parent_name' => 'stripe_subscriptions',
		'href' => 'admin/stripe_subscriptions/create',
		'text' => elgg_echo('admin:stripe_subscriptions:create'),
		'context' => 'admin',
		'section' => 'stripe',
	));

	elgg_register_menu_item('page', array(
		'name' => 'stripe_subscriptions:manage',
		'parent_name' => 'stripe_subscriptions',
		'href' => 'admin/stripe_subscriptions/manage',
		'text' => elgg_echo('admin:stripe_subscriptions:manage'),
		'context' => 'admin',
		'section' => 'stripe',
	));

	elgg_register_menu_item('page', array(
		'name' => 'stripe_subscriptions:membership',
		'href' => 'subscriptions/membership',
		'text' => elgg_echo('subscriptions:membership:plan'),
		'selected' => (substr_count(current_page_url(), 'subscriptions/membership')),
		'context' => 'settings',
		'section' => 'stripe',
	));
}

/**
 * Add site membership that may have been previously added on Stripe
 * 
 * @param string $event
 * @param string $type
 * @param ElggUser $user
 */
function stripe_subscriptions_login_user($event, $type, $user) {

	if (stripe_subscriptions_get_membership_plan($user->guid)) {
		return true;
	}

	$stripe = new StripeClient;
	$subscriptions = $stripe->getSubscriptions($user->guid, 100);

	if ($subscriptions->data) {
		foreach ($subscriptions->data as $subscription) {
			$plan = stripe_subscriptions_get_plan_from_id($subscription->plan->id);
			if ($plan instanceof SiteSubscriptionPlan && $plan->isMembershipPlan()) {
				elgg_set_plugin_user_setting('stripe_membership_subscription_id', $subscription->id, $user->id, 'stripe_subscriptions');
				$plan->subscribe($user->guid);
				return true;
			}
		}
	}

	return true;
}