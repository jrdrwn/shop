<?php

namespace App\Services;

use App\Models\Cafe;
use App\Models\Subscription;

class SubscriptionService
{
    /**
     * Resolve the active subscription for a cafe, or null when none assigned.
     */
    public function subscriptionFor(Cafe $cafe): ?Subscription
    {
        return $cafe->subscription;
    }

    // -------------------------------------------------------------------------
    // Numeric-limit checks
    // -------------------------------------------------------------------------

    /**
     * Check whether the cafe can create another product.
     */
    public function canCreateProduct(Cafe $cafe): bool
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_products');

        if ($max === null) {
            return true; // unlimited
        }

        return $cafe->products()->count() < $max;
    }

    /**
     * Remaining product slots (null = unlimited).
     */
    public function remainingProducts(Cafe $cafe): ?int
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_products');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $cafe->products()->count());
    }

    /**
     * Check whether the cafe can create another category.
     */
    public function canCreateCategory(Cafe $cafe): bool
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_categories');

        if ($max === null) {
            return true;
        }

        return $cafe->categories()->count() < $max;
    }

    /**
     * Remaining category slots (null = unlimited).
     */
    public function remainingCategories(Cafe $cafe): ?int
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_categories');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $cafe->categories()->count());
    }

    /**
     * Check whether the cafe can add another staff member (manager/cashier).
     */
    public function canAddStaff(Cafe $cafe): bool
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_staff');

        if ($max === null) {
            return true;
        }

        return $cafe->users()->whereIn('role', ['manager', 'cashier'])->count() < $max;
    }

    /**
     * How many staff slots remain (null = unlimited).
     */
    public function remainingStaff(Cafe $cafe): ?int
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_staff');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $cafe->users()->whereIn('role', ['manager', 'cashier'])->count());
    }

    /**
     * Check whether the cafe can add another payment method.
     */
    public function canAddPaymentMethod(Cafe $cafe): bool
    {
        $max = $this->subscriptionFor($cafe)?->getLimit('max_payment_methods');

        if ($max === null) {
            return true;
        }

        return $cafe->paymentMethods()->count() < $max;
    }

    // -------------------------------------------------------------------------
    // Boolean-feature checks
    // -------------------------------------------------------------------------

    /**
     * Check if a boolean feature is available for the cafe's subscription.
     */
    public function hasFeature(Cafe $cafe, string $feature): bool
    {
        return $this->subscriptionFor($cafe)?->hasFeature($feature) ?? false;
    }

    public function canExportReports(Cafe $cafe): bool
    {
        return $this->hasFeature($cafe, 'can_export_reports');
    }

    public function canUseInventory(Cafe $cafe): bool
    {
        return $this->hasFeature($cafe, 'can_use_inventory');
    }

    public function canUseVariants(Cafe $cafe): bool
    {
        return $this->hasFeature($cafe, 'can_use_variants');
    }

    public function canUseDiscounts(Cafe $cafe): bool
    {
        return $this->hasFeature($cafe, 'can_use_discounts');
    }

    // -------------------------------------------------------------------------
    // Payment Gateway Integration (Midtrans)
    // -------------------------------------------------------------------------

    /**
     * Initiate an upgrade payment for the cafe to the given subscription.
     *
     * @param  Cafe  $cafe  The cafe requesting the upgrade.
     * @param  Subscription  $subscription  Target subscription plan to upgrade to.
     * @return string Snap token for Midtrans payment.
     */
    public function initiateUpgrade(Cafe $cafe, Subscription $subscription): string
    {
        $midtrans = app(MidtransService::class);

        return $midtrans->createSnapToken($cafe, $subscription);
    }

    /**
     * Activate the subscription after successful payment.
     *
     * @param  Cafe  $cafe  The cafe to activate the subscription for.
     * @param  Subscription  $subscription  The subscription to activate.
     * @param  string  $transactionId  Payment gateway transaction ID.
     */
    public function activateSubscription(Cafe $cafe, Subscription $subscription, string $transactionId): void
    {
        $cafe->update([
            'subscription_id' => $subscription->id,
        ]);
    }
}
