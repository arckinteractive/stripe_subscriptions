<?php

$entity = elgg_extract('entity', $vars);
$full = elgg_extract('full_view', $vars, false);

if (!$entity instanceof SiteSubscriptionPlan) {
	return;
}

$amount = $entity->getPricing()->getHumanAmount();
$cycle = $entity->getCycle()->getLabel();

if ($entity->isSubscribed()) {
	$input = elgg_view_icon('checkmark');
}

$summary = elgg_view('object/elements/summary', array(
	'title' => $entity->title,
	'subtitle' => elgg_echo('subscriptions:plans:output:price', array($amount, $cycle)),
	'summary' => $entity->description,
		));

echo elgg_view_image_block($input, $summary);
