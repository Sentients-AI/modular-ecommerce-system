<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Role\Models\Role;
use App\Domain\User\Models\User;
use App\Filament\Resources\Users\UserResource;
use App\Shared\Domain\AuditLog;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_role')
                ->label('Assign Role')
                ->icon('heroicon-o-user-group')
                ->color('warning')
                ->form([
                    Select::make('role_id')
                        ->label('Role')
                        ->options(fn () => Role::query()->pluck('name', 'id'))
                        ->required()
                        ->default(fn (User $record): ?int => $record->role_id),
                ])
                ->requiresConfirmation()
                ->modalHeading('Assign Role')
                ->modalDescription('This action will change the user\'s role and permissions.')
                ->visible(fn (User $record): bool => auth()->user()->can('assignRole', $record))
                ->action(function (User $record, array $data): void {
                    try {
                        $oldRole = $record->role?->name;
                        $newRole = Role::find($data['role_id']);

                        $record->update(['role_id' => $data['role_id']]);

                        AuditLog::log(
                            action: 'role_assigned',
                            targetType: 'user',
                            targetId: $record->id,
                            payload: [
                                'user_name' => $record->name,
                                'old_role' => $oldRole,
                                'new_role' => $newRole?->name,
                            ],
                        );

                        Notification::make()
                            ->title('Role Assigned')
                            ->body("Role for {$record->name} has been updated to {$newRole?->name}.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['role_id']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Role Assignment Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
