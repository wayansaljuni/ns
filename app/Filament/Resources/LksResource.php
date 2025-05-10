<?php

namespace App\Filament\Resources;

use id;
use Carbon\Carbon;
use App\Models\Lks;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
// use Spatie\ImageOptimizer\Image;
use Filament\Forms\Components\Grid;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Auth;
use App\Filament\Exports\LksExporter;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Components\Select;
use Intervention\Image\Facades\Image;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LksResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LksResource\RelationManagers;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class LksResource extends Resource
{
    protected static ?string $model = Lks::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    public static function getNavigationBadge(): ?string
    {
        return (string) Lks::where('status', 'Open')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // atau 'primary', 'success', 'warning'
    }    

    public static function getNavigationGroup(): ?string
    {
        return 'Lembar Ketidaksesuaian';
    }

    public static function getNavigationLabel(): string
    {
        return 'LKS-Activities';
    }
    public static function getPluralModelLabel(): string
    {
        return 'LKS';
    }
    public static function getModelLabel(): string
    {
        return 'LKS';
    }

   
    // public static function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // $data['user_id'] = auth()->id(); // otomatis isi user_id
    //     // return $data;
    //     $data['user_id'] = Auth::user()->id;
    //     return $data;
    // }
    public static function canAccess(): bool
    {
        // dd(auth()->user()?->hasRole('admin'));
        return auth()->user()?->hasRole(['admin','lks']); // atau yang boleh saja
        // return auth()->user()->can('view lks');
    }
    public static function canEdit(Model $record): bool
    {
        return $record->status == 'Open';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // balik ke list
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest(); // urut berdasarkan created_at DESC
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')-> default(fn () => Auth::user()->id),

                Fieldset::make('')
                ->schema([
                    Grid::make(4)
                        ->schema([
                            DatePicker::make('tanggal')
                                ->label('Tanggal')
                                ->required(),
                            TextInput::make('noreference')
                                ->label('No. Reference')
                                ->maxLength(25)
                                ->required(),
                            Select::make('mesin_id_pembuat')
                                ->label('Pembuat LKS')
                                ->relationship('pembuat', 'nama')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->required()
                                        ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                            ]),                    

                            Select::make('mesin_id_penyebab')
                                ->label('Penyebab LKS')
                                ->relationship('penyebab', 'nama')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama')
                                        ->required()
                                        ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                                ]),                    
                            // Select::make('pembuat_lks')
                            //     ->label('Pembuat LKS')
                            //     ->options([
                            //         'ASSY BODY REFRI' => 'ASSY BODY REFRI',
                            //         'ASSY CHI PRO' => 'ASSY CHI PRO',
                            //         'ASSY SUPER JOB' => 'ASSY SUPER JOB',
                            //         'QC INPROSES' => 'QC INPROSES',
                            //         'REFRI COOLING' => 'REFRI COOLING',
                            //         'WELDING KBL' => 'WELDING KBL',
                            //     ])
                            //     ->required(),
                            // Select::make('penyebab_lks')
                            //     ->label('Penyebab LKS')
                            //     ->options([
                            //         'MESIN BENDING' => 'MESIN BENDING',
                            //         'MESIN PUNCHING' => 'MESIN PUNCHING',
                            //         'PD' => 'PD',
                            //         'QC INCOMING' => 'QC INCOMING',
                            //         'WELDING SPOT' => 'WELDING SPOT',
                            //         'WELDING SYSTEM' => 'WELDING SYSTEM',
                            //     ])
                            //     ->required(),
                        ]),
                    Grid::make(4)
                        ->schema([
                            TextInput::make('unit_jadi')
                                ->label('Unit Jadi (FG)')
                                ->maxLength(100)
                                ->required(),
                            TextInput::make('noseri')
                                ->label('No. Seri')
                                ->required(),
                            Select::make('kategory')
                                ->label('Kategori')
                                ->options([
                                    'Critical (Safety)' => 'Critical (Safety)',
                                    'Major (Function)' => 'Major (Function)',
                                    'Minor (Apperance)' => 'Minor (Apperance)',
                                ])
                                 ->required(),

                            Select::make('faktor_lks')
                                ->label('Faktor LKS')
                                ->options([
                                    'Manusia' => 'Manusia',
                                    'Material' => 'Material',
                                    'Metoda' => 'Metoda',
                                    'PD' => 'PD',
                                    'PD-Mesin Bending ' => 'PD-Mesin Bending',
                                    'Tool ' => 'Tool',
                                ])
                                ->required(),
                            Select::make('karyawan_id_pl')
                                ->label('Plant / Supervisor')
                                ->relationship('karyawan_id_plant', 'nama')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama')->required(),
                                    TextInput::make('jabatan'),
                                ]),                    

                            Select::make('karyawan_id_dft')
                                ->label('Drafter / Operator')
                                ->relationship('karyawan_id_drafter', 'nama')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama')->required(),
                                    TextInput::make('jabatan'),
                                ]),                    
                            ]),  
                        ]),
    
                Fieldset::make('Informasi Temuan, Penyebab & Penyelesaian')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Textarea::make('temuan')
                                ->label('Temuan Ketidaksesuaian')
                                ->maxLength(200)
                                ->required(),
                            Textarea::make('penyebab')
                                ->label('Penyebab Ketidaksesuaian')
                                ->maxLength(200)
                                ->required(),
                        ]),        
                    FileUpload::make('fileupload')
                        ->label('Attach File')
                        ->image()
                        ->maxSize(3000) // Maksimal 3MB per file
                        ->directory('spk-activities/photos')
                        ->preserveFilenames()
                        ->afterStateUpdated(function ($state) {
                            if (!$state) return;
                            $path = storage_path('app/public/' . $state);
                            if (!file_exists($path)) {
                                logger("File not found at: " . $path);
                                return;
                            }
                            if ($state) {
                                $path = storage_path('app/public/' . $state);
                                // Resize pakai Intervention Image
                                $image = Image::make($path)
                                    ->resize(800, null, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })
                                    ->encode('jpg', 80); // Kompres kualitas
                                $image->save($path); // Simpan hasil resize
                                // Optimasi pakai Spatie Image Optimizer
                                ImageOptimizer::optimize($path);
                            }
                        }),
    

                    Grid::make(2)
                    ->schema([
                        Textarea::make('penyelesaian_sementara')
                            ->label('Penyelesaian Sementara')
                            ->maxLength(200)
                            ->required(),
                        Textarea::make('penyelesaian_permanen')
                            ->label('Penyelesaian Permanen')
                            ->maxLength(200)
                            ->required(),
                    ]),        


                ]),

                Grid::make(4)
                    ->schema([
                        DatePicker::make('target_selesai')
                            ->label('Target Selesai')
                            ->required(),
                        DatePicker::make('realisasi_selesai')
                            ->label('Realisasi Selesai')
                            ->required(),
                        Select::make('pengulanganketidaksesuaian')
                                ->label('Pengulangan LKS')
                                ->options([
                                    'Pertama kali' => 'Pertama kali',
                                    'Kedua' => 'Kedua',
                                    'Ketiga' => 'Ketiga',
                                    'Lebih dari tiga kali' => 'Lebih dari tiga kali',
                                ])
                                ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Open' => 'Open',
                                'Close' => 'Close',
                            ])
                            ->required(),
                    ]),        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Weeks')
                    ->formatStateUsing(fn ($state) => 'M-' . Carbon::parse($state)->week)
                    ->searchable(),
                TextColumn::make('noreference')
                    ->label('No. Reference')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('pembuat.nama')
                    ->label('Pembuat LKS')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyebab.nama')
                    ->label('Penyebab LKS')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('unit_jadi')
                    ->label('Unit Jadi [FG]')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('temuan')
                    ->label('Temuan Ketidaksesuaian')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyebab')
                    ->label('Penyebab Ketidaksesuaian')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kategory')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('faktor_lks')
                    ->label('Faktor LKS')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('karyawan_id_plant.nama')
                    ->label('Plant / Supervisor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('karyawan_id_drafter.nama')
                    ->label('Drafter / Operator')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pengulanganketidaksesuaian')
                    ->label('Pengulangan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyelesaian_sementara')
                    ->label('Penyelesaian Sementara')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyelesaian_permanen')
                    ->label('Penyelesaian Pemanen')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('target_selesai')
                    ->label('Target')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'M-' . Carbon::parse($state)->week)
                    ->searchable(),
                TextColumn::make('realisasi_selesai')
                    ->label('Realisasi')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'M-' . Carbon::parse($state)->week)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'No' => 'warning',
                        'Yes' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',
                        'Close' => 'Close',
                    ]),
                    
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('from '),
                        Forms\Components\DatePicker::make('tanggal1')
                            ->label('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal1'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn ($record) => $record->status !== 'Close')
                ->label(false),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => $record->status !== 'Close')
                ->label(false),
                Tables\Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                // ->hidden()
                ->label(false),
            ])

            ->headerActions([
                ExportAction::make('export')
                    ->label('Export Excel')
                    ->color('gray')
                    ->exporter(LksExporter::class),
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
            'index' => Pages\ListLks::route('/'),
            'create' => Pages\CreateLks::route('/create'),
            'edit' => Pages\EditLks::route('/{record}/edit'),
        ];
    }
}
