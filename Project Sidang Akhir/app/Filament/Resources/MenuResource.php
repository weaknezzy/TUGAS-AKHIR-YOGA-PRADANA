<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = 'Menu';
    protected static ?string $pluralModelLabel = 'Daftar Menu';
    protected static ?string $modelLabel = 'Menu';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_menu')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama Menu'),

                Select::make('kategori')
                    ->required()
                    ->options([
                        'Makanan' => 'Makanan',
                        'Minuman' => 'Minuman',
                    ])
                    ->label('Kategori'),

                TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->stripCharacters('.')
                    ->prefix('Rp')
                    ->label('Harga'),

                FileUpload::make('gambar')
                    ->directory('menu')
                    ->required()
                    ->image()
                    ->imageEditor() // ✔️ Buka editor gambar saat upload
                    ->imageCropAspectRatio('1:1') // ✔️ Crop otomatis jadi kotak
                    ->imageResizeMode('cover') // ✔️ Isi penuh tanpa distorsi
                    ->imageResizeTargetWidth('600') // ✔️ Resize ke lebar tertentu
                    ->imageResizeTargetHeight('600') // ✔️ Tinggi tertentu
                    ->preserveFilenames()
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                        return (string) str()->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('nama_menu')
                    ->label('Nama Menu')
                    ->searchable(),

                TextColumn::make('kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Makanan' => 'success',
                        'Minuman' => 'info',
                        default => 'secondary',
                    })
                    ->label('Kategori'),

                TextColumn::make('harga')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->label('Harga')
                    ->sortable(),
                ImageColumn::make('gambar')
                    ->label('Foto')
                    ->size(120),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        // Owner dan admin bisa melihat resource
        return Auth::user()?->role === 'admin' || Auth::user()?->role === 'owner';
    }

    public static function canCreate(): bool
    {
        // Hanya admin yang bisa create
        return Auth::user()?->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        // Hanya admin yang bisa edit
        return Auth::user()?->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        // Hanya admin yang bisa delete
        return Auth::user()?->role === 'admin';
    }
}
