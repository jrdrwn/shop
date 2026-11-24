<?php

use App\Enums\SubscriptionPlan;
use App\Filament\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Widgets\Subscription\SubscriptionUpgradeWidget;
use App\Models\Subscription;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('subscription features normalize legacy text when rendering the upgrade widget', function (mixed $storedFeatures, ?array $expected): void {
    $subscription = Subscription::factory()->medium()->create();
    DB::table('subscriptions')->where('id', $subscription->id)->update(['features' => $storedFeatures]);

    expect($subscription->fresh()->features)->toBe($expected);

    $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
    $widget = Livewire::actingAs($owner)->test(SubscriptionUpgradeWidget::class)->assertSuccessful();
    foreach ($expected ?? SubscriptionPlan::Medium->marketingFeatures() as $feature) {
        $widget->assertSee($feature);
    }
})->with([
    'JSON list' => [json_encode(['Produk', 'Laporan']), ['Produk', 'Laporan']],
    'JSON string from textarea' => [json_encode(" Produk \r\n\r\n Laporan "), ['Produk', 'Laporan']],
    'double encoded list' => [json_encode(json_encode(['Produk', 'Laporan'])), ['Produk', 'Laporan']],
    'empty list' => ['[]', []],
    'null uses plan defaults' => [null, null],
    'empty string' => [json_encode(''), []],
]);

test('editing marketing features preserves one feature per line as a JSON array', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $subscription = Subscription::factory()->medium()->create(['features' => ['Produk', 'Laporan']]);

    Livewire::actingAs($admin)->test(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->assertSchemaStateSet(['features' => "Produk\nLaporan"])
        ->fillForm(['features' => " Produk baru \r\n\r\n Laporan baru "])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subscription->fresh()->features)->toBe(['Produk baru', 'Laporan baru'])
        ->and(json_decode(DB::table('subscriptions')->where('id', $subscription->id)->value('features'), true))
        ->toBe(['Produk baru', 'Laporan baru']);
});
