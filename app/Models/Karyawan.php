<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'nama',
        'jabatan',

    ];    
}
