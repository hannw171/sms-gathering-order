<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PharmacyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('customer_code')
                    ->required(),
                TextInput::make('owner_name'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('city'),
                TextInput::make('sales_pic'),
                Toggle::make('is_checked_in')
                    ->required(),
                DateTimePicker::make('checked_in_at'),
            ]);
    }
}
