<?php

class SiteSubscriptionPlan extends ElggObject {

	const SUBTYPE = 'site_subscription_plan';
	const PLAN_TYPE_MEMBERSHIP = 'membership';
	const PLAN_TYPE_SERVICE = 'service';
	const REL_MEMBERSHIP = 'has_membership_plan';
	const REL_SERVICE = 'has_service_plan';

	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
		$this->attributes['access_id'] = ACCESS_PUBLIC;
	}

	/**
	 * Subscribe the user to a plan
	 * @param integer $user_guid
	 * @param string $subscription_id	ID of the subscription assigned by Stripe
	 * @return boolean
	 */
	public function subscribe($user_guid = 0) {
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		$user = get_entity($user_guid);

		if ($this->isMembershipPlan()) {
			$relationship = self::REL_MEMBERSHIP;

			// Allow only one membership plan subscription
			$old_plan = stripe_subscriptions_get_membership_plan($user->guid);
			if ($old_plan instanceof SiteSubscriptionPlan) {
				remove_entity_relationship($user->guid, $relationship, $old_plan->guid);
			}
		} else {
			$relationship = self::REL_SERVICE;
		}

		// Add a relationship between the user and this plan
		if (add_entity_relationship($user->guid, $relationship, $this->guid)) {
			// Set the role if rules apply
			if ($relationship == self::REL_MEMBERSHIP && elgg_is_active_plugin('roles')) {
				$user = get_entity($user->guid);
				$rolename = $this->getRole();
				$role = roles_get_role_by_name($rolename);
				roles_set_role($role, $user);
			}
			return true;
		}

		return false;
	}

	/**
	 * Unsubscribe the user from a plan
	 *
	 * @param integer $user_guid	GUID of the user
	 * @return boolean
	 */
	public function unsubscribe($user_guid = 0) {
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if (!$this->isSubscribed($user_guid)) {
			$this->setMessage("You are not subscribed to this plan");
			return true;
		}

		$user = get_entity($user);

		if ($this->isMembershipPlan()) {
			$relationship = self::REL_MEMBERSHIP;
		} else {
			$relationship = self::REL_SERVICE;
		}

		// Remove relationships and roles
		if (remove_entity_relationship($user->guid, $relationship, $this->guid)) {
			if ($relationship == self::REL_MEMBERSHIP) {
				if (!stripe_subscriptions_get_membership_plan($user->guid)) {
					if (elgg_is_active_plugin('roles')) {
						$rolename = $this->getRole();
						if (roles_get_role($user) == $rolename) {
							roles_set_role(roles_get_role_by_name(DEFAULT_ROLE), $user);
						}
					}
				}
			}
			return true;
		}

		return false;
	}

	/**
	 * Check if the user is subscribed to this plan
	 * @param integer $user_guid
	 * @return boolean
	 */
	public function isSubscribed($user_guid = 0) {
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if ($this->isMembershipPlan()) {
			$relationship = self::REL_MEMBERSHIP;
		} else {
			$relationship = self::REL_SERVICE;
		}

		return (check_entity_relationship($user_guid, $relationship, $this->guid));
	}

	public function setPlanId($uid = '') {
		$this->plan_id = $uid;
	}

	public function getPlanId() {
		return $this->plan_id;
	}

	public function setPlanType($plan_type = '') {
		$this->plan_type = $plan_type;
	}

	public function getPlanType() {
		return $this->plan_type;
	}

	public function setRole($role = '') {
		$this->role = $role;
	}

	public function getRole() {
		return $this->role;
	}

	public function isMembershipPlan() {
		return $this->getPlanType() == self::PLAN_TYPE_MEMBERSHIP;
	}

	public function isServicePlan() {
		return $this->getPlanType() == self::PLAN_TYPE_SERVICE;
	}

	/**
	 * Get subscriptions based on this plan
	 * @param integers $user_guid			Filter by use
	 * @return ElggBatch
	 */
	public function getSusbscriptions($user_guid = ELGG_ENTITIES_ANY_VALUE) {
		return get_plan_subscriptions($this->guid, $user_guid);
	}

	/**
	 * Set trial period for the plan
	 * @param integer $days
	 */
	public function setTrialPeriodDays($days = 0) {
		$this->trial_period_days = (int) $days;
	}

	/**
	 * Get trial period
	 * @return integer
	 */
	public function getTrialPeriodDays() {
		return (int) $this->trial_period_days;
	}

	/**
	 * Handle pricing
	 * @return Handler_Pricing
	 */
	public function getPricing() {
		return new StripePricing($this->getAmount(), 0, 0, $this->getCurrency());
	}

	/**
	 * Set amount charged in each cycle
	 * @param float $amount	Amount charged in a cycle
	 * @return void
	 */
	public function setAmount($amount = 0) {
		$amount = new StripePricing($amount);
		if ($amount->isValid()) {
			$this->amount = $amount->getPrice();
		}
	}

	/**
	 * Get price or discount value
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * Set product currency
	 * @param string $currency_code	3-letter ISO currency code
	 * @return void
	 */
	public function setCurrency($currency_code = '') {
		$currency = new StripeCurrencies($currency_code);
		$this->currency = $currency->getCurrencyCode();
	}

	/**
	 * Get product currency
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * Set the billing cycle
	 * @param string $cycle
	 */
	public function setCycle($cycle = '', $interval = '', $interval_count = '') {
		if (!$cycle instanceof StripeBillingCycle) {
			$cycle = new StripeBillingCycle($cycle, $interval, $interval_count);
		}
		$this->billing_cycle = $cycle->getCycleName();
	}

	/**
	 * Get the cycle object
	 * @return StripeBillingCycle
	 */
	public function getCycle() {
		return new StripeBillingCycle($this->billing_cycle);
	}

	/**
	 * Get the interval derived from cycle
	 * @return string
	 */
	public function getInterval() {
		return $this->getCycle()->getInterval();
	}

	/**
	 * Get the interval count derived from cycle
	 * @return integer
	 */
	public function getIntervalCount() {
		return $this->getCycle()->getIntervalCount();
	}

	/**
	 * Get an array suitable for stripe API
	 * @return array
	 */
	public function exportAsStripeArray() {
		$export = array(
			'id' => $this->getPlanId(),
			'amount' => $this->getPricing()->getStripePrice(),
			'currency' => $this->getPricing()->getCurrency(),
			'interval' => $this->getInterval(),
			'interval_count' => $this->getIntervalCount(),
			'name' => $this->title,
			'trial_period_days' => $this->getTrialPeriodDays(),
			'metadata' => array(
				'guid' => $this->guid,
				'plan_type' => $this->getPlanType(),
				'role' => $this->getRole(),
				'active' => ($this->isEnabled()) ? 'yes' : 'no',
			)
		);

		return array_filter($export);
	}

}
