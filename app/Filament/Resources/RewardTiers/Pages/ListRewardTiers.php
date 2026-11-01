<?php

namespace App\Filament\Resources\RewardTiers\Pages;

use App\Filament\Resources\RewardTiers\RewardTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRewardTiers extends ListRecords
{
    protected static string $resource = RewardTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
