<?php

use Illuminate\Support\Facades\Blade;

test('role links menyembunyikan tombol admin saat mode debug off', function () {
    app()->detectEnvironment(fn () => 'production');

    $adminHtml = Blade::render('<x-admin-role-links />');
    expect($adminHtml)->toContain('fi-role-links--compact');
    expect($adminHtml)->toContain('fi-role-link');

    $cashierHtml = Blade::render('<x-cashier-role-links />');
    expect($cashierHtml)->not->toContain('/admin/login');
    expect($cashierHtml)->toContain('fi-role-links');
    expect($cashierHtml)->toContain('fi-role-link__name');

    $ownerHtml = Blade::render('<x-owner-role-links />');
    expect($ownerHtml)->not->toContain('/admin/login');
    expect($ownerHtml)->toContain('fi-role-links');
    expect($ownerHtml)->toContain('fi-role-link__description');

    $warehouseHtml = Blade::render('<x-warehouse-role-links />');
    expect($warehouseHtml)->not->toContain('/admin/login');
    expect($warehouseHtml)->toContain('fi-role-links');
    expect($warehouseHtml)->toContain('fi-role-link__chevron');
});

test('role links menampilkan tombol admin saat mode debug on', function () {
    app()->detectEnvironment(fn () => 'local');

    $adminHtml = Blade::render('<x-admin-role-links />');
    expect($adminHtml)->toContain('fi-role-link');
    expect($adminHtml)->toContain('fi-role-links__heading');

    $cashierHtml = Blade::render('<x-cashier-role-links />');
    expect($cashierHtml)->toContain('/admin/login');
    expect($cashierHtml)->toContain('fi-role-link');

    $ownerHtml = Blade::render('<x-owner-role-links />');
    expect($ownerHtml)->toContain('/admin/login');
    expect($ownerHtml)->toContain('fi-role-link');

    $warehouseHtml = Blade::render('<x-warehouse-role-links />');
    expect($warehouseHtml)->toContain('/admin/login');
    expect($warehouseHtml)->toContain('fi-role-link');
});
