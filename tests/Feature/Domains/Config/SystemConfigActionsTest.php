<?php

declare(strict_types=1);

use App\Domain\Config\Actions\UpdateConfigAction;
use App\Domain\Config\DTOs\UpdateConfigData;
use App\Domain\Config\Models\SystemConfig;
use App\Domain\User\Models\User;
use App\Shared\Domain\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('UpdateConfigAction', function () {
    it('updates a config value', function () {
        $config = SystemConfig::create([
            'group' => 'test',
            'key' => 'setting',
            'name' => 'Test Setting',
            'type' => 'string',
            'value' => 'old_value',
        ]);

        $action = app(UpdateConfigAction::class);
        $result = $action->execute(new UpdateConfigData(
            group: 'test',
            key: 'setting',
            value: 'new_value',
        ));

        expect($result->value)->toBe('new_value');
    });

    it('validates against rules when provided', function () {
        $config = SystemConfig::create([
            'group' => 'test',
            'key' => 'number',
            'name' => 'Test Number',
            'type' => 'integer',
            'value' => '50',
            'validation_rules' => ['integer', 'min:0', 'max:100'],
        ]);

        $action = app(UpdateConfigAction::class);
        $action->execute(new UpdateConfigData(
            group: 'test',
            key: 'number',
            value: 200,
        ));
    })->throws(DomainException::class);

    it('creates audit log on update', function () {
        $user = User::factory()->create();
        $config = SystemConfig::create([
            'group' => 'audit',
            'key' => 'test',
            'name' => 'Audit Test',
            'type' => 'string',
            'value' => 'before',
        ]);

        $this->actingAs($user);
        app(UpdateConfigAction::class)->execute(new UpdateConfigData(
            group: 'audit',
            key: 'test',
            value: 'after',
            updatedBy: $user->id,
        ));

        $auditLog = AuditLog::query()
            ->where('action', 'config_updated')
            ->where('target_id', $config->id)
            ->first();

        expect($auditLog)->not->toBeNull();
        expect($auditLog->payload['old_value'])->toBe('before');
        expect($auditLog->payload['new_value'])->toBe('after');
    });

    it('redacts sensitive values in audit log', function () {
        $user = User::factory()->create();
        $config = SystemConfig::create([
            'group' => 'sensitive',
            'key' => 'secret',
            'name' => 'Secret Key',
            'type' => 'string',
            'value' => 'old_secret',
            'is_sensitive' => true,
        ]);

        $this->actingAs($user);
        app(UpdateConfigAction::class)->execute(new UpdateConfigData(
            group: 'sensitive',
            key: 'secret',
            value: 'new_secret',
        ));

        $auditLog = AuditLog::query()
            ->where('action', 'config_updated')
            ->first();

        expect($auditLog->payload['old_value'])->toBe('[REDACTED]');
        expect($auditLog->payload['new_value'])->toBe('[REDACTED]');
    });
});

describe('SystemConfig model', function () {
    it('can get typed value', function () {
        SystemConfig::create([
            'group' => 'typed',
            'key' => 'integer',
            'name' => 'Integer',
            'type' => 'integer',
            'value' => '42',
        ]);

        $result = SystemConfig::getValue('typed', 'integer');

        expect($result)->toBe(42);
        expect($result)->toBeInt();
    });

    it('can get boolean value', function () {
        SystemConfig::create([
            'group' => 'typed',
            'key' => 'bool',
            'name' => 'Boolean',
            'type' => 'boolean',
            'value' => 'true',
        ]);

        $result = SystemConfig::getValue('typed', 'bool');

        expect($result)->toBeTrue();
        expect($result)->toBeBool();
    });

    it('returns default when config not found', function () {
        $result = SystemConfig::getValue('nonexistent', 'key', 'default_value');

        expect($result)->toBe('default_value');
    });
});
