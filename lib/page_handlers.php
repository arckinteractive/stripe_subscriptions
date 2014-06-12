<?php

function stripe_subscriptions_page_handler($page, $handler) {

	switch ($page[0]) {

		default :
		case 'membership' :

			gatekeeper();

			$username = elgg_extract(1, $page, false);
			if ($username) {
				$user = get_user_by_username($username);
			}

			if (!elgg_instanceof($user) || !$user->canEdit()) {
				$user = elgg_get_logged_in_user_entity();
				forward("$handler/membership/$user->username");
			}

			elgg_set_context('settings');

			elgg_set_page_owner_guid($user->guid);

			$title = elgg_echo('subscriptions:membership:plan');
			$content = elgg_view('stripe_subscriptions/membership/plan', array(
				'entity' => $user,
			));
			$sidebar = false;
			$filter = false;
			break;
	}

	if ($content) {
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
		return true;
	}

	return false;
}
