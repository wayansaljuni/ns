<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'noko', 'kdb', 'nmb', 'nosr', 'nop', 'tglg', 'klh', 'krskn', 'solusi',
        'rtgl', 'stgl', 'sts', 'kdcab', 'ho', 'tgldtg1', 'tglplg1', 'tgldtg2',
        'tglplg2', 'tgldtg3', 'tglplg3', 'electrical', 'waterinlet', 'drainage',
        'steamwand', 'showers', 'portafilt', 'backflush', 'gasket', 'volmetric',
        'group1', 'group2', 'group3', 'waterqual', 'hotwtrtmp', 'cofwtrtmp',
        'wtrpress', 'boilpress', 'pumppress', 'motor', 'blades', 'autobutt',
        'yvolt', 'yampere', 'ymbar', 'ybar', 'ycelcius'
    ];
    protected $casts = [
        'tglg' => 'date',
        'rtgl' => 'date',
    ];

    /**
     * Get all of the teknisi for the Produk
     */
    public function teknisi(): HasMany
    {
        return $this->hasMany(Teknisi::class, 'noko', 'noko');
    }

    /**
     * Get the SPK activities indirectly related to this Produk
     * This is a convenience method that goes through Teknisi
     */
    public function activiti()
    {
        return $this->hasManyThrough(
            Activiti::class,
            Teknisi::class,
            'noko', // Foreign key on Teknisi table
            'teknisi_id', // Foreign key on Activiti table
            'noko', // Local key on Produk table
            'id' // Local key on Teknisi table
        );
    }
    public function spk(): BelongsTo
    {
        return $this->belongsTo(SPK::class, 'idp');
    }
}
