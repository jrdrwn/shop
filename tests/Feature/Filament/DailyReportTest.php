<?php

use App\Filament\Resources\DailyReports\DailyReportResource;
use App\Models\Toko;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('Owner can see daily reports for their store', function (): void {
    if (! Schema::hasTable('daily_reports')) {
        Schema::create('daily_reports', function ($table) {
            $table->id();
            $table->unsignedBigInteger('toko_id');
            $table->date('report_date');
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 14, 2)->default(0);
            $table->decimal('total_discount', 14, 2)->default(0);
            $table->decimal('total_tax', 14, 2)->default(0);
            $table->decimal('total_cash', 14, 2)->default(0);
            $table->decimal('total_debit', 14, 2)->default(0);
            $table->decimal('total_qris', 14, 2)->default(0);
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('closing_balance', 14, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    $Owner = User::factory()->createOne([
        'role' => 'owner',
        'is_active' => true,
    ]);

    $toko = Toko::query()->create([
        'name' => 'Toko Test',
        'created_by' => $Owner->id,
    ]);

    $Owner->update(['toko_id' => $toko->id]);

    $report = DailyReport::create([
        'toko_id' => $toko->id,
        'report_date' => now()->toDateString(),
        'total_transactions' => 10,
        'total_sales' => 100000,
        'total_discount' => 0,
        'total_tax' => 10000,
        'total_cash' => 50000,
        'total_debit' => 50000,
        'total_qris' => 0,
        'opening_balance' => 0,
        'closing_balance' => 100000,
        'created_by' => $Owner->id,
    ]);

    actingAs($Owner);

    // Check if the resource query scopes correctly
    $query = DailyReportResource::getEloquentQuery();

    expect($query->count())->toBe(1)
        ->and($query->first()->id)->toBe($report->id);
});
