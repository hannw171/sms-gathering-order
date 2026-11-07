<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_pharmacy_report')
                ->label('Rincian Belanja Apotek')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->slideOver()
                ->modalHeading(fn (): string => 'Rincian Belanja: '.($this->record->pharmacy?->name ?? '-'))
                ->modalDescription(fn (): string => ($this->record->pharmacy?->customer_code ?? '-').' • '.($this->record->pharmacy?->city ?? '-'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn () => view('filament.pages.partials.pharmacy-orders-detail', ['pharmacy' => $this->record->pharmacy])),
            DeleteAction::make(),
        ];
    }
}
