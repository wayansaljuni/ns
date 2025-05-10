<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        // if (!auth()->user()?->hasRole('teknisi')) {
            return parent::getEloquentQuery()->where('nik','like', "%$user->nik%");
        // }
    }

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\User::count();
    }
   
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                Select::make('roles')
                ->relationship('roles','name')
                ->disabled(fn () => !auth()->user()?->hasRole('super_admin')),
                TextInput::make('nik')->label('NIK')->nullable(),
                TextInput::make('kdcab')->label('Kode Cabang')->nullable(),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Password')
                    ->required(fn (string $context): bool => $context === 'create'),                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('User Name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->searchable(),
                TextColumn::make('nik'),
                TextColumn::make('kdcab')->label('Cabang'),               
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
