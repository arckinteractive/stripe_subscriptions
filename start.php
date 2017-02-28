<?php

// Composer autoload, depends on how it was installed
if (file_exists(__DIR__ . '/vendors/autoload.php')) {
	require_once __DIR__ . '/vendors/autoload.php';
}

// Load libs
require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\stripe_subscriptions_init', 700);
elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\stripe_subscriptions_pagesetup');

function stripe_subscriptions_init() {

	elgg_register_page_handler('subscriptions', 'stripe_subscriptions_page_handler');
	
	elgg_register_action('subscriptions/plans/delete', __DIR__ . '/actions/subscriptions/plans/delete.php', 'admin');
	elgg_register_action('subscriptions/plans/sync', __DIR__ . '/actions/subscriptions/plans/sync.php', 'admin');
	elgg_register_action('subscriptions/plans/edit', __DIR__ . '/actions/subscriptions/plans/edit.php', 'admin');
	elgg_register_action('subscriptions/plans/manage', __DIR__ . '/actions/subscriptions/plans/manage.php', 'admin');

	elgg_register_action('subscriptions/membership/plan', __DIR__ . '/actions/subscriptions/membership/plan.php');
	elgg_register_action('subscriptions/cancel', __DIR__ . '/actions/subscriptions/cancel.php');

	elgg_register_plugin_hook_handler('register', 'menu:entity', 'stripe_subscriptions_entity_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:stripe-actions', 'stripe_subscriptions_actions_menu');

	// Exempt pages from routing
	elgg_register_plugin_hook_handler('allowed_pages', 'stripe.subscriptions', 'stripe_subscriptions_allowed_pages');

	// Route users to subscriptions management page
	elgg_register_plugin_hook_handler('route', 'all', 'stripe_subscriptions_router', 5);

	// Exempt admins from subscription requirements
	elgg_register_plugin_hook_handler('require_subscriptions.exempt', 'stripe.subscriptions', 'stripe_subscriptions_exempt_from_subscriptions_requirement');

	// Handle Stripe webhooks
	elgg_register_plugin_hook_handler('customer.subscription.created', 'stripe.events', 'stripe_subscriptions_event_susbscription_updated');
	elgg_register_plugin_hook_handler('customer.subscription.updated', 'stripe.events', 'stripe_subscriptions_event_susbscription_updated');
	elgg_register_plugin_hook_handler('customer.subscription.deleted', 'stripe.events', 'stripe_subscriptions_event_susbscription_deleted');
	elgg_register_plugin_hook_handler('customer.subscription.trial_will_end', 'stripe.events', 'stripe_subscriptions_event_susbscription_trial_ending');

	//elgg_register_event_handler('login', 'user', 'stripe_subscriptions_login_user');
}
