<?php

declare(strict_types=1);

use App\Domain\FeatureFlag\Actions\DisableFeatureFlagAction;
use App\Domain\FeatureFlag\Actions\EnableFeatureFlagAction;
use App\Domain\FeatureFlag\Models\FeatureFlag;
use App\Domain\User\Models\User;
use App\Shared\Domain\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('EnableFeatureFlagAction', function () {
    it('enables a disabled feature flag', function () {
        $user = User::factory()->create();
        $flag = FeatureFlag::create([
            'key' => 'test_feature',
            'name' => 'Test Feature',
            'is_enabled' => false,
        ]);

        $action = app(EnableFeatureFlagAction::class);
        $result = $action->execute($flag, $user);

        expect($result->is_enabled)->toBeTrue();
        expect($result->enabled_by)->toBe($user->id);
        expect($result->enabled_at)->not->toBeNull();
    });

    it('creates audit log when enabling', function () {
        $user = User::factory()->create();
        $flag = FeatureFlag::create([
            'key' => 'audit_test',
            'name' => 'Audit Test',
            'is_enabled' => false,
        ]);

        $this->actingAs($user);
        app(EnableFeatureFlagAction::class)->execute($flag, $user);

        $auditLog = AuditLog::query()
            ->where('action', 'feature_flag_enabled')
            ->where('target_id', $flag->id)
            ->first();

        expect($auditLog)->not->toBeNull();
        expect($auditLog->payload['key'])->toBe('audit_test');
    });

    it('throws exception if flag is already enabled', function () {
        $user = User::factory()->create();
        $flag = FeatureFlag::create([
            'key' => 'already_enabled',
            'name' => 'Already Enabled',
            'is_enabled' => true,
        ]);

        $action = app(EnableFeatureFlagAction::class);
        $action->execute($flag, $user);
    })->throws(DomainException::class);
});

describe('DisableFeatureFlagAction', function () {
    it('disables an enabled feature flag', function () {
        $user = User::factory()->create();
        $flag = FeatureFlag::create([
            'key' => 'to_disable',
            'name' => 'To Disable',
            'is_enabled' => true,
        ]);

        $action = app(DisableFeatureFlagAction::class);
        $result = $action->execute($flag, $user);

        expect($result->is_enabled)->toBeFalse();
        expect($result->disabled_by)->toBe($user->id);
        expect($result->disabled_at)->not->toBeNull();
    });

    it('throws exception if flag is already disabled', function () {
        $user = User::factory()->create();
        $flag = FeatureFlag::create([
            'key' => 'already_disabled',
            'name' => 'Already Disabled',
            'is_enabled' => false,
        ]);

        $action = app(DisableFeatureFlagAction::class);
        $action->execute($flag, $user);
    })->throws(DomainException::class);
});

describe('FeatureFlag model', function () {
    it('can check if enabled via static method', function () {
        FeatureFlag::create([
            'key' => 'static_check',
            'name' => 'Static Check',
            'is_enabled' => true,
        ]);

        expect(FeatureFlag::isEnabled('static_check'))->toBeTrue();
        expect(FeatureFlag::isEnabled('nonexistent'))->toBeFalse();
    });
});
