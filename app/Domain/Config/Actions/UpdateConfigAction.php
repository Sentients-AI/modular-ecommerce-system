<?php

declare(strict_types=1);

namespace App\Domain\Config\Actions;

use App\Domain\Config\DTOs\UpdateConfigData;
use App\Domain\Config\Models\SystemConfig;
use App\Shared\Domain\AuditLog;
use DomainException;
use Illuminate\Support\Facades\Validator;

final class UpdateConfigAction
{
    public function execute(UpdateConfigData $data): SystemConfig
    {
        $config = SystemConfig::query()
            ->where('group', $data->group)
            ->where('key', $data->key)
            ->firstOrFail();

        $oldValue = $config->value;

        if ($config->validation_rules) {
            $validator = Validator::make(
                ['value' => $data->value],
                ['value' => $config->validation_rules]
            );

            if ($validator->fails()) {
                throw new DomainException(
                    "Invalid value for config '{$config->group}.{$config->key}': "
                    .implode(', ', $validator->errors()->all())
                );
            }
        }

        $config->update(['value' => (string) $data->value]);

        SystemConfig::clearCache($data->group, $data->key);

        AuditLog::log(
            action: 'config_updated',
            targetType: 'system_config',
            targetId: $config->id,
            payload: [
                'group' => $data->group,
                'key' => $data->key,
                'old_value' => $config->is_sensitive ? '[REDACTED]' : $oldValue,
                'new_value' => $config->is_sensitive ? '[REDACTED]' : $data->value,
            ],
        );

        return $config->fresh();
    }
}
