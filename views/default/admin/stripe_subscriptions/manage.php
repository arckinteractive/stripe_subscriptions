<?php

$guid = get_input('guid');
$vars['entity'] = get_input($guid);

$sticky_values = elgg_get_sticky_values('subscriptions/plans/manage');
if (is_array($sticky_values)) {
	$vars = array_merge($vars, $sticky_values);
}


$mod = elgg_view('output/url', array(
	'text' => elgg_echo('admin:stripe_subscriptions:sync'),
	'href' => 'action/subscriptions/plans/sync',
	'is_action' => true,
	'class' => 'elgg-button elgg-button-action mam pam',
		));

echo elgg_view_module('main', elgg_echo('admin:stripe_subscriptions:sync'), $mod);


$mod2 = elgg_view_form('subscriptions/plans/manage', array(
	'class' => 'gateway-form',
		), $vars);

echo elgg_view_module('main', elgg_echo('admin:stripe_subscriptions:manage'), $mod2);
