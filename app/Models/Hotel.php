<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hotel';

    protected $fillable = [
        'nama_hotel',
        'bintang_hotel',
        'lokasi',
        'alamat_hotel',
        'foto_hotel',
    ];

    protected function casts(): array
    {
        return [
            'bintang_hotel' => 'integer',
        ];
    }

    public function paketTipeKamars(): HasMany
    {
        return $this->hasMany(PaketTipeKamar::class, 'id_hotel');
    }
}
