<?php

access_show_hidden_entities(true);

$guid = get_input('guid');
$entity = get_entity($guid);

if ($entity instanceof SiteSubscriptionPlan) {
	$plan_id = $entity->getPlanId();

	if ($entity->delete()) {
		system_message(elgg_echo('subscriptions:plans:delete:success'));

		// When the plan is deleted, also remove it from Stripe
		$stripe = new StripeClient();
		$stripe->deletePlan($plan_id);
	}
} else {
	register_error(elgg_echo('subscriptions:plans:delete:error'));
}

forward(REFERER);