<?php

elgg_load_js('parsley.js');

$sticky_values = elgg_get_sticky_values('subscriptions/plans/edit');
if (is_array($sticky_values)) {
	$vars = array_merge($vars, $sticky_values);
}

echo elgg_view_form('subscriptions/plans/edit', array(
	'class' => 'gateway-form',
		), $vars);
