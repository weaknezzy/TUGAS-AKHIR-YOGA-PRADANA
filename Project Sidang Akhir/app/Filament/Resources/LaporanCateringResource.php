<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanCateringResource\Pages;
use App\Models\LaporanCatering;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\Action;

class LaporanCateringResource extends Resource
{
    protected static ?string $model = LaporanCatering::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Laporan Catering';
    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal_pengantaran')
                    ->required()
                    ->label('Tanggal Pengantaran'),
                // Jika ingin menampilkan created_at di form, tambahkan di sini:
                Forms\Components\Placeholder::make('created_at')
                    ->label('Tanggal Pemesanan')
                    ->content(fn($record) => $record?->created_at?->format('d-m-Y H:i') ?? '-')
                    ->visible(fn($record) => filled($record)),

                Forms\Components\TextInput::make('nama_pemesan')
                    ->required()
                    ->label('Nama'),
                    
                Forms\Components\TextInput::make('no_hp')
                    ->required()
                    ->label('No. HP'),

                Forms\Components\TextInput::make('alamat')
                    ->required()
                    ->label('Alamat'),


                Forms\Components\TextInput::make('acara')
                    ->required()
                    ->label('Acara'),

                Forms\Components\Select::make('menu')
                    ->options([
                        'PAKET 1' => 'PAKET 1 Rp.16.000/Porsi',
                        'PAKET 2' => 'PAKET 2 Rp.17.000/Porsi',
                        'PAKET 3' => 'PAKET 3 Rp.28.000/Porsi',
                    ])
                    ->required()
                    ->label('Menu')
                    ->reactive()
                    ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                        $jumlah = $get('jumlah_porsi');
                        $kemasan = $get('kemasan');
                        $harga_paket = match ($state) {
                            'PAKET 1' => 16000,
                            'PAKET 2' => 17000,
                            'PAKET 3' => 28000,
                            default => 0,
                        };
                        $harga_kemasan = match ($kemasan) {
                            'Bungkus Biasa' => 0,
                            'Box' => 3000,
                            default => 0,
                        };
                        $set('total_bayar', ($harga_paket + $harga_kemasan) * (int) $jumlah);
                    }),

                Forms\Components\TextInput::make('jumlah_porsi')
                    ->numeric()
                    ->required()
                    ->label('Jumlah Porsi')
                    ->reactive()
                    ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                        $menu = $get('menu');
                        $kemasan = $get('kemasan');
                        $harga_paket = match ($menu) {
                            'PAKET 1' => 16000,
                            'PAKET 2' => 17000,
                            'PAKET 3' => 28000,
                            default => 0,
                        };
                        $harga_kemasan = match ($kemasan) {
                            'Bungkus Biasa' => 0,
                            'Box' => 3000,
                            default => 0,
                        };
                        $set('total_bayar', ($harga_paket + $harga_kemasan) * (int) $state);
                    }),

                Forms\Components\Select::make('kemasan')
                    ->options([
                        'Bungkus Biasa' => 'Bungkus Biasa Rp.0',
                        'Box' => 'Box Rp.3.000',
                    ])
                    ->required()
                    ->label('Kemasan')
                    ->reactive()
                    ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                        $menu = $get('menu');
                        $jumlah = $get('jumlah_porsi');
                        $harga_paket = match ($menu) {
                            'PAKET 1' => 16000,
                            'PAKET 2' => 17000,
                            'PAKET 3' => 28000,
                            default => 0,
                        };
                        $harga_kemasan = match ($state) {
                            'Bungkus Biasa' => 0,
                            'Box' => 3000,
                            default => 0,
                        };
                        $set('total_bayar', ($harga_paket + $harga_kemasan) * (int) $jumlah);
                    }),

                Forms\Components\Select::make('metode_pembayaran')
                    ->options([
                        'COD' => 'COD',
                        'Transfer' => 'Transfer',
                    ])
                    ->required()
                    ->label('Metode Pembayaran')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        // Reset bukti pembayaran jika switch ke COD
                        if ($state === 'COD') {
                            $set('bukti_pembayaran', null);
                            // Reset status pembayaran ke default untuk COD
                            $set('status_pembayaran', 'Belum Bayar');
                            // Reset total dibayar dan sisa bayar
                            $set('total_dibayar', 0);
                            $set('sisa_bayar', 0);
                        }
                    }),

                Forms\Components\FileUpload::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->directory('bukti-pembayaran')
                    ->visibility('public')
                    ->nullable()
                    ->helperText('Upload bukti pembayaran dari pelanggan (jika metode pembayaran Transfer)')
                    ->visible(fn (Get $get) => $get('metode_pembayaran') === 'Transfer')
                    ->required(fn (Get $get) => $get('metode_pembayaran') === 'Transfer')
                    ->rules(['image', 'max:2048'])
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        // Auto-update status pembayaran jika ada bukti pembayaran
                        if (filled($state)) {
                            $totalBayar = $get('total_bayar') ?? 0;
                            $totalDibayar = $get('total_dibayar') ?? 0;
                            
                            // Jika sudah dibayar penuh, status = Sudah Bayar
                            if ($totalDibayar >= $totalBayar) {
                                $set('status_pembayaran', 'Sudah Bayar');
                            } else {
                                $set('status_pembayaran', 'Dibayar Sebagian');
                            }
                        }
                    }),

                Forms\Components\Select::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Sudah Bayar' => 'Sudah Bayar',
                        'Dibayar Sebagian' => 'Dibayar Sebagian',
                    ])
                    ->default('Belum Bayar')
                    ->required()
                    ->helperText('Update status pembayaran sesuai kondisi sebenarnya. Untuk COD bisa diupdate manual.')
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        // Auto-update status jika ada bukti pembayaran dan status diubah ke "Sudah Bayar"
                        if ($state === 'Sudah Bayar' && filled($get('bukti_pembayaran'))) {
                            // Status sudah sesuai
                        }
                        // Auto-update status jika switch dari "Sudah Bayar" ke status lain
                        if ($state !== 'Sudah Bayar' && $get('metode_pembayaran') === 'Transfer') {
                            // Bisa direset ke status lain
                        }
                    }),

                Forms\Components\Section::make('Detail Pembayaran')
                    ->description('Informasi detail pembayaran yang sudah dilakukan')
                    ->schema([
                        Forms\Components\TextInput::make('total_dibayar')
                            ->label('Total Sudah Dibayar')
                            ->numeric()
                            ->default(0)
                            ->helperText('Masukkan jumlah yang sudah dibayar oleh pelanggan')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $totalBayar = $get('total_bayar') ?? 0;
                                $sisaBayar = max(0, $totalBayar - $state);
                                $set('sisa_bayar', $sisaBayar);
                                
                                // Auto-update status berdasarkan total yang sudah dibayar
                                if ($state >= $totalBayar) {
                                    $set('status_pembayaran', 'Sudah Bayar');
                                } elseif ($state > 0) {
                                    $set('status_pembayaran', 'Dibayar Sebagian');
                                } else {
                                    $set('status_pembayaran', 'Belum Bayar');
                                }
                            }),

                        Forms\Components\TextInput::make('sisa_bayar')
                            ->label('Sisa yang Belum Dibayar')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Sisa pembayaran yang belum lunas (otomatis terhitung)'),

                        Forms\Components\Textarea::make('catatan_pembayaran')
                            ->label('Catatan Pembayaran')
                            ->nullable()
                            ->helperText('Catatan tambahan tentang pembayaran (opsional)'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\TextInput::make('total_bayar')
                    ->numeric()
                    ->required()
                    ->label('Total Bayar')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pemesanan')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->default('desc')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pengantaran')
                    ->label('Tanggal Pengantaran')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('nama_pemesan')
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP'),
                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat'),

                Tables\Columns\TextColumn::make('acara')
                    ->label('Acara'),
                Tables\Columns\TextColumn::make('menu')
                    ->label('Menu'),
                Tables\Columns\TextColumn::make('jumlah_porsi')
                    ->label('Jumlah Porsi'),
                Tables\Columns\TextColumn::make('kemasan')
                    ->label('Kemasan'),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode Pembayaran'),
                Tables\Columns\ImageColumn::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
                    ->circular()
                    ->size(40)
                    ->visibility('public'),
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->colors([
                        'success' => 'Sudah Bayar',
                        'warning' => 'Dibayar Sebagian',
                        'danger' => 'Belum Bayar',
                    ]),
                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('total_dibayar')
                    ->label('Sudah Dibayar')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('sisa_bayar')
                    ->label('Sisa Bayar')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan'),
            ])
            ->filters([
                TrashedFilter::make(),
                
                // Filter status pembayaran
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Sudah Bayar' => 'Sudah Bayar',
                        'Dibayar Sebagian' => 'Dibayar Sebagian',
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'], function ($query, $value) {
                            $query->where('status_pembayaran', $value);
                        });
                    }),
                
                // Filter periode per bulan berdasarkan tanggal pemesanan
                Tables\Filters\Filter::make('periode_bulan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->label('Bulan'),
                        Forms\Components\TextInput::make('tahun')
                            ->numeric()
                            ->default(date('Y'))
                            ->label('Tahun'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['bulan'] && $data['tahun'],
                                fn ($query) => $query->whereYear('created_at', $data['tahun'])
                                                    ->whereMonth('created_at', $data['bulan'])
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['bulan'] ?? null) {
                            $bulan = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                            $indicators['bulan'] = 'Bulan: ' . $bulan[$data['bulan']] . ' ' . ($data['tahun'] ?? date('Y'));
                        }
                        return $indicators;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (LaporanCatering $record) => Auth::user()->role === 'admin'),
                
                // View bukti pembayaran action
                Action::make('view_bukti')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->bukti_pembayaran))
                    ->modalHeading('Bukti Pembayaran')
                    ->modalContent(fn ($record) => view('filament.modals.bukti-pembayaran', ['record' => $record]))
                    ->modalWidth('lg'),
                
                // Delete action (hanya admin)
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (LaporanCatering $record) => Auth::user()->role === 'admin'),
                
                // Restore action (hanya admin)
                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => $record->trashed() && Auth::user()->role === 'admin')
                    ->action(fn ($record) => $record->restore()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->role === 'admin'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanCaterings::route('/'),
            'create' => Pages\CreateLaporanCatering::route('/create'),
            'edit' => Pages\EditLaporanCatering::route('/{record}/edit'),
        ];
    }

    // Method untuk mengontrol akses berdasarkan role
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'owner']);
    }

    public static function canView($record): bool
    {
        return in_array(Auth::user()->role, ['admin', 'owner']);
    }
}
