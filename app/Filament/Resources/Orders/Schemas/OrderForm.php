<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Nomor PO')
                    ->disabled()
                    ->dehydrated(),
                Select::make('pharmacy_id')
                    ->label('Apotek / Klinik')
                    ->relationship('pharmacy', 'name')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('total_amount')
                    ->label('Total Belanja')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('total_items')
                    ->label('Total Item')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                Select::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'confirmed' => 'Terkonfirmasi',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('confirmed')
                    ->required()
                    ->native(false)
                    ->helperText('💡 Terkonfirmasi: Pesanan sah masuk gathering (langsung dihitung ke omset, peringkat panggung, dan reward). Dibatalkan: Pesanan dikeluarkan dari omset dan reward apotek.'),
                Placeholder::make('reward_selections')
                    ->label('Hadiah Pilihan Tamu')
                    ->columnSpanFull()
                    ->content(function (?Order $record): HtmlString {
                        if (! $record || empty($record->reward_selections) || ! is_array($record->reward_selections)) {
                            return new HtmlString('<p class="text-xs text-gray-400 italic py-1">Tidak ada hadiah pilihan khusus pada pesanan ini.</p>');
                        }

                        $raw = $record->reward_selections;
                        $cleanItems = [];

                        $hasInterbatSlot = isset($raw['interbat_1']) || isset($raw['Interbat #1']) || isset($raw['Interbat (Hadiah 1)']);
                        $hasGrahaSlot = isset($raw['graha_neo_1']) || isset($raw['Neo Protifed #1']) || isset($raw['Neo Protifed (Hadiah 1)']);

                        foreach ($raw as $key => $val) {
                            if ($key === 'interbat' && $hasInterbatSlot) {
                                continue;
                            }
                            if ($key === 'graha_neo' && $hasGrahaSlot) {
                                continue;
                            }

                            $label = match ($key) {
                                'interbat' => 'Interbat Generic',
                                'interbat_1', 'Interbat #1' => 'Interbat Generic',
                                'interbat_2', 'Interbat #2' => 'Interbat Generic',
                                'interbat_3', 'Interbat #3' => 'Interbat Generic',
                                'graha_neo' => 'Neo Protifed Graha',
                                'graha_neo_1', 'Neo Protifed #1' => 'Neo Protifed Graha',
                                'graha_neo_2', 'Neo Protifed #2' => 'Neo Protifed Graha',
                                'graha_neo_3', 'Neo Protifed #3' => 'Neo Protifed Graha',
                                'exeltis_lanso' => 'Exeltis Lansoprazole',
                                'exeltis_sucra' => 'Exeltis Sucralfate',
                                default => ucwords(str_replace(['_', '-'], ' ', (string) $key)),
                            };

                            $badge = match ($key) {
                                'interbat_1', 'Interbat #1' => 'Pilihan #1',
                                'interbat_2', 'Interbat #2' => 'Pilihan #2',
                                'interbat_3', 'Interbat #3' => 'Pilihan #3',
                                'graha_neo_1', 'Neo Protifed #1' => 'Pilihan #1',
                                'graha_neo_2', 'Neo Protifed #2' => 'Pilihan #2',
                                'graha_neo_3', 'Neo Protifed #3' => 'Pilihan #3',
                                default => 'Hadiah Pilihan',
                            };

                            if (is_array($val)) {
                                if (isset($val['selected_option'])) {
                                    $cleanItems[] = ['program' => $label, 'badge' => $badge, 'reward' => (string) $val['selected_option']];
                                } else {
                                    foreach ($val as $idx => $v) {
                                        if (is_string($v) && ! empty($v)) {
                                            $cleanItems[] = ['program' => $label, 'badge' => 'Pilihan #'.($idx + 1), 'reward' => $v];
                                        }
                                    }
                                }
                            } elseif (is_string($val) && ! empty($val)) {
                                $cleanItems[] = ['program' => $label, 'badge' => $badge, 'reward' => $val];
                            }
                        }

                        if (empty($cleanItems)) {
                            return new HtmlString('<p style="font-size: 12px; color: #94a3b8; font-style: italic; padding: 4px 0;">Tidak ada hadiah pilihan khusus pada pesanan ini.</p>');
                        }

                        return new HtmlString(view('filament.resources.orders.partials.reward-selections-table', [
                            'items' => $cleanItems,
                        ])->render());
                    }),
                Placeholder::make('order_items')
                    ->label('Rincian Barang / Produk yang Dipesan')
                    ->columnSpanFull()
                    ->content(function (?Order $record): HtmlString {
                        if (! $record) {
                            return new HtmlString('');
                        }
                        $record->loadMissing('items.product.manufacturer');
                        $items = $record->items;
                        if ($items->isEmpty()) {
                            return new HtmlString('<p class="text-sm text-gray-500 italic py-2">Tidak ada produk tercatat dalam pesanan ini.</p>');
                        }

                        return new HtmlString(view('filament.resources.orders.partials.order-items-table', [
                            'items' => $items,
                            'order' => $record,
                        ])->render());
                    }),
                Textarea::make('notes')
                    ->label('Catatan Pesanan')
                    ->columnSpanFull(),
            ]);
    }
}
