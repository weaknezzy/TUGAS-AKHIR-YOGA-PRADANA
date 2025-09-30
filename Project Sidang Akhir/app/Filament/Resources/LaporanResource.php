<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanResource\Pages;
use App\Models\Laporan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\TrashedFilter;

class LaporanResource extends Resource
{
    protected static ?string $model = Laporan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Laporan Harian';
    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->required()
                    ->disabled()
                    ->label('Nama Pelanggan'),
                    
                Forms\Components\TextInput::make('no_telp')
                    ->required()
                    ->disabled()
                    ->label('Nomor Telepon'),

                Forms\Components\Select::make('payment_method')
                    ->options([
                        'COD' => 'COD',
                        'Transfer' => 'Transfer'
                    ])
                    ->required()
                    ->disabled()
                    ->label('Metode Pembayaran'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Diproses' => 'Diproses',
                        'Selesai' => 'Selesai',
                        'Dibatalkan' => 'Dibatalkan'
                    ])
                    ->required()
                    ->label('Status'),

                Forms\Components\TextInput::make('total_amount')
                    ->disabled()
                    ->prefix('Rp')
                    ->numeric()
                    ->required()
                    ->label('Total Pembayaran'),

                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->disabled(),

                Forms\Components\Textarea::make('order_items')
                    ->label('Item Pesanan')
                    ->disabled()
                    ->formatStateUsing(function ($state) {
                        $items = json_decode($state, true);
                        if (!$items) return $state;
                        
                        return collect($items)->map(function ($item) {
                            return sprintf(
                                '%s x%d (Rp%s)',
                                $item['nama_menu'],
                                $item['jumlah'],
                                number_format($item['harga'], 0, ',', '.')
                            );
                        })->join("\n");
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('Nomor Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order_items')
                    ->label('Item Pesanan')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $items = json_decode($state, true);
                        if (!$items) return $state;
                        
                        return collect($items)->map(function ($item) {
                            return sprintf(
                                '%s x%d (Rp%s)',
                                $item['nama_menu'],
                                $item['jumlah'],
                                number_format($item['harga'], 0, ',', '.')
                            );
                        })->join('<br>');
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Diproses' => 'info',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->note)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->default('desc')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
                // Filter periode per bulan berdasarkan tanggal dibuat
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
                // Action untuk konfirmasi pesanan (hanya admin)
                Tables\Actions\Action::make('confirmOrder')
                    ->label('Konfirmasi Pesanan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pesanan')
                    ->modalDescription('Apakah Anda yakin ingin mengkonfirmasi pesanan ini?')
                    ->modalSubmitActionLabel('Ya, Konfirmasi')
                    ->visible(fn (Laporan $record) => $record->status === 'Pending' && Auth::user()->role === 'admin')
                    ->action(function (Laporan $record) {
                        try {
                            DB::beginTransaction();
                            
                            // Update status laporan dan pemesanan
                            $record->updateStatus('Diproses');
                            
                            DB::commit();
                            
                            Notification::make()
                                ->title('Pesanan berhasil dikonfirmasi')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Gagal mengkonfirmasi pesanan')
                                ->danger()
                                ->send();
                        }
                    }),

                // Action untuk tolak pesanan (hanya admin)
                Tables\Actions\Action::make('rejectOrder')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pesanan')
                    ->modalDescription('Apakah Anda yakin ingin menolak pesanan ini?')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn (Laporan $record) => $record->status === 'Pending' && Auth::user()->role === 'admin')
                    ->action(function (Laporan $record) {
                        try {
                            DB::beginTransaction();
                            
                            // Update status laporan dan pemesanan menjadi dibatalkan
                            $record->updateStatus('Dibatalkan');
                            
                            DB::commit();
                            
                            Notification::make()
                                ->title('Pesanan berhasil ditolak')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Gagal menolak pesanan')
                                ->danger()
                                ->send();
                        }
                    }),

                // Edit action (hanya admin)
                Tables\Actions\EditAction::make()
                    ->visible(fn (Laporan $record) => Auth::user()->role === 'admin' && !in_array($record->status, ['Selesai', 'Dibatalkan'])),
                
                // Delete action (hanya admin)
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Laporan $record) => Auth::user()->role === 'admin'),
                
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
            'index' => Pages\ListLaporans::route('/'),
            'edit' => Pages\EditLaporan::route('/{record}/edit'),
        ];
    }

    // public static function canCreate(): bool
    // {
    //     return false; // Menonaktifkan fitur create
    // }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
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

