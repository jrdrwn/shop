<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Toko;

class SubscriptionService
{
    /**
     * Resolve the active subscription for a toko, or null when none assigned.
     */
    public function subscriptionFor(Toko $toko): ?Subscription
    {
        return $toko->subscription;
    }

    // -------------------------------------------------------------------------
    // Numeric-limit checks
    // -------------------------------------------------------------------------

    /**
     * Check whether the toko can create another product.
     */
    public function canCreateProduct(Toko $toko): bool
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_products');

        if ($max === null) {
            return true; // unlimited
        }

        return $toko->products()->count() < $max;
    }

    /**
     * Remaining product slots (null = unlimited).
     */
    public function remainingProducts(Toko $toko): ?int
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_products');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $toko->products()->count());
    }

    /**
     * Check whether the toko can create another category.
     */
    public function canCreateCategory(Toko $toko): bool
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_categories');

        if ($max === null) {
            return true;
        }

        return $toko->categories()->count() < $max;
    }

    /**
     * Remaining category slots (null = unlimited).
     */
    public function remainingCategories(Toko $toko): ?int
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_categories');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $toko->categories()->count());
    }

    /**
     * Check whether the toko can add another staff member (Owner/cashier).
     */
    public function canAddStaff(Toko $toko): bool
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_staff');

        if ($max === null) {
            return true;
        }

        return $toko->users()->whereIn('role', ['owner', 'kasir', 'gudang'])->count() < $max;
    }

    /**
     * How many staff slots remain (null = unlimited).
     */
    public function remainingStaff(Toko $toko): ?int
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_staff');

        if ($max === null) {
            return null;
        }

        return max(0, $max - $toko->users()->whereIn('role', ['owner', 'kasir', 'gudang'])->count());
    }

    /**
     * Check whether the toko can add another payment method.
     */
    public function canAddPaymentMethod(Toko $toko): bool
    {
        $max = $this->subscriptionFor($toko)?->getLimit('max_payment_methods');

        if ($max === null) {
            return true;
        }

        return $toko->paymentMethods()->count() < $max;
    }

    // -------------------------------------------------------------------------
    // Boolean-feature checks
    // -------------------------------------------------------------------------

    /**
     * Check if a boolean feature is available for the toko's subscription.
     */
    public function hasFeature(Toko $toko, string $feature): bool
    {
        return $this->subscriptionFor($toko)?->hasFeature($feature) ?? false;
    }

    public function canExportReports(Toko $toko): bool
    {
        return $this->hasFeature($toko, 'can_export_reports');
    }

    public function canUseInventory(Toko $toko): bool
    {
        return $this->hasFeature($toko, 'can_use_inventory');
    }

    public function canUseVariants(Toko $toko): bool
    {
        return $this->hasFeature($toko, 'can_use_variants');
    }

    public function canUseDiscounts(Toko $toko): bool
    {
        return $this->hasFeature($toko, 'can_use_discounts');
    }

    // -------------------------------------------------------------------------
    // Payment Gateway Integration (Midtrans)
    // -------------------------------------------------------------------------

    /**
     * Initiate an upgrade payment for the toko to the given subscription.
     *
     * @param  Toko  $toko  The toko requesting the upgrade.
     * @param  Subscription  $subscription  Target subscription plan to upgrade to.
     * @return string Snap token for Midtrans payment.
     */
    public function initiateUpgrade(Toko $toko, Subscription $subscription): string
    {
        $midtrans = app(MidtransService::class);

        return $midtrans->createSnapToken($toko, $subscription);
    }

    /**
     * Activate the subscription after successful payment.
     *
     * @param  Toko  $toko  The toko to activate the subscription for.
     * @param  Subscription  $subscription  The subscription to activate.
     * @param  string  $transactionId  Payment gateway transaction ID.
     */
    public function activateSubscription(Toko $toko, Subscription $subscription, string $transactionId): void
    {
        $toko->update([
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Enforce subscription limits by deactivating excess items.
     */
    public function enforceLimits(Toko $toko): void
    {
        $subscription = $this->subscriptionFor($toko);
        if (! $subscription) {
            return;
        }

        // 1. Payment Methods
        $maxPaymentMethods = $subscription->getLimit('max_payment_methods');
        if ($maxPaymentMethods !== null) {
            $paymentMethods = $toko->paymentMethods()->orderBy('id', 'asc')->get();
            if ($paymentMethods->count() > $maxPaymentMethods) {
                foreach ($paymentMethods->skip($maxPaymentMethods) as $pm) {
                    $pm->update(['is_active' => false]);
                }
            }
        }

        // 2. Categories
        $maxCategories = $subscription->getLimit('max_categories');
        if ($maxCategories !== null) {
            $categories = $toko->categories()->orderBy('id', 'asc')->get();
            if ($categories->count() > $maxCategories) {
                foreach ($categories->skip($maxCategories) as $cat) {
                    $cat->update(['is_active' => false]);
                }
            }
        }

        // 3. Products
        $maxProducts = $subscription->getLimit('max_products');
        if ($maxProducts !== null) {
            $products = $toko->products()->orderBy('id', 'asc')->get();
            if ($products->count() > $maxProducts) {
                foreach ($products->skip($maxProducts) as $prod) {
                    $prod->update(['is_active' => false]);
                }
            }
        }
    }
}
