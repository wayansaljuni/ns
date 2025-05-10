<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teknisi extends Model
{
    protected $table = 'teknisi';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'noko', 'nospk', 'nik', 'nama'
    ];

    /** 
    * Get all of the activities for the Teknisi
    */
   public function activiti(): HasMany
   {
       return $this->hasMany(Activiti::class, 'teknisi_id');
   }

   /**
    * Get the SPK that owns the Teknisi
    */

   public function spk()
    {
        return $this->hasMany(SPK::class, 'nospk', 'nospk');
    }

   /**
    * Get the Produk that is associated with the Teknisi
    */
   public function produk(): BelongsTo
   {
       return $this->belongsTo(Produk::class, 'noko', 'noko');
   }

   /**
    * Get the User associated with the Teknisi
    */
   public function user(): BelongsTo
   {
       return $this->belongsTo(User::class, 'nik', 'nik');
   }
}
