<?php

$subscription_id = get_input('subscription_id');
$customer_id = get_input('customer_id');

$user = stripe_get_user_from_customer_id($customer_id);

if (!elgg_instanceof($user) || !$user->canEdit()) {
	register_error(elgg_echo('stripe:access_error'));
	forward(REFERER);
}

if (stripe_subscriptions_cancel_subscription($user->guid, $subscription_id)) {
	system_message(elgg_echo('subscriptions:cancel:success'));
} else {
	register_error(elgg_echo('subscriptions:cancel:error'));
}

forward(REFERER);

