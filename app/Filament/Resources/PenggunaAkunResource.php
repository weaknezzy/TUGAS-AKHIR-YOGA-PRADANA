<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenggunaAkunResource\Pages;
use App\Filament\Resources\PenggunaAkunResource\RelationManagers;
use App\Models\PenggunaAkun;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PenggunaAkunResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'owner' => 'Owner',
                        'pelanggan' => 'Pelanggan',
                    ])
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn(string $context) => $context === 'create')
                    ->maxLength(255)
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Password'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'admin' => 'success',
                        'owner' => 'info',
                        'pelanggan' => 'warning',
                        default => 'secondary',
                    })
                    ->label('Role')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListPenggunaAkuns::route('/'),
            'create' => Pages\CreatePenggunaAkun::route('/create'),
            'edit' => Pages\EditPenggunaAkun::route('/{record}/edit'),
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
