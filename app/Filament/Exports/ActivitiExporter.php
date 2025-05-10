<?php

namespace App\Filament\Exports;

use App\Models\Activiti;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Writer\Common\Entity\Worksheet;

class ActivitiExporter extends Exporter
{
    protected static ?string $model = Activiti::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('nik'),
            ExportColumn::make('teknisi.nama')->label('Teknisi'),
            ExportColumn::make('kode_barang'),
            ExportColumn::make('no_seri'),
            ExportColumn::make('nospk'),
            ExportColumn::make('produk_nmb')->label('Nama Produk'),
            ExportColumn::make('produk_krskn')->label('Diagnosa'),
            ExportColumn::make('kerusakan'),
            ExportColumn::make('solusi'),
            ExportColumn::make('status')->label('Status SPK'),
            ExportColumn::make('tanggal_datang'),
            ExportColumn::make('tanggal_pulang'),
            ExportColumn::make('voltage'),
            ExportColumn::make('current'),
            ExportColumn::make('gas_pressure'),
            ExportColumn::make('water_pressure'),
            ExportColumn::make('room_temperature'),
            ExportColumn::make('foto_produk2')
            ->label('Gambar-2')
            ->state(function ($record) {
                return url('storage/' . $record->foto_produk2);
            }),            
            ExportColumn::make('foto_produk3')
            ->label('Gambar-3')
            ->state(function ($record) {
                return url('storage/' . $record->foto_produk3);
            }),            
            ExportColumn::make('fileupload')
            ->label('File Video')
            ->state(function ($record) {
                return url('storage/' . $record->fileupload);
            }),            
            ExportColumn::make('kdcab'),
            ExportColumn::make('spbk')
            ->label('Part Kembali'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your activiti export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk baris pertama (header)
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'name' => 'Arial'],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function fileName(): string
    {
        return 'activities-export.xlsx'; // ✅ Nama file export
    }
}
