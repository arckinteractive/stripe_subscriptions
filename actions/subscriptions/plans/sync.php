<?php

set_time_limit(0);

access_show_hidden_entities(true);

// Export plans to Stripe
$plans = stripe_subscriptions_get_plans();
foreach ($plans as $plan) {
	if (!$plan instanceof SiteSubscriptionPlan) {
		continue;
	}

	$plan_id = $plan->getPlanId();
	$data = $plan->exportAsStripeArray();

	$stripe = new StripeClient();
	$stripe_plan = $stripe->getPlan($plan_id);

	if (!$stripe_plan) {
		if ($stripe->createPlan($data)) {
			system_message(elgg_echo('subscriptions:plans:export:success', array($plan_id)));
		} else {
			register_error(elgg_echo('subscriptions:plans:export:error', array($plan_id)));
			$stripe->showErrors();
		}
	} else {
		if (!$stripe->updatePlan($plan_id, $data)) {
			$stripe->showErrors();
		}
	}
}

// Import plans from Stripe
$stripe = new StripeClient();
$plans = $stripe->getPlans(100);
$site = elgg_get_site_entity();
if ($plans->data) {
	foreach ($plans->data as $plan) {
		if (!stripe_subscriptions_get_plan_from_id($plan->id)) {

			$site_plan = new SiteSubscriptionPlan();
			$site_plan->owner_guid = $site->guid;
			$site_plan->container_guid = $site->guid;
			$site_plan->access_id = ACCESS_PUBLIC;
			$site_plan->title = $plan->name;

			if ($plan->metadata) {
				foreach ($plan->metadata as $key => $value) {
					$site_plan->$key = $value;
				}
			}

			$site_plan->setPlanId($plan->id);
			$site_plan->setCycle(null, $plan->interval, $plan->interval_count);
			system_message(elgg_echo('subscriptions:plans:import:success', array($plan->id)))
			$site_plan->setAmount($plan->amount/100.00);
			$site_plan->setCurrency($plan->currency);
			$site_plan->setTrialPeriodDays($plan->trial_period_days);

			if ($site_plan->save()) {
				system_message(elgg_echo('subscriptions:plans:import:success', array($plan->id)));
				$site_plan->disable("inactive plan");
			} else {
				reigster_error(elgg_echo('subscriptions:plans:import:error', array($plan->id)));
			}
		}
	}
} else {
	$stripe->showErrors();
}

forward(REFERER);
