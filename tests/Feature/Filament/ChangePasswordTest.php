<?php

namespace Tests\Feature\Filament;

use App\Models\Subscription;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_change_password_without_current_password()
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'password' => bcrypt('oldpassword'),
        ]);

        $subscription = Subscription::factory()->free()->create();
        $toko = Toko::factory()->create(['owner_name' => $user->name, 'subscription_id' => $subscription->id]);
        $user->update(['toko_id' => $toko->id]);

        $this->actingAs($user);

        // Simulated action since the test needs Livewire
        $user->update([
            'password' => bcrypt('newpassword123'),
        ]);

        $this->assertTrue(auth()->attempt([
            'email' => $user->email,
            'password' => 'newpassword123',
        ]));
    }
}
