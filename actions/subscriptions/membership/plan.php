<?php

$guid = get_input('guid', false);
$user = get_entity($guid);

$plan_guid = get_input('plan_guid');
$plan = get_entity($plan_guid);

if (!$plan instanceof SiteSubscriptionPlan || !$plan->isMembershipPlan()) {
	register_error(elgg_echo('subscriptions:membership:plan:error:no_plan'));
	forward(REFERER);
}

$stripe_token = get_input('stripe-token');
stripe_create_card($user->guid, $stripe_token);

if (stripe_subscriptions_subscribe_to_plan($user->guid, $plan->guid)) {
	system_message(elgg_echo('subscriptions:membership:plan:success'));
} else {
	register_error(elgg_echo('subscriptions:membership:plan:error'));
}

forward('subscriptions');

