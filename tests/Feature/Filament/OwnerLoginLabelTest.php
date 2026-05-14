<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Owner login panel shows owner label', function (): void {
    $response = $this->get('/owner/login');

    $response->assertOk();
    $response->assertSee('Login Owner', false);
    $response->assertSee('Owner', false);
});
