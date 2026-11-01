<?php

namespace App\Filament\Resources\RewardTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RewardTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tier_level')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('name')
                    ->required(),
                TextInput::make('min_spending')
                    ->required()
                    ->numeric(),
                TextInput::make('reward_description')
                    ->required(),
                TextInput::make('badge_icon')
                    ->required()
                    ->default('🏆'),
            ]);
    }
}
