<?php

elgg_make_sticky_form('subscriptions/plans/edit');

$guid = get_input('guid', null);
$container_guid = get_input('container_guid', null);
$title = get_input('title');
$description = get_input('description');
$access_id = get_input('access_id', ACCESS_PUBLIC);
$role = get_input('role', 0);
$plan_type = get_input('plan_type');

if (!$title) {
	register_error(elgg_echo('subscriptions:plans:edit:error_required_field_empty'));
	forward(REFERER);
}

$entity = new SiteSubscriptionPlan($guid);

$entity->access_id = $access_id;
$entity->title = $title;
$entity->description = $description;
$entity->setRole($role);
$entity->setPlanType($plan_type);

if (!$guid) {

	$amount = (int) get_input('amount');
	$currency = get_input('currency');
	$cycle = get_input('cycle');
	$trial_period_days = get_input('trial_period_days');

	if ($amount <= 0 || !$currency) {
		register_error(elgg_echo('subscriptions:plans:edit:error_required_field_empty'));
		forward(REFERER);
	}

	$intervals = StripeBillingCycle::getCycles();
	$interval_options = elgg_extract($cycle, $intervals);
	$interval = $interval_options['interval'];
	$interval_count = $interval_options['interval_count'];

	if (!$interval || !$interval_count) {
		register_error(elgg_echo('subscriptions:plans:edit:error_undefined_cycle', array($cycle)));
		forward(REFERER);
	}

	$entity->setAmount($amount);
	$entity->setCurrency($currency);
	$entity->setCycle($cycle);
	$entity->setTrialPeriodDays($trial_period_days);

	$plan_id = implode('_', array_filter(array($entity->getPlanType(), $entity->getRole(), $entity->getCycle()->getCycleName())));
	$entity->setPlanId($plan_id);
}

if ($entity->save()) {

	$plan_id = $entity->getPlanId();
	$data = $entity->exportAsStripeArray();

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

	elgg_clear_sticky_form('subscriptions/plans/edit');
	system_message(elgg_echo('subscriptions:plans:edit:success'));
	$forward_url = 'admin/stripe_subscriptions/manage';
} else {
	register_error(elgg_echo('subscriptions:plans:edit:error_generic'));
	$forward_url = REFERER;
}

forward($forward_url);

