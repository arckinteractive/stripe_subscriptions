<?php

$guid = get_input('guid');
$vars['entity'] = get_entity($guid);

$sticky_values = elgg_get_sticky_values('subscriptions/plans/edit');
if (is_array($sticky_values)) {
	$vars = array_merge($vars, $sticky_values);
}

echo elgg_view_form('subscriptions/plans/edit', array(), $vars);