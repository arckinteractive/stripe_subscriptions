<?php

$english = array(
	'admin:stripe_subscriptions' => 'Site Subscriptions',
	'admin:stripe_subscriptions:settings' => 'Settings',
	'admin:stripe_subscriptions:create' => 'Add a plan',
	'admin:stripe_subscriptions:sync' => 'Synchronize',
	'admin:stripe_subscriptions:manage' => 'Manage plans',
	'admin:stripe_subscriptions:create' => 'Add a plan',
	'admin:stripe_subscriptions:edit' => 'Edit a plan',
	'admin:stripe_subscriptions:manage' => 'Manage plans',

	'subscriptions:settings:require_subscriptions' => 'Require membership subscriptions',
	'subscriptions:settings:require_subscriptions:help' => 'Require all users (except admins) to have an active or trial subscription. Users will not be allowed to navigate the site until they subscribe to a plan',
	'subscriptions:settings:require_cards' => 'Require credit card information',
	'subscriptions:settings:require_cards:help' => 'Require users to enter their credit card information before the subscription is activated. This would be applicable to free plans or plans with a trial period that can be activated without credit card information on file',

	'subscriptions' => 'Subscriptions',
	'subscriptions:plans' => 'Subscription Plans',
	'subscriptions:plans:add' => 'Add a subscription plan',
	'subscriptions:plans:edit' => 'Edit a subscription plan',
	'subscriptions:plans:title' => 'Plan Name',
	'subscriptions:plans:title:help' => 'Name of the subscription plan',
	'subscriptions:plans:description' => 'Description',
	'subscriptions:plans:description:help' => 'Description of the subscription plan',
	'subscriptions:plans:amount' => 'Amount',
	'subscriptions:plans:amount:help' => 'Amount to be charged in each billing cycle',
	'subscriptions:plans:currency' => 'Currency',
	'subscriptions:plans:currency:help' => 'Currency in which charges will be made',
	'subscriptions:plans:cycle' => 'Cycle',
	'subscriptions:plans:cycle:help' => 'Regularity at which the charges are made',
	'subscriptions:plans:access_id' => 'Access',
	'subscriptions:plans:actions' => 'Actions',
	'subscriptions:plans:edit:error_required_field_empty' => 'Please check that you have filled out all the required fields',
	'subscriptions:plans:edit:error_undefined_cycle' => 'Cycle %s is not defined',
	'subscriptions:plans:edit:error_generic' => 'Subscription plan could not be saved',
	'subscriptions:plans:edit:error_manage' => 'Plan %s could not be udpated',
	'subscriptions:plans:edit:success' => 'Subscription plan successfully saved',
	'subscriptions:plans:delete:success' => 'Subscription plan was successfully deleted',
	'subscriptions:plans:delete:error' => 'Subscription plan could not be deleted',
	'subscriptions:plans:trial_period_days' => 'Trial period',
	'subscriptions:plans:trial_period_days:help' => 'Duration of a trial period in days (0 for no trial)',
	'subscriptions:plans:plan_type' => 'Type of plan',
	'subscriptions:plans:plan_type:help' => 'Specify whether this plan provides membership access or an add on service',
	'subscriptions:plans:plan_type:membership' => 'Membership',
	'subscriptions:plans:plan_type:service' => 'Add-on Service',
	'subscriptions:plans:status' => 'Status',
	'subscriptions:plans:status:enabled' => 'Active',
	'subscriptions:plans:status:disabled' => 'Disabled',
	'subscriptions:plans:provider_id' => 'Unique ID',
	'subscriptions:plans:tier' => 'Tier',
	'subscriptions:plans:tier:help' => 'By adding a tier, you can group plans that offer the same benefits but differ by cycle',
	'subscriptions:plans:plan_id' => 'Unique Plan ID',
	'subscriptions:plans:plan_in:help' => 'Unique identifier of this plan (no spaces)',
	'subscriptions:plans:import:success' => 'Plan %s was successfully imported',
	'subscriptions:plans:import:error' => 'Plan %s could not be imported',
	'subscriptions:plans:export:success' => 'Plan %s was successfully exported',
	'subscriptions:plans:export:error' => 'Plan %s could not be exported',
	'subscriptions:plans:roles:select' => 'None',
	'subscriptions:plans:roles:require' => 'Required roles',
	'subscriptions:plans:roles:require:help' => 'Specify if any specific role is required for the plan to be available to the user',
	'subscriptions:plans:roles:provide' => 'Provided role',
	'subscriptions:plans:roles:provide:help' => 'Specify if a specific role should be assigned to the user when this plan is activated (applies to membership plans only)',
	'subscriptions:plans:list:empty' => 'No subscription plans have yet been created',
	'subscriptions:plans:output:price' => '%s %s',
	'subscriptions:plans:subscribe' => 'Subscribe',
	'subscriptions:plans:cancel' => 'Cancel subscription',
	'subscriptions:plans:enable' => 'Activate',
	'subscriptions:plans:disable' => 'Deactivate',
	'subscriptions:usersettings:active' => 'Active subscriptions',
	'subscriptions:usersettings:plans' => 'Membership plans',

	'subscriptions:membership:plan' => 'Membership plan',
	'subscriptions:membership:change_plan' => 'Change your membership plan',
	'subscriptions:membership:select_plan' => 'Select your plan',
	'subscriptions:membership:add_card' => 'Add a payment method',

	'subscriptions:membership:plan:success' => 'Your membership plan has been updated',
	'subscriptions:membership:plan:error' => 'Your membership plan could not be updated',
	'subscriptions:membership:plan:error:no_plan' => 'Plan does not exist',

	'subscriptions:subscriptions' => 'Active subscriptions',
	'subscriptions:cancel' => 'Cancel',
	'subscriptions:cancel:success' => 'Subscription has been canceled',
	'subscriptions:cancel:error' => 'Subscription could not be canceled',

	'subscriptions:invoices:upcoming' => 'Upcoming invoice',
	
	'subscriptions:notify:updated:title' => 'Your subscription to %s',
	'subscriptions:notify:updated:body' => 'Dear %s,

		Below are the details of your subscription:

		<blockquote>
			%s
		</blockquote>

		To manage your subscriptions, please visit:
		%s',

	'subscriptions:notify:deleted:title' => 'Your subscription to %s was canceled',
	'subscriptions:notify:deleted:body' => 'Dear %s,

		Below are the details of your canceled subscription:

		<blockquote>
			%s
		</blockquote>

		To manage your subscriptions, please visit:
		%s',

	'subscriptions:notify:trial_ending:title' => 'Your trial of %s is ending soon',
	'subscriptions:notify:trial_ending:body' => 'Dear %s,

		Below are the details of the trial subscription that will soon become active, and corresponding charges will made:

		<blockquote>
			%s
		</blockquote>

		%s

		To manage your cards, please visit:
		%s

		To manage your subscriptions, please visit:
		%s',

	'subscriptions:notify:trial_ending:card' => 'Charges will be made to your %s-%s',
	'subscriptions:notify:trial_ending:no_card' => '<b>You have not yet entered your billing information. Please do so as soon as possible, or your subscription will be cancelled.</b>',


);

add_translation('en', $english);
