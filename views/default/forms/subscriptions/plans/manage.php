<?php


?>
<table class="elgg-table elgg-table-alt">
	<thead>
	<th><?php echo elgg_echo('subscriptions:plans:provider_id') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:title') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:amount') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:cycle') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:roles:provide') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:plan_type') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:status') ?></th>
	<th><?php echo elgg_echo('subscriptions:plans:actions') ?></th>
</thead>
<tbody>
	<?php
	$ha = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	$plans = stripe_subscriptions_get_plans();

	foreach ($plans as $plan) {

		if (!$plan instanceof SiteSubscriptionPlan) {
			continue;
		}

		$roles_dropdown_options = array(0 => elgg_echo('subscriptions:plans:roles:select'));
		if (elgg_is_active_plugin('roles')) {
			$roles = roles_get_all_selectable_roles();

			foreach ($roles as $role) {
				$roles_dropdown_options[$role->name] = $role->title;
			}
			$role_input = elgg_view('input/dropdown', array(
				'name' => "plans[$plan->guid][role]",
				'value' => $plan->getRole(),
				'options_values' => $roles_dropdown_options,
			));
		} else {
			$role_input = elgg_view('input/text', array(
				'name' => "plans[$plan->guid][role]",
				'value' => $plan->getRole(),
			));
		}

		echo '<tr>';
		echo '<td>' . $plan->getPlanId() . '</td>';
		echo '<td>' . $plan->title . '</td>';
		echo '<td>' . $plan->getPricing()->getHumanAmount() . '</td>';
		echo '<td>' . $plan->getCycle()->getLabel() . '</td>';
		echo '<td>' . $role_input . '</td>';
		echo '<td>' . elgg_view('input/dropdown', array(
			'name' => "plans[$plan->guid][plan_type]",
			'value' => $plan->getPlanType(),
			'options_values' => array(
				SiteSubscriptionPlan::PLAN_TYPE_MEMBERSHIP => elgg_echo('subscriptions:plans:plan_type:membership'),
				SiteSubscriptionPlan::PLAN_TYPE_SERVICE => elgg_echo('subscriptions:plans:plan_type:service'),
			),
			'required' => true
		)) . '</td>';
		echo '<td>' . elgg_view('input/dropdown', array(
			'name' => "plans[$plan->guid][enabled]",
			'value' => ($plan->isEnabled()) ? 'enabled' : 'disabled',
			'options_values' => array(
				'enabled' => elgg_echo('subscriptions:plans:status:enabled'),
				'disabled' => elgg_echo('subscriptions:plans:status:disabled'),
			),
		)) . '</td>';
		echo '<td>' . elgg_view_menu('entity', array(
			'entity' => $plan,
			'sort_by' => 'priority',
			'class' => 'elgg-menu-hz'
		)) . '</td>';
		echo '<tr/>';
	}

	access_show_hidden_entities($ha);
	?>
</tbody>
</table>

<div class="elgg-foot mam">
	<?php
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('save')
	));
	?>
</div>