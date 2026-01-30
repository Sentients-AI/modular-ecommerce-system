<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('loads the control plane login page successfully', function () {
    $response = $this->get('/control-plane/login');

    $response->assertStatus(200);
    $response->assertSee('Control Plane');
    $response->assertSee('Email');
    $response->assertSee('Password');
});

it('resolves user model correctly from auth config', function () {
    $userModel = config('auth.providers.users.model');

    expect($userModel)->toBe(App\Domain\User\Models\User::class);
    expect(class_exists($userModel))->toBeTrue();
});
