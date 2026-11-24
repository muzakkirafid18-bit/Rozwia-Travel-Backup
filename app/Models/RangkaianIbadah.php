<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RangkaianIbadah extends Model
{
    use HasFactory;

    protected $table = 'rangkaian_ibadah';

    protected $fillable = [
        'id_paket',
        'urutan',
        'judul_tahapan',
        'deskripsi',
        'path_icon',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }
}
