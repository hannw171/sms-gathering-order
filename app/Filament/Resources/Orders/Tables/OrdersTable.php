<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('pharmacy.name')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('total_items')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reward_selections')
                    ->label('Hadiah Pilihan')
                    ->state(function (Order $record): string {
                        if (empty($record->reward_selections) || ! is_array($record->reward_selections)) {
                            return '-';
                        }
                        $items = [];
                        foreach ($record->reward_selections as $key => $val) {
                            if (is_array($val)) {
                                $items[] = ($val['selected_option'] ?? '').' ('.($val['manufacturer_slug'] ?? $key).')';
                            } else {
                                $items[] = (string) $val;
                            }
                        }

                        return implode(', ', $items) ?: '-';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state !== '-' ? 'info' : 'gray')
                    ->wrap(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
