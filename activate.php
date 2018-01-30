<?php

// Composer autoload
require_once __DIR__ . '/autoloader.php';

$subtypes = array(
	SiteSubscriptionPlan::SUBTYPE => 'SiteSubscriptionPlan',
);

foreach ($subtypes as $subtype => $class) {
	if (get_subtype_id('object', $subtype)) {
		update_subtype('object', $subtype, $class);
	} else {
		add_subtype('object', $subtype, $class);
	}
}