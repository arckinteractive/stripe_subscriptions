<?php

function stripe_subscriptions_page_handler($page, $handler) {

	$username = elgg_extract(1, $page, false);
	if ($username) {
		$user = get_user_by_username($username);
	}

	if (!elgg_instanceof($user) || !$user->canEdit()) {
		$user = elgg_get_logged_in_user_entity();
		forward("$handler/membership/$user->username");
	}

	elgg_set_page_owner_guid($user->guid);
	
	echo elgg_view('resources/subscriptions/membership', [
		'username' => $user->username,
	]);
	return true;
}
