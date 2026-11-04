<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersOverviewWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('📦 5 Transaksi PO Terkini')
            ->description('Aktivitas transaksi order masuk terkini dari apotek')
            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua Pesanan →')
                    ->url('/admin/orders')
                    ->color('primary')
                    ->button()
                    ->size('xs'),
            ])
            ->query(
                Order::query()
                    ->with('pharmacy')
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. PO')
                    ->badge()
                    ->color('gray')
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('pharmacy.name')
                    ->label('Apotek / Klinik')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Order $record): string => $record->pharmacy?->city ?? '-'),

                TextColumn::make('total_items')
                    ->label('Items')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('total_amount')
                    ->label('Total Transaksi')
                    ->money('IDR', locale: 'id')
                    ->alignEnd()
                    ->weight(FontWeight::Black)
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('H:i')
                    ->alignCenter()
                    ->fontFamily(FontFamily::Mono),
            ])
            ->emptyStateHeading('Belum Ada Transaksi PO')
            ->emptyStateDescription('Pesanan dari apotek akan tampil di sini secara real-time.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->paginated(false);
    }
}
