<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Lks extends Model
{
    protected $table = 'lks';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'tanggal',
        'noreference',
        'pembuat_lks',
        'penyebab_lks',
        'unit_jadi',
        'temuan',
        'penyebab',
        'kategory',
        'faktorpenyebab',
        'faktor_lks',
        'pl_spv',
        'dft_opr',
        'pengulanganketidaksesuaian',
        'penyelesaian_sementara',
        'penyelesaian_permanen',
        'target_selesai',
        'realisasi_selesai',
        'status',
        'user_id',
        'noseri',
        'fileupload',
        'kdun'
    ];

    public function user()
        {
            return $this->belongsTo(User::class);
        }
    public function karyawan_id_plant()
        {
            return $this->belongsTo(Karyawan::class, 'karyawan_id_pl');
        }
    public function karyawan_id_drafter()
        {
            return $this->belongsTo(Karyawan::class, 'karyawan_id_dft');
        }

    public function pembuat()
        {
            return $this->belongsTo(Mesin::class, 'mesin_id_pembuat');
        }
        
    public function penyebab()
        {
            return $this->belongsTo(Mesin::class, 'mesin_id_penyebab');
        }
    protected static function booted()
    {
        static::creating(function ($lks) {
            if (Auth::check()) {
                $lks->user_id = Auth::id();
                $lks->kdun = Auth::user()->kdun;
            }
        });
    }
    public function scopeFilterByUserKdun(Builder $query): Builder
    {
        if (Auth::check()) {
            $userKdun = Auth::user()->kdun;

            if ($userKdun) {
                return $query->where('kdun', $userKdun);
            }
        }

        return $query; // Jika user tidak login atau kdun user kosong, tidak ada filter diterapkan
        // $userKdun = Auth::user()->kdun;

        // if ($userKdun) {
        //     return $query->where('kdun', $userKdun);
        // }

        // return $query; // Jika kdun user kosong, tidak ada filter diterapkan
    }

}
