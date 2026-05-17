<?php

use App\Filament\Widgets\Subscription\SubscriptionStatusWidget;
use App\Filament\Widgets\Subscription\SubscriptionUpgradeWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('subscription widgets render Filament-prefixed classes for owner', function (): void {
    $owner = User::factory()->createOne([
        'role' => 'owner',
        'is_active' => true,
    ]);

    Livewire::actingAs($owner)
        ->test(SubscriptionStatusWidget::class)
        ->assertSee('fi-subscription-status', false)
        ->assertSee('Status Langganan', false);

    Livewire::actingAs($owner)
        ->test(SubscriptionUpgradeWidget::class)
        ->assertSee('fi-subscription-upgrade', false)
        ->assertSee('Pilih Paket Langganan', false)
        ->assertSee('fi-subscription-upgrade__plans', false);
});
