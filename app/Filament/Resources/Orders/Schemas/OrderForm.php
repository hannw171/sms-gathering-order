<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('pharmacy_id')
                    ->relationship('pharmacy', 'name')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('total_items')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('confirmed'),
                KeyValue::make('reward_selections')
                    ->label('Hadiah Pilihan Tamu')
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('ip_address'),
            ]);
    }
}
