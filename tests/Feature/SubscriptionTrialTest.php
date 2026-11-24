<?php

use App\Filament\Resources\Tokos\Pages\ViewToko;
use App\Filament\Widgets\Subscription\SubscriptionUpgradeWidget;
use App\Http\Middleware\CheckSubscriptionExpiry;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Toko;
use App\Models\User;
use App\Services\SubscriptionService;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Livewire\Livewire;

test('super admin grants exactly 30 days of premium without a payment', function (): void {
    $this->travelTo(now()->setDate(2026, 1, 31)->startOfDay());
    $free = Subscription::factory()->free()->create();
    $premium = Subscription::factory()->premium()->create();
    $record = Toko::factory()->create(['subscription_id' => $free->id]);
    $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::actingAs($admin)->test(ViewToko::class, ['record' => $record->getRouteKey()])
        ->assertActionVisible('startTrial')
        ->callAction('startTrial')
        ->assertNotified('Trial Premium 30 hari aktif')
        ->assertSee('Trial Premium berakhir')
        ->assertActionHidden('startTrial');

    $record->refresh();
    expect($record->subscription_id)->toBe($premium->id)
        ->and($record->trial_ends_at->toDateTimeString())->toBe('2026-03-02 00:00:00')
        ->and(SubscriptionPayment::query()->count())->toBe(0);

    $this->travel(1)->day();
    expect(app(SubscriptionService::class)->startTrial($record))->toBeFalse()
        ->and($record->fresh()->trial_ends_at->toDateTimeString())->toBe('2026-03-02 00:00:00');

    $owner = User::factory()->create(['role' => 'owner', 'toko_id' => $record->id]);
    $this->actingAs($owner);
    expect((new SubscriptionUpgradeWidget)->getExpirySeconds())->toBe(29 * 86400);

    $widget = Livewire::test(SubscriptionUpgradeWidget::class)
        ->mountAction('selectPlan')
        ->instance();
    $options = $widget->getSchema($widget->getMountedActionSchemaName())->getFlatFields()['subscription_id']->getOptions();

    expect($options)->toHaveKey($premium->id)->not->toHaveKey($free->id);
});

test('tenant user cannot activate trial from the detail page', function (): void {
    $free = Subscription::factory()->free()->create();
    Subscription::factory()->premium()->create();
    $record = Toko::factory()->create(['subscription_id' => $free->id]);
    $owner = User::factory()->create(['role' => 'owner', 'toko_id' => $record->id, 'is_active' => true]);

    Filament::setCurrentPanel(Filament::getPanel('owner'));

    Livewire::actingAs($owner)->test(ViewToko::class, ['record' => $record->getRouteKey()])
        ->assertActionHidden('startTrial');

    expect($record->fresh()->trial_ends_at)->toBeNull();
});

test('an expired trial returns to Free on the next cashier request', function (): void {
    $this->travelTo(now()->startOfDay());
    $free = Subscription::factory()->free()->create();
    Subscription::factory()->premium()->create();
    $record = Toko::factory()->create(['subscription_id' => $free->id]);
    app(SubscriptionService::class)->startTrial($record);
    $cashier = User::factory()->create(['role' => 'kasir', 'toko_id' => $record->id]);

    $this->travel(30)->days();
    $this->actingAs($cashier);
    app(CheckSubscriptionExpiry::class)->handle(Request::create('/cashier'), fn () => response()->noContent());

    $record->refresh();
    expect($record->subscription_id)->toBe($free->id)
        ->and($record->trial_ends_at)->toBeNull();
});

test('paid activation ends the trial and a paid plan cannot start another trial', function (): void {
    $this->travelTo(now()->startOfDay());
    $free = Subscription::factory()->free()->create();
    $premium = Subscription::factory()->premium()->create();
    $record = Toko::factory()->create(['subscription_id' => $free->id]);
    $staleRecord = $record->fresh();
    $service = app(SubscriptionService::class);

    expect($service->startTrial($record))->toBeTrue();
    $service->activateSubscription($staleRecord, $premium, 'paid-transaction');

    $this->travel(30)->days();
    $service->expireTrial($record);
    expect($record->fresh()->trial_ends_at)->toBeNull()
        ->and($record->fresh()->subscription_id)->toBe($premium->id)
        ->and($service->startTrial($record->fresh()))->toBeFalse();
});
