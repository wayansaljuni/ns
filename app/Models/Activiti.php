<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Activiti extends Model 
{
    // use LogsActivity;
    protected $table = 'activitis';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'teknisi_id',
        'nospk',
        'nik',
        'produk_nmb',
        'produk_id',
        'kode_barang',
        'no_seri',
        'produk_krskn',
        'foto_produk',
        'foto_produk2',
        'foto_produk3',
        'fileupload',
        'kerusakan',
        'solusi',
        'status',
        'tanggal_datang',
        'tanggal_pulang',
        'tanggal_datang2',
        'tanggal_pulang2',
        'tanggal_datang3',
        'tanggal_pulang3',
        'voltage',
        'current',
        'gas_pressure',
        'water_pressure',
        'room_temperature',
        'kdcab',
    ];

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // log semua atribut
            ->logOnlyDirty() // hanya log perubahan
            ->setDescriptionForEvent(fn(string $eventName) => "Spk-Activiti - {$eventName}");
    }

    protected $casts = [
        'tanggal_datang' => 'datetime',
        'tanggal_pulang' => 'datetime',
        'tanggal_datang2' => 'datetime',
        'tanggal_pulang2' => 'datetime',
        'tanggal_datang3' => 'datetime',
        'tanggal_pulang3' => 'datetime',
    ];

    /**
     * Relasi ke tabel Teknisi
     */
    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(Teknisi::class, 'teknisi_id', 'id');
    }

    /**
     * Relasi ke tabel SPK
     */
    public function spk(): BelongsTo
    {
        return $this->belongsTo(Spk::class, 'nospk', 'nospk');
    }

    /**
     * Relasi ke tabel Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id');
    }

    /**
     * Scope query to match the SQL query you provided
     */
    public function scopeCustomQuery(Builder $query)
    {
        return $query->select([
                'spk.nospk',
                'spk.tgk',
            ])
            ->whereIn('spk.nospk', function ($subQuery) {
                $subQuery->select('spk.nospk')
                    ->from('spk')
                    ->join('produk', 'spk.idp', '=', 'produk.id')
                    ->join('teknisi', 'teknisi.noko', '=', 'produk.noko')
                    ->where('produk.sts', '<>', 'Closed')
                    ->groupBy('spk.nospk');
            })
            ->orderBy('spk.tgk', 'DESC');
    }
    /**
     * Automatically load related models to avoid N+1 query issues
     */
    protected static function booted()
    {
        static::addGlobalScope('withRelations', function ($query) {
            $query->with(['teknisi', 'spk', 'produk']);
        });
        
        // This scope might not be necessary if you need to see all statuses
        // Remove or comment out if you need to see all records
        // static::addGlobalScope('statusOpen', function ($query) {
        //     $query->where('status', 'Open');
        // });
    }    
}
