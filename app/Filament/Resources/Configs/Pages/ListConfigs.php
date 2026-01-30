<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configs\Pages;

use App\Filament\Resources\Configs\ConfigResource;
use Filament\Resources\Pages\ListRecords;

final class ListConfigs extends ListRecords
{
    protected static string $resource = ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
