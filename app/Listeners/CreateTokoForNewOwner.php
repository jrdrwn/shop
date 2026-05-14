<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\Toko;
use Illuminate\Auth\Events\Registered;

class CreateTokoForNewOwner
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Hanya proses jika user yang register adalah Owner
        if ($user->role !== UserRole::Owner->value) {
            return;
        }

        // Cari subscription Free
        $freeSubscription = Subscription::where('name', 'Free')
            ->orWhere('name', 'Free Plan')
            ->orWhere('price', 0)
            ->first();

        // Buat toko baru untuk Owner
        $toko = Toko::create([
            'name' => $user->name.' Toko',
            'address' => '-',
            'phone' => $user->phone ?? '-',
            'email' => $user->email,
            'city' => '-',
            'province' => '-',
            'description' => 'Toko milik '.$user->name,
            'owner_name' => $user->name,
            'logo_url' => null,
            'is_active' => true,
            'created_by' => $user->id,
            'subscription_id' => $freeSubscription?->id,
            'tax_percentage' => 10,
            'service_charge_percentage' => 5,
        ]);

        // Update user dengan toko_id
        $user->update(['toko_id' => $toko->id]);
    }
}
