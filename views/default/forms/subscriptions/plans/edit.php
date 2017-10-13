<?php

$entity = elgg_extract('entity', $vars);
$container = elgg_extract('container', $vars);

$required = (!$entity) ? elgg_format_attributes(array(
			'title' => elgg_echo('required'),
			'class' => 'required'
		)) : '';

if ($entity instanceof SiteSubscriptionPlan) {
	$title = $entity->title;
	$description = $entity->description;
	$plan_id = $entity->getPlanId();
	$plan_type = $entity->getPlanType();
	$cycle = $entity->getCycle()->getCycleName();
	$amount = $entity->getPricing()->getAmount();
	$currency = $entity->getPricing()->getCurrency();
	$trial_period_days = $entity->getTrialPeriodDays();
	$role_name = $entity->getRole();
}
?>

<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:plan_type') ?></label>
	<div><?php echo elgg_echo('subscriptions:plans:plan_type:help') ?></div>
	<?php
	echo elgg_view('input/dropdown', array(
		'name' => 'plan_type',
		'value' => elgg_extract('plan_type', $vars, $plan_type),
		'options_values' => array(
			SiteSubscriptionPlan::PLAN_TYPE_MEMBERSHIP => elgg_echo('subscriptions:plans:plan_type:membership'),
			SiteSubscriptionPlan::PLAN_TYPE_SERVICE => elgg_echo('subscriptions:plans:plan_type:service'),
		),
		'required' => true,
	));
	?>
</div>

<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:title') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'title',
		'value' => elgg_extract('title', $vars, $title),
		'required' => true,
		'parsley-trigger' => 'keyup focusout',
		'parsley-validation-minlength' => 1,
		'parsley-minlength' => 1,
	));
	?>
</div>

<div>
	<label><?php echo elgg_echo('subscriptions:plans:description') ?></label>
	<?php
	echo elgg_view('input/longtext', array(
		'name' => 'description',
		'value' => elgg_extract('description', $vars, $description),
	));
	?>
</div>

<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:amount') ?></label>
	<div><?php echo elgg_echo('subscriptions:plans:amount:help') ?></div>
	<?php
	echo elgg_view('input/stripe/price', array(
		'name' => 'amount',
		'value' => elgg_extract('amount', $vars, $amount),
		'required' => true,
		'disabled' => ($entity->guid),
	));
	?>
</div>
<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:currency') ?></label>
	<div><?php echo elgg_echo('subscriptions:plans:currency:help') ?></div>
	<?php
	echo elgg_view('input/stripe/currency', array(
		'name' => 'currency',
		'value' => elgg_extract('currency', $vars, $currency),
		'required' => true,
		'disabled' => ($entity->guid),
	));
	?>
</div>

<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:cycle') ?></label>
	<div><?php echo elgg_echo('subscriptions:plans:cycle:help') ?></div>
	<?php
	echo elgg_view('input/stripe/cycle', array(
		'name' => 'cycle',
		'value' => elgg_extract('cycle', $vars, $cycle),
		'required' => true,
		'disabled' => ($entity->guid),
	));
	?>
</div>

<div>
	<label <?php echo $required ?>><?php echo elgg_echo('subscriptions:plans:trial_period_days') ?></label>
	<div><?php echo elgg_echo('subscriptions:plans:trial_period_days:help') ?></div>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'trial_period_days',
		'value' => elgg_extract('trial_period_days', $vars, $trial_period_days),
		'required' => true,
		'disabled' => ($entity->guid),
	));
	?>
</div>

<?php
$roles_dropdown_options = array(0 => elgg_echo('subscriptions:plans:roles:select'));
if (elgg_is_active_plugin('roles')) {
	$roles = roles_get_all_selectable_roles();

	foreach ($roles as $role) {
		$roles_dropdown_options[$role->name] = $role->getDisplayName();
	}
	?>
	<div>
		<label><?php echo elgg_echo('subscriptions:plans:roles:provide') ?></label>
		<div><?php echo elgg_echo('subscriptions:plans:roles:provide:help') ?></div>
		<?php
		echo elgg_view('input/dropdown', array(
			'name' => 'role',
			'value' => elgg_extract('role', $vars, $role_name),
			'options_values' => $roles_dropdown_options,
		));
		?>
	</div>
	<?php
} else {
	?>
	<div>
		<label><?php echo elgg_echo('subscriptions:plans:tier') ?></label>
		<div><?php echo elgg_echo('subscriptions:plans:tier:help') ?></div>
		<?php
		echo elgg_view('input/text', array(
			'name' => 'role',
			'value' => elgg_extract('role', $vars, $role_name),
		));
		?>
	</div>
	<?php
}
?>

<div class="elgg-foot columns text-right">
	<?php
	echo elgg_view('input/hidden', array(
		'name' => 'access_id',
		'value' => ($entity->guid) ? $entity->access_id : ACCESS_PUBLIC,
		'required' => true,
	));
	echo elgg_view('input/hidden', array(
		'name' => 'guid',
		'value' => elgg_extract('guid', $vars, $entity->guid),
	));
	echo elgg_view('input/hidden', array(
		'name' => 'container_guid',
		'value' => elgg_extract('container_guid', $vars, $container->guid),
	));
	echo elgg_view('input/hidden', array(
		'name' => 'scope',
		'value' => elgg_extract('scope', $vars, $entity->scope),
	));

	echo elgg_view('input/submit', array(
		'value' => elgg_echo('save')
	));
	?>
</div>