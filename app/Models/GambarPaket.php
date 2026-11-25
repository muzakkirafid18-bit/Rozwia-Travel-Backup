<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GambarPaket extends Model
{
    use HasFactory;

    protected $table = 'gambar_paket';

    protected $fillable = [
        'id_paket',
        'file_path',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }
}
