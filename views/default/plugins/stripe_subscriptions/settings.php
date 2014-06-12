<?php

$entity = elgg_extract('entity', $vars);

echo '<div>';
echo '<label>' . elgg_echo('subscriptions:settings:require_subscriptions') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('subscriptions:settings:require_subscriptions:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'value' => $entity->require_subscriptions,
	'name' => 'params[require_subscriptions]',
	'options_values' => array(
		false => elgg_echo('option:no'),
		true => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('subscriptions:settings:require_cards') . '</label>';
echo '<div class="elgg-text-help">' . elgg_echo('subscriptions:settings:require_cards:help') . '</div>';
echo elgg_view('input/dropdown', array(
	'value' => $entity->require_cards,
	'name' => 'params[require_cards]',
	'options_values' => array(
		false => elgg_echo('option:no'),
		true => elgg_echo('option:yes'),
	)
));
echo '</div>';

