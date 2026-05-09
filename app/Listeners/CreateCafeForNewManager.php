<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Models\Cafe;
use App\Models\Subscription;
use Illuminate\Auth\Events\Registered;

class CreateCafeForNewManager
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Hanya proses jika user yang register adalah manager
        if ($user->role !== UserRole::Manager->value) {
            return;
        }

        // Cari subscription Free
        $freeSubscription = Subscription::where('name', 'Free')
            ->orWhere('name', 'Free Plan')
            ->orWhere('price', 0)
            ->first();

        // Buat cafe baru untuk manager
        $cafe = Cafe::create([
            'name' => $user->name.' Cafe',
            'address' => '-',
            'phone' => $user->phone ?? '-',
            'email' => $user->email,
            'city' => '-',
            'province' => '-',
            'description' => 'Cafe milik '.$user->name,
            'owner_name' => $user->name,
            'logo_url' => null,
            'is_active' => true,
            'created_by' => $user->id,
            'subscription_id' => $freeSubscription?->id,
            'tax_percentage' => 10,
            'service_charge_percentage' => 5,
        ]);

        // Update user dengan cafe_id
        $user->update(['cafe_id' => $cafe->id]);
    }
}
