<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionPlan;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && in_array($user->role, [UserRole::Owner->value, 'owner', 'gudang'], true) && $user->toko_id) {
            $toko = $user->toko;
            if ($toko && $toko->subscription_id) {
                $subscription = $toko->subscription;
                if ($subscription && $subscription->plan !== SubscriptionPlan::Free) {
                    // Find latest successful payment
                    $latestPayment = SubscriptionPayment::where('toko_id', $toko->id)
                        ->where('status', 'success')
                        ->latest()
                        ->first();

                    if ($latestPayment && $latestPayment->settlement_time) {
                        $expiresAt = $latestPayment->settlement_time->addMonths($subscription->duration_months);
                        if (now()->greaterThan($expiresAt)) {
                            // Expired!
                            // Downgrade to Free plan!
                            $freePlan = Subscription::where('plan', SubscriptionPlan::Free)->first();
                            if ($freePlan) {
                                $toko->update(['subscription_id' => $freePlan->id]);

                                // Enforce limits!
                                app(SubscriptionService::class)->enforceLimits($toko);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
