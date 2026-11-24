<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maskapai extends Model
{
    use HasFactory;

    protected $table = 'maskapai';

    protected $fillable = [
        'nama_maskapai',
    ];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'id_maskapai');
    }
}
