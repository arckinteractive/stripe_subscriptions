<?php
/**
 * Display a grid of membership options
 * @uses $vars['show_input']			Display radio input with each option
 * @uses $vars['role_names']			An array of role names to filter the plans
 * @uses $vars['value']					Pre-selected plan
 */
elgg_push_context('subscriptions-grid');

$user = elgg_extract('entity', $vars, elgg_get_logged_in_user_entity());

$show_input = elgg_extract('show_input', $vars, true);

$plan_type = SiteSubscriptionPlan::PLAN_TYPE_MEMBERSHIP;
$role_names = elgg_extract('role_names', $vars, ELGG_ENTITIES_ANY_VALUE);

$plans = stripe_subscriptions_get_plans($plan_type, $role_names);

$current_plan = stripe_subscriptions_get_membership_plan($user->guid);

$sorted_plans = array();

foreach ($plans as $plan) {

	if (!$plan instanceof SiteSubscriptionPlan) {
		continue;
	}

	$role = $plan->getRole();
	$cycle = $plan->getCycle()->getCycleName();
	$cycles[] = $cycle;
	$sorted_plans[$role][$cycle] = $plan;
}
?>

<table class="elgg-table-alt site-subscriptions-grid">
	<thead>
		<?php
		foreach ($sorted_plans as $role => $plans_by_cycle) {
			echo '<th>';
			echo '<label>';
			if (elgg_is_active_plugin('roles')) {
				$role_obj = roles_get_role_by_name($role);
				echo '<span>' . $role_obj->title . '</span>';
			} else {
				echo '<span>' . elgg_echo("subscriptions:roles:$role") . '</span>';
			}
			echo elgg_view('registration/role_help', array(
				'role' => $role
			));
			echo '</label>';
			echo '</th>';
		}
		?>
	</thead>

	<tbody>
		<?php
		$default_cycles = StripeBillingCycle::getCycles();
		foreach ($default_cycles as $cycle => $options) {
			if (!in_array($cycle, $cycles)) {
				continue;
			}
			echo '<tr>';
			foreach ($sorted_plans as $role => $plans_by_cycle) {

				$plan = elgg_extract($cycle, $plans_by_cycle, false);

				if ($plan) {
					$class = ($subscription) ? "subscriptions-plans-current" : "subscriptions-plans-available";
					echo "<td class=\"$class\">";
					$amount = $plan->getPricing()->getHumanAmount();
					$cycle = $plan->getCycle()->getLabel();
					echo '<label>';
					if ($show_input) {
						$attrs = elgg_format_attributes(array(
							'type' => 'radio',
							'name' => 'plan_guid',
							'value' => $plan->guid,
							'checked' => ($plan->guid == $current_plan->guid),
						));
						echo "<input $attrs />";
					}
					echo '<span class="subscriptions-plans-cycle"> ' . $cycle . '</span>';
					echo '</label>';
					echo ':&nbsp;';
					echo '<span class="subscriptions-plans-price">' . $amount . '</span>';
					echo '</td>';
					
				} else {
					echo '<td></td>';
				}
			}
			echo '</tr>';
		}
		?>
	</tbody>

</table>

<?php
elgg_pop_context();
