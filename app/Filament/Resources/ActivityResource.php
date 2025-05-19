<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ActivityResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ActivityResource\RelationManagers;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function getNavigationGroup(): ?string
    {
        return 'User Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Log Transaksi';
    }
  public static function getPluralModelLabel(): string
    {
        return 'Log Transaksi';
    }
    public static function getModelLabel(): string
    {
        return 'Log Transaksi';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin','admin']); // atau yang boleh saja
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->since()
                    ->label('Created at'),
                TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('causer.name') // user yang melakukan
                    ->label('User Entry')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('properties.attributes.nospk')
                    ->label('No SPK')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('properties.attributes.kode_barang')
                    ->label('Kode Produk')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('properties.attributes.no_seri')
                    ->label('Serial Number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('properties.attributes.produk_nmb')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('properties')
                //     ->sortable()
                //     ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ;
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
            'index' => Pages\ListActivities::route('/'),
            // 'create' => Pages\CreateActivity::route('/create'),
            // 'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
