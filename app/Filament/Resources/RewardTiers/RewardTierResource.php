<?php

namespace App\Filament\Resources\RewardTiers;

use App\Filament\Resources\RewardTiers\Pages\CreateRewardTier;
use App\Filament\Resources\RewardTiers\Pages\EditRewardTier;
use App\Filament\Resources\RewardTiers\Pages\ListRewardTiers;
use App\Filament\Resources\RewardTiers\Schemas\RewardTierForm;
use App\Filament\Resources\RewardTiers\Tables\RewardTiersTable;
use App\Models\RewardTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RewardTierResource extends Resource
{
    protected static ?string $model = RewardTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RewardTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardTiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRewardTiers::route('/'),
            'create' => CreateRewardTier::route('/create'),
            'edit' => EditRewardTier::route('/{record}/edit'),
        ];
    }
}
