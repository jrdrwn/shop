<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Cashier login panel shows cashier label', function (): void {
    $response = $this->get('/cashier/login');

    $response->assertOk();
    $response->assertSee('Login Kasir', false);
    $response->assertSee('Kasir', false);
});
