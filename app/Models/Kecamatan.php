<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;
    protected $table = 'wilayah_kecamatan';

    public function agen()
    {
        return $this->hasOne('App\Models\AgenDesa', 'id_kecamatan', 'id');
    }
}
