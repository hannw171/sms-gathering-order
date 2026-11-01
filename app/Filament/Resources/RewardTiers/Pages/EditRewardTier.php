<?php

namespace App\Filament\Resources\RewardTiers\Pages;

use App\Filament\Resources\RewardTiers\RewardTierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRewardTier extends EditRecord
{
    protected static string $resource = RewardTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
