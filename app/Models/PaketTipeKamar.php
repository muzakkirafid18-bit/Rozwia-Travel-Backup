<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketTipeKamar extends Model
{
    use HasFactory;

    protected $table = 'paket_tipe_kamar';

    protected $fillable = [
        'id_paket',
        'id_hotel',
        'id_tipe',
        'harga',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'id_hotel');
    }

    public function tipeKamar(): BelongsTo
    {
        return $this->belongsTo(TipeKamar::class, 'id_tipe');
    }

    public function detailKamarBookings(): HasMany
    {
        return $this->hasMany(DetailKamarBooking::class, 'id_paket_tipe_kamar');
    }
}
