<?php

namespace App\Filament\Resources\Manufacturers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ManufacturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('logo_url')
                    ->url(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Master Aktif')
                    ->default(true)
                    ->required(),
                Toggle::make('is_ordering_active')
                    ->label('Sesi Pemesanan (PO) Aktif')
                    ->helperText('Buka atau tutup sesi pemesanan produk untuk pabrikan ini.')
                    ->default(false),
                DateTimePicker::make('session_end_at')
                    ->label('Batas Waktu Sesi (Hitung Mundur)')
                    ->helperText('Kosongkan jika aktif tanpa batas waktu.')
                    ->seconds(false),
            ]);
    }
}
