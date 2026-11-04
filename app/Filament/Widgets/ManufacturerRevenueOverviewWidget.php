<?php

namespace App\Filament\Widgets;

use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ManufacturerRevenueOverviewWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('🏢 Omset 9 Pabrikan Farmasi')
            ->description('Perolehan omset dari masing-masing prinsipal')
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Rekap Omset Lengkap →')
                    ->url('/admin/revenue-report')
                    ->color('primary')
                    ->button()
                    ->size('xs'),
            ])
            ->query(Manufacturer::query()->withCount('products'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pabrikan')
                    ->weight(FontWeight::Bold),

                TextColumn::make('total_units')
                    ->label('Volume')
                    ->state(function (Manufacturer $record): int {
                        return (int) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('quantity');
                    })
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('total_revenue')
                    ->label('Total Omset')
                    ->money('IDR', locale: 'id')
                    ->state(function (Manufacturer $record): float {
                        return (float) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('subtotal');
                    })
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('contribution')
                    ->label('Kontribusi')
                    ->state(function (Manufacturer $record): string {
                        $totalGMV = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
                        if ($totalGMV <= 0) {
                            return '0.0%';
                        }
                        $rev = (float) OrderItem::whereHas('product', fn ($q) => $q->where('manufacturer_id', $record->id))
                            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                            ->sum('subtotal');

                        return number_format(($rev / $totalGMV) * 100, 1, ',', '.').'%';
                    })
                    ->badge()
                    ->color('success')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
