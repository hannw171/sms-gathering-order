<?php

namespace App\Filament\Pages;

use App\Models\EventSetting;
use App\Models\Manufacturer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageEventSettings extends Page
{
    use InteractsWithFormActions;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.manage-event-settings';

    public function getTitle(): string|Htmlable
    {
        return 'Pengaturan Acara & Sesi PO';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Acara';
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $eventSetting = EventSetting::current();
        $this->form->fill($eventSetting->attributesToArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Acara & Lokasi')
                    ->description('Pengaturan nama acara, lokasi venue, dan jadwal meja registrasi untuk tamu apotek.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextInput::make('event_name')
                            ->label('Nama Acara')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('venue_name')
                            ->label('Lokasi Acara / Venue')
                            ->placeholder('Contoh: Ballroom Grand Hotel • Meja Undangan')
                            ->helperText('Tampil pada badge lokasi di header halaman masuk tamu.')
                            ->maxLength(255),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('registration_schedule')
                                    ->label('Jadwal Meja Registrasi')
                                    ->placeholder('Contoh: Meja Registrasi Buka: 08:30 – 13:00 WIB')
                                    ->helperText('Tampil pada banner kuning di halaman masuk tamu.'),
                                TextInput::make('registration_help_note')
                                    ->label('Catatan Bantuan Registrasi')
                                    ->placeholder('Contoh: PIC Meja Bantuan siap melayani pencarian manual ID di Area Foyer Ballroom.'),
                            ]),
                    ]),

                Section::make('Jadwal & Kontrol Sesi Pemesanan (Sesi PO)')
                    ->description('Atur waktu buka/tutup sesi pemesanan otomatis atau gunakan saklar kontrol manual.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Toggle::make('is_ordering_active')
                            ->label('Buka Sesi Pemesanan Sekarang (Saklar Manual)')
                            ->helperText('Jika dinonaktifkan, tamu tidak dapat melakukan checkout pesanan baru.')
                            ->onColor('success')
                            ->offColor('danger'),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('session_start_at')
                                    ->label('Waktu Mulai Sesi PO (Otomatis)')
                                    ->seconds(false)
                                    ->helperText('Kosongkan jika ingin membuka sesi PO secara manual.'),
                                DateTimePicker::make('session_end_at')
                                    ->label('Waktu Selesai Sesi PO (Countdown Selesai)')
                                    ->seconds(false)
                                    ->helperText('Waktu hitung mundur di layar panggung dan aplikasi tamu.'),
                            ]),
                    ]),

                Section::make('Status & Durasi Sesi PO per Pabrikan')
                    ->description('Buka atau tutup sesi pemesanan khusus untuk pabrikan tertentu, lengkap dengan batas durasi menit.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Placeholder::make('manufacturer_sessions')
                            ->hiddenLabel()
                            ->view('filament.pages.partials.manufacturer-sessions-table'),
                    ]),

                Section::make('Tampilan Layar Panggung & Pengumuman')
                    ->description('Atur teks pengumuman doorprize dan teks berjalan yang tampil di layar proyektor panggung.')
                    ->icon('heroicon-o-tv')
                    ->collapsible()
                    ->schema([
                        Toggle::make('is_leaderboard_visible')
                            ->label('Tampilkan Papan Peringkat Live di Panggung')
                            ->default(true),
                        TextInput::make('doorprize_announcement')
                            ->label('Teks Pengumuman Doorprize')
                            ->placeholder('Contoh: DOORPRIZE EXCLUSIVE: Grand Prize Emas Logam Mulia & Paket Liburan'),
                        Textarea::make('running_ticker_text')
                            ->label('Teks Berjalan di Bawah Layar Panggung (Running Ticker)')
                            ->rows(3)
                            ->placeholder('Teks berjalan realtime di bagian bawah layar proyektor...'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan Acara')
                ->icon('heroicon-o-check')
                ->submit('save')
                ->color('primary')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_session_now')
                ->label(fn (): string => EventSetting::current()->isOrderingOpen() ? 'Sesi PO Aktif' : 'Buka Sesi PO Sekarang')
                ->icon('heroicon-o-play')
                ->color(fn (): string => EventSetting::current()->isOrderingOpen() ? 'gray' : 'success')
                ->disabled(fn (): bool => EventSetting::current()->isOrderingOpen())
                ->tooltip(fn (): string => EventSetting::current()->isOrderingOpen() ? 'Sesi PO saat ini sudah berjalan' : 'Buka sesi pemesanan PO sekarang')
                ->requiresConfirmation()
                ->modalHeading('Buka Sesi Pemesanan Sekarang?')
                ->modalDescription('Sesi pemesanan akan langsung dibuka untuk seluruh tamu gathering.')
                ->modalSubmitActionLabel('Ya, Buka Sesi')
                ->action(function () {
                    $event = EventSetting::current();
                    $event->update([
                        'is_ordering_active' => true,
                        'session_start_at' => now(),
                        'session_end_at' => now()->addHours(2),
                    ]);
                    $this->fillForm();
                    Notification::make()
                        ->success()
                        ->title('Sesi PO Berhasil Dibuka!')
                        ->body('Pemesanan aktif hingga pukul '.$event->fresh()->session_end_at?->format('H:i').' WIB.')
                        ->send();
                }),

            Action::make('extend_15_minutes')
                ->label('+15 Menit')
                ->icon('heroicon-o-clock')
                ->color(fn (): string => EventSetting::current()->isOrderingOpen() ? 'warning' : 'gray')
                ->disabled(fn (): bool => ! EventSetting::current()->isOrderingOpen())
                ->tooltip(fn (): string => EventSetting::current()->isOrderingOpen() ? 'Tambah 15 menit ke waktu hitung mundur PO' : 'Hanya dapat digunakan saat sesi PO aktif')
                ->action(function () {
                    $event = EventSetting::current();
                    $currentEnd = $event->session_end_at ? $event->session_end_at : now();
                    $newEnd = $currentEnd->copy()->addMinutes(15);
                    $event->update([
                        'session_end_at' => $newEnd,
                        'is_ordering_active' => true,
                    ]);
                    $this->fillForm();
                    Notification::make()
                        ->success()
                        ->title('Waktu PO Diperpanjang!')
                        ->body('Waktu selesai diperpanjang hingga '.$newEnd->format('H:i').' WIB.')
                        ->send();
                }),

            Action::make('stop_session_now')
                ->label(fn (): string => ! EventSetting::current()->isOrderingOpen() ? 'Sesi PO Tertutup' : 'Tutup Sesi PO')
                ->icon('heroicon-o-stop')
                ->color(fn (): string => EventSetting::current()->isOrderingOpen() ? 'danger' : 'gray')
                ->disabled(fn (): bool => ! EventSetting::current()->isOrderingOpen())
                ->tooltip(fn (): string => ! EventSetting::current()->isOrderingOpen() ? 'Sesi PO saat ini sedang tidak aktif' : 'Tutup dan kunci sesi pemesanan sekarang')
                ->requiresConfirmation()
                ->modalHeading('Tutup Sesi Pemesanan Sekarang?')
                ->modalDescription('Apotek tidak dapat mengirimkan pesanan baru lagi setelah sesi ditutup.')
                ->modalSubmitActionLabel('Ya, Tutup Sesi')
                ->action(function () {
                    $event = EventSetting::current();
                    $event->update([
                        'is_ordering_active' => false,
                        'session_end_at' => now(),
                    ]);
                    $this->fillForm();
                    Notification::make()
                        ->warning()
                        ->title('Sesi PO Telah Ditutup')
                        ->body('Pemesanan telah dinonaktifkan untuk seluruh tamu.')
                        ->send();
                }),

            Action::make('set_manufacturer_session')
                ->label('Atur Sesi Pabrikan')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('info')
                ->form([
                    Select::make('manufacturer_ids')
                        ->label('Pilih Pabrikan')
                        ->options(fn () => Manufacturer::where('is_active', true)->pluck('name', 'id'))
                        ->multiple()
                        ->required()
                        ->helperText('Pilih satu atau beberapa pabrikan yang akan dibuka sesinya.'),
                    Select::make('duration_minutes')
                        ->label('Durasi Sesi Pemesanan')
                        ->options([
                            '15' => '15 Menit',
                            '30' => '30 Menit (Direkomendasikan)',
                            '45' => '45 Menit',
                            '60' => '60 Menit (1 Jam)',
                            'unlimited' => 'Tanpa Batas Waktu (Buka Penuh)',
                        ])
                        ->default('30')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $mIds = $data['manufacturer_ids'];
                    $endAt = $data['duration_minutes'] === 'unlimited' ? null : now()->addMinutes((int) $data['duration_minutes']);

                    Manufacturer::whereIn('id', $mIds)->update([
                        'is_ordering_active' => true,
                        'session_end_at' => $endAt,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Sesi Pabrikan Berhasil Diatur!')
                        ->body(count($mIds).' pabrikan aktif'.($endAt ? ' hingga pukul '.$endAt->format('H:i').' WIB.' : ' tanpa batas waktu.'))
                        ->send();
                }),

            Action::make('open_all_manufacturers')
                ->label('Buka Semua Pabrik')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Buka Sesi Seluruh Pabrikan?')
                ->modalDescription('Seluruh pabrikan aktif akan dibuka pemesanannya tanpa batas waktu.')
                ->action(function () {
                    Manufacturer::query()->update([
                        'is_ordering_active' => true,
                        'session_end_at' => null,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Seluruh Pabrikan Telah Dibuka')
                        ->send();
                }),

            Action::make('open_stage')
                ->label('Layar Panggung')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('stage.leaderboard'), shouldOpenInNewTab: true),
        ];
    }

    public function openManufacturerWithDuration(int $id, int $minutes = 30): void
    {
        $m = Manufacturer::findOrFail($id);
        $endAt = now()->addMinutes($minutes);
        $m->update([
            'is_ordering_active' => true,
            'session_end_at' => $endAt,
        ]);

        Notification::make()
            ->success()
            ->title("Sesi {$m->name} Dibuka ({$minutes} Menit)!")
            ->body("Aktif hingga pukul {$endAt->format('H:i')} WIB.")
            ->send();
    }

    public function openManufacturerUnlimited(int $id): void
    {
        $m = Manufacturer::findOrFail($id);
        $m->update([
            'is_ordering_active' => true,
            'session_end_at' => null,
        ]);

        Notification::make()
            ->success()
            ->title("Sesi {$m->name} Dibuka Penuh")
            ->body('Aktif tanpa batas waktu hingga ditutup manual.')
            ->send();
    }

    public function closeManufacturer(int $id): void
    {
        $m = Manufacturer::findOrFail($id);
        $m->update([
            'is_ordering_active' => false,
            'session_end_at' => now(),
        ]);

        Notification::make()
            ->warning()
            ->title("Sesi {$m->name} Ditutup")
            ->body('Pemesanan untuk produk pabrikan ini telah dinonaktifkan.')
            ->send();
    }

    public function extendManufacturer(int $id, int $minutes = 15): void
    {
        $m = Manufacturer::findOrFail($id);
        $baseTime = ($m->session_end_at && $m->session_end_at->isFuture()) ? $m->session_end_at : now();
        $newEnd = $baseTime->copy()->addMinutes($minutes);
        $m->update([
            'is_ordering_active' => true,
            'session_end_at' => $newEnd,
        ]);

        Notification::make()
            ->success()
            ->title("Sesi {$m->name} Diperpanjang +{$minutes} Menit")
            ->body("Aktif hingga pukul {$newEnd->format('H:i')} WIB.")
            ->send();
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $event = EventSetting::current();
        $event->update($data);

        Notification::make()
            ->success()
            ->title('Pengaturan Acara Tersimpan')
            ->body('Perubahan jadwal dan tempat acara berhasil diperbarui.')
            ->send();
    }
}
