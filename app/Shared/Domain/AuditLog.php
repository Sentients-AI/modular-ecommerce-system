<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuditLog extends Model
{
    protected $fillable = [
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'target_type',
        'target_id',
        'payload',
        'result',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $payload = null,
        string $result = 'success',
    ): self {
        $user = auth()->user();

        return self::create([
            'actor_type' => $user ? 'user' : 'system',
            'actor_id' => $user?->id,
            'actor_name' => $user?->name ?? 'System',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'payload' => $payload,
            'result' => $result,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
