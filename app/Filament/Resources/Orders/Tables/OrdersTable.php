<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
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
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pharmacy.name')
                    ->label('Apotek / Klinik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Belanja')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('total_items')
                    ->label('Total Item')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reward_selections')
                    ->label('Hadiah Pilihan')
                    ->state(function (Order $record): array {
                        if (empty($record->reward_selections) || ! is_array($record->reward_selections)) {
                            return [];
                        }
                        $raw = $record->reward_selections;
                        $items = [];
                        $hasInterbatSlot = isset($raw['interbat_1']) || isset($raw['Interbat #1']);
                        $hasGrahaSlot = isset($raw['graha_neo_1']) || isset($raw['Neo Protifed #1']);

                        foreach ($raw as $key => $val) {
                            if ($key === 'interbat' && $hasInterbatSlot) {
                                continue;
                            }
                            if ($key === 'graha_neo' && $hasGrahaSlot) {
                                continue;
                            }
                            if (is_array($val)) {
                                if (isset($val['selected_option'])) {
                                    $items[] = (string) $val['selected_option'];
                                } else {
                                    foreach ($val as $v) {
                                        if (is_string($v) && ! empty($v)) {
                                            $items[] = $v;
                                        }
                                    }
                                }
                            } elseif (is_string($val) && ! empty($val)) {
                                $items[] = $val;
                            }
                        }

                        return $items;
                    })
                    ->badge()
                    ->color('info')
                    ->listWithLineBreaks()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Terkonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu Order')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_pharmacy')
                    ->label('Rincian Belanja')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->slideOver()
                    ->modalHeading(fn (Order $record): string => 'Rincian Belanja: '.($record->pharmacy?->name ?? '-'))
                    ->modalDescription(fn (Order $record): string => ($record->pharmacy?->customer_code ?? '-').' • '.($record->pharmacy?->city ?? '-'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Order $record) => view('filament.pages.partials.pharmacy-orders-detail', ['pharmacy' => $record->pharmacy])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
