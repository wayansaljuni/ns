<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spk extends Model
{
    protected $table = 'spk';
    protected $primaryKey = 'idwo';
    public $timestamps = false;

    protected $fillable = [
        'idp', 'noko', 'kdb', 'nosr', 'nospk', 'rctgl', 'tgk', 'ins', 'prwt', 
        'prbk', 'srvc', 'pmnd', 'foc', 'noin', 'bjs', 'spbk', 'approve'
    ];
    protected $casts = [
        'rctgl' => 'date',
        'tgk' => 'date',
        'ins' => 'boolean',
        'prwt' => 'boolean',
        'prbk' => 'boolean',
        'srvc' => 'boolean',
        'pmnd' => 'boolean',
        'foc' => 'boolean',
        'spbk' => 'boolean',
    ];
    /**
     * Get all of the activities for the SPK
     */
    public function activiti(): HasMany
    {
        return $this->hasMany(Activiti::class, 'nospk', 'nospk');
    }


    /**
     * Get all of the teknisi for the SPK
     */
    public function teknisi(): HasMany
    {
        return $this->hasMany(Teknisi::class, 'nospk', 'nospk');
    }
    
}
