<?php

namespace App\Filament\Resources;

use id;
use App\Models\Spk;
use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use App\Models\Teknisi;
use App\Models\Activiti;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Intervention\Image\Facades\Image;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\EditAction; 
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\ActivitiExporter;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\ActivitiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Enums\ActionsPosition; 
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use App\Filament\Resources\ActivitiResource\RelationManagers;
use Maatwebsite\Excel\Facades\Excel;

class ActivitiResource extends Resource
{
    protected static ?string $model = Activiti::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): ?string
    {
        return 'Surat Perintah Kerja';
    }

    public static function getNavigationLabel(): string
    {
        return 'SPK-Activities';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Activiti::where('status','<>','Closed')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // atau 'primary', 'success', 'warning'
    }    

    public static function getPluralModelLabel(): string
    {
        return 'SPK Activities';
    }
    public static function getModelLabel(): string
    {
        return 'SPK Activities';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        return parent::getEloquentQuery()->where('nik','like', "$user->nik%");
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nik'] = Auth::user()->nik;
        return $data;
    }    

    public static function canAccess(): bool
    {
        // dd(auth()->user()?->hasRole('super_admin'));
        return auth()->user()?->hasRole(['super_admin','admin','teknisi']); // atau yang boleh saja
        // return auth()->user()->can('view lks');
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status == 'Open';
    }

    public static function getRecordTitle($record): string
    {
        return 'Detail SPK : ' . $record->nospk;
    }
    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             TextEntry::make('status')
    //             ->label('Status SPK')
    //             ->badge()
    //             ->color(fn (string $state): string => match ($state) {
    //                 'Open' => 'warning',
    //                 'Process' => 'info',
    //                 'Closed' => 'success',
    //                 default => 'gray',
    //             }),
    //             Section::make('')
    //             ->schema([
    //             Grid::make(4)->schema([
    //                 TextEntry::make('kode_barang')
    //                     ->label('SKU'),
    //                 TextEntry::make('produk_nmb')
    //                     ->label('Nama Produk'),
    //                 TextEntry::make('no_seri')
    //                     ->label('No. Seri'),
    //                 TextEntry::make('tanggal_datang')
    //                     ->label('Tgl. Datang : '),
    //                 ]),
    //             ]),

    //             Section::make('')
    //             ->schema([
    //             Grid::make(3)->schema([
    //                 TextEntry::make('produk_krskn')
    //                     ->label('Keluhan Awal : '),
    //                 TextEntry::make('kerusakan')
    //                     ->label('Kerusakan :'),
    //                 TextEntry::make('solusi')
    //                     ->label('Solusi : '),
    //                 ]),
    //             ]),
    //             ImageEntry::make('foto_produk')
    //                 ->label('Foto-1')
    //                 ->width(200)
    //                 ->height(200),
    //             ImageEntry::make('foto_produk2')
    //                 ->label('Foto-2')
    //                 ->width(200)
    //                 ->height(200),
    //             ImageEntry::make('foto_produk3')
    //                 ->label('Foto-3')
    //                 ->width(200)
    //                 ->height(200),
    //             ViewEntry::make('fileupload')
    //                 ->label('video')
    //                 ->views('infolists.components.video'),
    //                 // entry lain...
    //         ]);
    // }    

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // balik ke list
    }
    
    public static function form(Form $form): Form
    {
        // dd($data);
        // Get the currently logged in user's NIK
        $user = Auth::user();
        $teknisi = null;
        
        // Directly connect user.nik with teknisi.nik
        if ($user && isset($user->nik)) {
            $teknisi = Teknisi::where('nik', $user->nik)->first();
        }
       
        return $form
            ->schema([
                // No Seri field (populated automatically based on selected kode_barang)
                Hidden::make('nik')-> default(fn () => Auth::user()->nik),
                // Hidden field for teknisi_id to ensure it's included in form submission
                Hidden::make('teknisi_id')
                    ->default(function () use ($teknisi) {
                        return $teknisi ? $teknisi->id : null;
                    }),
                // NOSPK (automatic dropdown based on technician's nospk field)
                Select::make('nospk')
                    ->label('No. SPK')
                    ->options(function () use ($teknisi) {
                        if (!$teknisi) return [];
                        return Spk::join('produk', 'spk.idp', '=', 'produk.id')
                            ->join('teknisi', 'teknisi.noko', '=', 'produk.noko')
                            ->where('teknisi.nik', $teknisi->nik)
                            ->where('produk.sts', '<>', 'Closed') // Hanya status yang tidak "Closed"
                            // ->groupBy('spk.nospk') // Pastikan hanya satu baris per SPK
                            ->orderByRaw('(spk.idwo) DESC') // Gunakan MAX untuk ORDER BY
                            ->pluck('spk.nospk', 'spk.nospk')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                            // Jika No SPK dihapus (state kosong), hapus juga field yang terkait
                        if (empty($state)) {
                            $set('kode_barang', null);
                            $set('no_seri', null);
                            $set('produk_nmb', null);
                            $set('produk_krskn', null);
                            $set('produk_id', null);
                            $set('kdcab', null);
                            $set('nmcust', null);
                        };
                        $nospk    = $get('nospk');
                        if (!$nospk ) return;
                            $spk = Spk::where('nospk', $nospk)
                                ->first();
                            if ($spk) {
                                $set('nmcust', $spk->nmcust);
                            }
                    }),

                TextInput::make('nmcust')
                    ->label('Nama Customer')
                    ->disabled() // agar tidak bisa diubah
                    ->dehydrated(false) // agar tidak disimpan ke database
                    ->afterStateHydrated(function ($state, callable $set, $get) {
                        // Saat form dibuka untuk edit atau view, ambil SPK terkait
                        $spk = Spk::where('nospk', $get('nospk'))->first();
                        if ($spk) {
                            $set('nmcust', $spk->nmcust);
                        }
                    }),
                    // Kode Barang dropdown (filtered by selected SPK)
                Select::make('produk_id')
                    ->label('Produk ID')
                    ->options(function (Forms\Get $get) {
                        $nospk = $get('nospk');
                        if (!$nospk) return [];
                        
                        // Produk::select(DB::raw("CONCAT(kode, ' - ', nama) as label"), 'id'
                        // Ambil hanya kode barang dengan status Open atau Process
                        return Produk::select(DB::raw("CONCAT(produk.nmb,' - ', produk.nosr) as listproduct"),'produk.id')
                            ->join('spk', 'spk.idp', '=', 'produk.id')
                            ->where('produk.sts', '<>', 'Closed') // Hanya status yang tidak "Closed"
                            ->where('spk.nospk','=',$nospk) // Hanya produk atas spk yg dipilih
                            ->orderByRaw('(produk.kdb) ') 
                            ->pluck('listproduct', 'produk.id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                        if (empty($state)) {
                            $set('no_seri', null);
                            $set('produk_nmb', null);
                            $set('kode_barang', null);
                            $set('kdcab', null);
                            $set('produk_krskn', null);
                        };

                        $nospk    = $get('nospk');
                        $produkid = $get('produk_id');
                        if (!$nospk || !$produkid) return;
                            $produk = Produk::where('id', $produkid)
                                ->first();
                            if ($produk) {
                                $set('no_seri', $produk->nosr);
                                $set('kode_barang', $produk->kdb);
                                $set('produk_nmb', $produk->nmb);
                                $set('kdcab', $produk->kdcab);
                                $set('produk_krskn', $produk->klh ?? 'N/A');
                            }
                    }),
                Hidden::make('kdcab')
                    ->label('Kode Cabang'),
                TextInput::make('no_seri')
                    ->label('No. Seri')
                    ->readOnly(),
                TextInput::make('produk_nmb')
                    ->label('Nama Produk')
                    ->readOnly(),
                TextInput::make('kode_barang')
                    ->label('Kode Barang')
                    ->readOnly(),

                Textarea::make('produk_krskn')
                    ->label('Keluhan Awal')
                    ->readOnly(),
                // Product Photo (manual input)
                FileUpload::make('foto_produk')
                    ->label('Foto Produk-1')
                    ->image()
                    ->maxSize(15000) // Maksimal 3MB per file
                    ->directory('spk-activities/photos')
                    ->resize(50),

                    // ->preserveFilenames()
                    // ->afterStateUpdated(function ($state) {
                    //     if (!$state) return;
                    //     $path = storage_path('app/public/' . $state);
                    //     if (!file_exists($path)) {
                    //         logger("File not found at: " . $path);
                    //         return;
                    //     }
                    //     if ($state) {
                    //         $path = storage_path('app/public/' . $state);
                    //         // Resize pakai Intervention Image
                    //         $image = Image::make($path)
                    //             ->resize(800, null, function ($constraint) {
                    //                 $constraint->aspectRatio();
                    //                 $constraint->upsize();
                    //             })
                    //             ->encode('jpg', 80); // Kompres kualitas
                    //         $image->save($path); // Simpan hasil resize
                    //         // Optimasi pakai Spatie Image Optimizer
                    //         ImageOptimizer::optimize($path);
                    //     }
                    // }),
                FileUpload::make('foto_produk2')
                    ->label('Foto Produk-2')
                    ->image()
                    ->maxSize(15000) // Maksimal 3MB per file
                    ->directory('spk-activities/photos')
                    ->resize(50),
                    // ->preserveFilenames()
                    // ->afterStateUpdated(function ($state) {
                    //     if (!$state) return;
                    //     $path = storage_path('app/public/' . $state);
                    //     if (!file_exists($path)) {
                    //         logger("File not found at: " . $path);
                    //         return;
                    //     }
                    //     if ($state) {
                    //         $path = storage_path('app/public/' . $state);
                    //         // Resize pakai Intervention Image
                    //         $image = Image::make($path)
                    //             ->resize(800, null, function ($constraint) {
                    //                 $constraint->aspectRatio();
                    //                 $constraint->upsize();
                    //             })
                    //             ->encode('jpg', 80); // Kompres kualitas
                    //         $image->save($path); // Simpan hasil resize
                    //         // Optimasi pakai Spatie Image Optimizer
                    //         ImageOptimizer::optimize($path);
                    //     }
                    // }),
                FileUpload::make('foto_produk3')
                    ->label('Foto Produk-3')
                    ->image()
                    ->maxSize(15000) // Maksimal 3MB per file
                    ->directory('spk-activities/photos')
                    ->resize(50),
                    // ->preserveFilenames()
                    // ->afterStateUpdated(function ($state) {
                    //     if (!$state) return;
                    //     $path = storage_path('app/public/' . $state);
                    //     if (!file_exists($path)) {
                    //         logger("File not found at: " . $path);
                    //         return;
                    //     }
                    //     if ($state) {
                    //         $path = storage_path('app/public/' . $state);
                    //         // Resize pakai Intervention Image
                    //         $image = Image::make($path)
                    //             ->resize(800, null, function ($constraint) {
                    //                 $constraint->aspectRatio();
                    //                 $constraint->upsize();
                    //             })
                    //             ->encode('jpg', 80); // Kompres kualitas
                    //         $image->save($path); // Simpan hasil resize
                    //         // Optimasi pakai Spatie Image Optimizer
                    //         ImageOptimizer::optimize($path);
                    //     }
                    // }),

                    // // ->multiple() // Mengizinkan multiple files
                    // // ->acceptedFileTypes([
                    // //     'image/*', // Semua jenis gambar
                    // //     'video/*', // Semua jenis video
                    // // ])
                    // ->maxSize(3000) // Maksimal 3MB per file
                    // ->directory('spk-activities/photos') // Disimpan di storage/app/public/spk-activities/photos
                    // ->visibility('public')
                    // ->previewable()
                    // ->getUploadedFileNameForStorageUsing(fn ($file) => $file->getClientOriginalName()),

                FileUpload::make('fileupload')
                    ->label('File Video')
                    // ->image()
                    ->disk('public')
                    // ->multiple() // Mengizinkan multiple files
                    ->acceptedFileTypes([
                        'video/*', // Semua jenis video
                    ])
                    ->maxSize(15000) // Maksimal 3MB per file
                    ->directory('spk-activities/photos') // Disimpan di storage/app/public/spk-activities/photos
                    ->visibility('public')
                    ->previewable()
                    ->getUploadedFileNameForStorageUsing(fn ($file) => $file->getClientOriginalName()),

                // Damage description (manual input)
                Textarea::make('kerusakan')
                    ->label('Kerusakan')
                    ->required(),

                // Solution (manual input)
                Textarea::make('solusi')
                    ->label('Solusi')
                    ->required(),

                // Status (dropdown)
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Open' => 'Open',
                        'Process' => 'Process',
                        'Closed' => 'Closed',
                    ])
                    ->required(),
                Select::make('spbk')
                    ->label('Part Bekas Kembali')
                    ->options([
                        'No' => 'No',
                        'Yes' => 'Yes',
                    ])
                    ->required(),

                // Arrival date and time
                DateTimePicker::make('tanggal_datang')
                    ->label('Tanggal Datang')
                    ->required()
                    ->reactive()
                    ->maxDate(fn (callable $get) => $get('tanggal_pulang')),
                    // ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    //     $tanggalPulang = $get('tanggal_pulang');
                    //     if ($tanggalPulang && $state && $state > $tanggalPulang) {
                    //         $set('tanggal_datang', null); // kosongkan input
                    //         // Atau pakai setError dari Filament v3
                    //         $set('errors.tanggal_datang', 'Tanggal datang tidak boleh lebih besar dari tanggal pulang.');
                    //     }
                    // }),

                    // Departure date and time
                DateTimePicker::make('tanggal_pulang')
                    ->label('Tanggal Pulang')
                    ->required()
                    ->minDate(fn (callable $get) => $get('tanggal_datang')),
                    // ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    //     $tanggalDatang = $get('tanggal_datang');
                    //     if ($tanggalDatang && $state && $state < $tanggalDatang) {
                    //         $set('tanggal_pulang', null); // kosongkan input
                    //         $set('errors.tanggal_pulang', 'Tanggal pulang tidak boleh lebih kecil dari tanggal datang.');
                    //     }
                    // }),
                // // Arrival date and time 2
                // DateTimePicker::make('tanggal_datang2')
                //     ->label('Tanggal Datang 2'),

                // // Departure date and time 2
                // DateTimePicker::make('tanggal_pulang2')
                //     ->label('Tanggal Pulang 2'),
                
                // // Arrival date and time 3
                // DateTimePicker::make('tanggal_datang3')
                //     ->label('Tanggal Datang 3'),

                // // Departure date and time 3
                // DateTimePicker::make('tanggal_pulang3')
                //     ->label('Tanggal Pulang 3'),

                // Technical measurements (if applicable)
                Forms\Components\Section::make('Pengukuran Teknis')
                    ->schema([
                        TextInput::make('voltage')
                            ->label('Voltage / Tegangan (Volt)')
                            ->numeric()
                            ->hint('Jika ada'),
                            
                        TextInput::make('current')
                            ->label('Current / Arus (Ampere)')
                            ->numeric()
                            ->hint('Jika ada'),
                            
                        TextInput::make('gas_pressure')
                            ->label('Gas Pressure / Tekanan Gas (mbar)')
                            ->numeric()
                            ->hint('Jika ada'),
                            
                        TextInput::make('water_pressure')
                            ->label('Water Pressure / Tekanan Air (bar)')
                            ->numeric()
                            ->hint('Jika ada'),
                            
                        TextInput::make('room_temperature')
                            ->label('Room Temperature / Temperatur Ruangan (¡ÆC)')
                            ->numeric()
                            ->hint('Jika ada'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teknisi.nama')
                    ->label('Nama Teknisi')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('nospk')
                    ->label('No. SPK')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('produk_nmb')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('produk_id')
                    ->label('ID Produk')
                    ->sortable()
                    ->hidden(),


                // ImageColumn::make('foto_produk')
                //     ->label('Foto Produk')
                //     ->disk('public')
                //     ->getStateUsing(fn ($record) => $record->foto_produk ? asset('storage/spk-activities/photos/' . basename($record->foto_produk)) : null)
                //     ->width(100) 
                //     ->height(100),

                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable()
                    ->searchable(),
                    
                TextColumn::make('no_seri')
                    ->label('No Seri')
                    ->sortable()
                    ->searchable()
                    ->searchable(),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Open' => 'warning',
                        'Process' => 'info',
                        'Closed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('spbk')
                    ->label('Part Bekas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'No' => 'warning',
                        'Yes' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                TextColumn::make('tanggal_datang')
                    ->label('Tanggal Datang')
                    ->dateTime()
                    ->sortable(),
                    
                TextColumn::make('tanggal_pulang')
                    ->label('Tanggal Pulang')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Entry Date')
                    ->dateTime()
                    ->sortable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',
                        'Process' => 'Process',
                        'Closed' => 'Closed',
                    ]),
                    
                Tables\Filters\Filter::make('updated_at')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    }),
            ])
            
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn ($record) => $record->status !== 'Closed')
                ->label(false),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => $record->status !== 'Closed')
                ->label(false),
                Tables\Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                // ->hidden()
                ->label(false),
            ])
            // ->actionsPosition(ActionsPosition::BeforeColumns); // ✅ Enum, bukan string
            
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make('delete')
                    ->label('Delete Records')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $adaClosed = $records->contains(fn ($record) => $record->status === 'Closed');
                        if ($adaClosed) {
                            Notification::make()
                                ->title('Aksi dibatalkan')
                                ->body('Tidak bisa menghapus data dengan status CLOSED.')
                                ->danger() // 🔴 warna merah
                                ->persistent() // butuh diklik untuk hilang
                                ->send();
                            return;
                        }
                        $records->each->delete();
                        Notification::make()
                            ->title('Berhasil')
                            ->body('Data berhasil dihapus.')
                            ->success()
                            ->persistent() // butuh diklik untuk hilang
                            ->send();
                    }),
                ]),
            ])

            ->headerActions([
                ExportAction::make('export')
                    ->label('Export Excel')
                    ->color('gray')
                    ->exporter(ActivitiExporter::class),
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
            'index' => Pages\ListActivitis::route('/'),
            'create' => Pages\CreateActiviti::route('/create'),
            'edit' => Pages\EditActiviti::route('/{record}/edit'),
        ];
    }
}
