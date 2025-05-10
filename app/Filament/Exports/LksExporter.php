<?php

namespace App\Filament\Exports;

use Carbon\Carbon;
use App\Models\Lks;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class LksExporter extends Exporter
{
    protected static ?string $model = Lks::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('tanggal')->label('MGG : ')->formatStateUsing(fn ($state) => 'M ' . Carbon::parse($state)->week),
            ExportColumn::make('noreference')->label('No. Reference'),
            ExportColumn::make('pembuat_lks')->label('Pembuat LKS'),
            ExportColumn::make('penyebab_lks')->label('Penyebab LKS'),
            ExportColumn::make('unit_jadi')->label('Unit Jadi [FG]'),
            ExportColumn::make('temuan')->label('Temuan Ketidaksesuaian'),
            ExportColumn::make('penyebab')->label('Penyebab Ketidaksesuaian'),
            ExportColumn::make('fileupload')
            ->label('Attach File')
            ->state(function ($record) {
                return url('storage/' . $record->fileupload);
            }),            
            ExportColumn::make('kategory')->label('Kategori'),
            ExportColumn::make('faktor_lks')->label('Faktor LKS'),
            ExportColumn::make('pl_spv')->label('PL / SPV'),
            ExportColumn::make('dft_opr')->label('DFT / OPR'),
            ExportColumn::make('pengulanganketidaksesuaian')->label('Pengulangan'),
            ExportColumn::make('penyelesaian_sementara')->label('Penyelesaian Sementara'),
            ExportColumn::make('penyelesaian_permanen')->label('Penyelesaian Permanen'),
            ExportColumn::make('target_selesai')->label('Target Selesai')->formatStateUsing(fn ($state) => 'M ' . Carbon::parse($state)->week),
            ExportColumn::make('realisasi_selesai')->label('Realisasi Selesai')->formatStateUsing(fn ($state) => 'M ' . Carbon::parse($state)->week),
            ExportColumn::make('status')->label('Status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your lks export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
