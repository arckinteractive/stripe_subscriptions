<?php

gatekeeper();

$username = elgg_extract('username', $vars);
$user = get_user_by_username($username);

if (!$user || !$user->canEdit()) {
	forward('', '404');
}

elgg_set_context('settings');

elgg_set_page_owner_guid($user->guid);

$title = elgg_echo('subscriptions:membership:plan');
$content = elgg_view('stripe_subscriptions/membership/plan', array(
	'entity' => $user,
		));
$sidebar = false;
$filter = false;

if (elgg_is_xhr()) {
	echo $content;
} else {
	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'sidebar' => $sidebar,
		'filter' => $filter,
	));

	echo elgg_view_page($title, $layout);
}
