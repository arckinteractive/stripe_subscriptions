<?php
$user = elgg_extract('entity', $vars, elgg_get_logged_in_user_entity());

?>
<div class="small-12 columns">
	<label title="<?php elgg_echo('required') ?>"><?php echo elgg_echo('subscriptions:membership:select_plan') . elgg_view_icon('required'); ?></label>
	<?php
	echo elgg_view('forms/subscriptions/membership/grid', $vars);
	?>
</div>
<?php

// Display a form to add a card
$require_cards = (bool) elgg_get_plugin_setting('require_cards', 'stripe_subscriptions');
if ($require_cards && !stripe_has_card($user->guid)) {
	?>
	<div class="small-12 columns">
		<label title="<?php elgg_echo('required') ?>"><?php echo elgg_echo('subscriptions:membership:add_card') . elgg_view_icon('required'); ?></label>
		<?php
		echo elgg_view('forms/stripe/cards/add', array(
			'show_footer' => false,
			'show_remember' => false,
		));
		?>
	</div>
	<?php
}

if (elgg_extract('show_footer', $vars, true)) {
	echo '<div class="small-12 elgg-foot columns text-right">';
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('save')
	));
	echo '</div>';
}