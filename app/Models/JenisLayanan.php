<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_layanan';

    protected $fillable = [
        'nama_pelayanan',
    ];

    public function pakets(): HasMany
    {
        return $this->hasMany(Paket::class, 'id_jenis_layanan');
    }
}
