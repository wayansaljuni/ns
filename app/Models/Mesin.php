<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    protected $table = 'mesin';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'nama',
    ];    
}
