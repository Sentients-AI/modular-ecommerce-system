<?php

declare(strict_types=1);

namespace App\Domain\FeatureFlag\Actions;

use App\Domain\FeatureFlag\Models\FeatureFlag;
use App\Domain\User\Models\User;
use App\Shared\Domain\AuditLog;
use DomainException;

final class DisableFeatureFlagAction
{
    public function execute(FeatureFlag $flag, User $user): FeatureFlag
    {
        if (! $flag->is_enabled) {
            throw new DomainException("Feature flag '{$flag->key}' is already disabled.");
        }

        $flag->disable($user->id);

        AuditLog::log(
            action: 'feature_flag_disabled',
            targetType: 'feature_flag',
            targetId: $flag->id,
            payload: [
                'key' => $flag->key,
                'name' => $flag->name,
                'disabled_by' => $user->id,
            ],
        );

        return $flag->fresh();
    }
}
