<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fileupload'
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


}
